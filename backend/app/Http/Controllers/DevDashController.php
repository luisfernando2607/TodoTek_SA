<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class DevDashController extends Controller
{
    /**
     * GET /health
     * Health check completo del sistema TodoTek_SA.
     */
    public function health(): JsonResponse
    {
        $checks = [];
        $allOk  = true;

        // ── POSTGRESQL ────────────────────────────────
        try {
            DB::connection()->getPdo();
            $version = DB::selectOne('SELECT version()');
            $checks['database'] = [
                'status'  => 'connected',
                'driver'  => config('database.default'),
                'version' => $version ? explode(' ', $version->version)[1] ?? 'unknown' : 'unknown',
            ];
        } catch (\Exception $e) {
            $checks['database'] = ['status' => 'fail', 'error' => $e->getMessage()];
            $allOk = false;
        }

        // ── CACHE ─────────────────────────────────────
        try {
            Cache::put('_health_ping', true, 5);
            $hit = Cache::get('_health_ping');
            $checks['cache'] = [
                'status' => $hit ? 'ok' : 'fail',
                'driver' => config('cache.default'),
            ];
            if (!$hit) $allOk = false;
        } catch (\Exception $e) {
            $checks['cache'] = ['status' => 'fail', 'error' => $e->getMessage()];
            $allOk = false;
        }

        // ── STORAGE ───────────────────────────────────
        try {
            Storage::put('_health_check.txt', 'ok');
            Storage::delete('_health_check.txt');
            $checks['storage'] = ['status' => 'ok', 'driver' => config('filesystems.default')];
        } catch (\Exception $e) {
            $checks['storage'] = ['status' => 'fail', 'error' => $e->getMessage()];
            $allOk = false;
        }

        // ── QUEUE ─────────────────────────────────────
        $qDriver = config('queue.default');
        $checks['queue'] = [
            'status' => 'ok',
            'driver' => $qDriver,
            'note'   => $qDriver === 'sync' ? 'sync mode (no worker needed)' : 'configured',
        ];

        // ── TELESCOPE ─────────────────────────────────
        $checks['telescope'] = [
            'status'  => class_exists(\Laravel\Telescope\Telescope::class) ? 'installed' : 'not_installed',
            'url'     => '/telescope',
        ];

        // ── SANCTUM ───────────────────────────────────
        $checks['sanctum'] = [
            'status' => class_exists(\Laravel\Sanctum\Sanctum::class) ? 'ok' : 'not_installed',
        ];

        // ── SWAGGER ───────────────────────────────────
        $swaggerPath = public_path('docs/api-docs.json');
        $checks['swagger'] = [
            'status'   => file_exists($swaggerPath) ? 'generated' : 'not_generated',
            'url'      => '/api/documentation',
            'json_url' => '/docs/api-docs.json',
        ];

        // ── MODELOS / TABLAS ──────────────────────────
        $tables = ['users', 'categories', 'clients', 'products', 'product_images',
                   'stock_movements', 'invoices', 'invoice_items'];
        $tablesOk = true;
        foreach ($tables as $table) {
            try {
                DB::table($table)->count();
            } catch (\Exception $e) {
                $tablesOk = false;
                break;
            }
        }
        $checks['migrations'] = ['status' => $tablesOk ? 'ok' : 'pending_migrations'];

        // ── APP INFO ──────────────────────────────────
        $appInfo = [
            'name'    => config('app.name'),
            'env'     => app()->environment(),
            'debug'   => config('app.debug'),
            'version' => app()->version(),
            'php'     => PHP_VERSION,
            'locale'  => config('app.locale'),
        ];

        // ── SYSTEM ────────────────────────────────────
        $system = [];
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $system['load_avg'] = round($load[0], 2);
        }
        $system['memory_usage'] = round(memory_get_usage(true) / 1024 / 1024, 1) . ' MB';
        $system['memory_peak']  = round(memory_get_peak_usage(true) / 1024 / 1024, 1) . ' MB';

        return response()->json([
            'status'    => $allOk ? 'ok' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'app'       => $appInfo,
            'system'    => $system,
            'checks'    => $checks,
        ], $allOk ? 200 : 503)->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * POST /dev/run-tests
     * Ejecuta PHPUnit/Pest. Solo disponible fuera de producción.
     */
    public function runTests(): JsonResponse
    {
        if (app()->isProduction()) {
            return response()->json(['error' => 'No disponible en producción.'], 403);
        }

        $start = microtime(true);

        $pestBin   = base_path('vendor/bin/pest');
        $phpunit   = base_path('vendor/bin/phpunit');
        $binary    = file_exists($pestBin) ? $pestBin : $phpunit;
        $framework = file_exists($pestBin) ? 'Pest' : 'PHPUnit';

        $process = new Process(
            [$binary, '--colors=never', '--testdox'],
            base_path(),
            [
                'APP_ENV'      => 'testing',
                'DB_CONNECTION' => config('database.default', 'pgsql'),
                'DB_DATABASE'   => 'todotek_test',
                'DB_HOST'       => config('database.connections.pgsql.host', '127.0.0.1'),
                'DB_PORT'       => config('database.connections.pgsql.port', '5432'),
                'DB_USERNAME'   => config('database.connections.pgsql.username', 'postgres'),
                'DB_PASSWORD'   => config('database.connections.pgsql.password', ''),
            ],
            null,
            180
        );

        try {
            $process->run();
        } catch (\Exception $e) {
            return response()->json([
                'error'     => 'Error ejecutando tests: ' . $e->getMessage(),
                'framework' => $framework,
            ], 500);
        }

        $output   = $process->getOutput() . $process->getErrorOutput();
        $duration = round(microtime(true) - $start, 2);

        // Parsear resultados
        $passed = 0; $failed = 0; $total = 0; $skipped = 0;

        if (preg_match('/(\d+) passed/',  $output, $m)) $passed  = (int)$m[1];
        if (preg_match('/(\d+) failed/',  $output, $m)) $failed  = (int)$m[1];
        if (preg_match('/(\d+) skipped/', $output, $m)) $skipped = (int)$m[1];
        if (preg_match('/(\d+) total/',   $output, $m)) $total   = (int)$m[1];

        // Fallback PHPUnit clásico: "OK (5 tests, 8 assertions)"
        if ($total === 0 && preg_match('/OK \((\d+) tests?,/', $output, $m)) {
            $passed = (int)$m[1];
            $total  = $passed;
        }
        // "FAILURES! Tests: 5, Assertions: 3, Failures: 2"
        if ($total === 0 && preg_match('/Tests: (\d+),/', $output, $m)) {
            $total = (int)$m[1];
            if (preg_match('/Failures: (\d+)/', $output, $mf)) $failed = (int)$mf[1];
            $passed = $total - $failed;
        }

        if ($total === 0) $total = $passed + $failed;

        return response()->json([
            'success'   => $process->isSuccessful(),
            'framework' => $framework,
            'output'    => $output,
            'passed'    => $passed,
            'failed'    => $failed,
            'skipped'   => $skipped,
            'total'     => $total,
            'duration'  => $duration . 's',
            'exit_code' => $process->getExitCode(),
        ]);
    }

    /**
     * POST /dev/regenerate-swagger
     * Regenera la documentación Swagger L5.
     */
    public function regenerateSwagger(): JsonResponse
    {
        if (app()->isProduction()) {
            return response()->json(['error' => 'No disponible en producción.'], 403);
        }

        $process = new Process(
            [PHP_BINARY, 'artisan', 'l5-swagger:generate'],
            base_path(),
            null, null, 60
        );

        $process->run();

        return response()->json([
            'success' => $process->isSuccessful(),
            'output'  => trim($process->getOutput() . $process->getErrorOutput()),
        ]);
    }

    /**
     * GET /dev/db-stats
     * Estadísticas rápidas de las tablas principales.
     */
    public function dbStats(): JsonResponse
    {
        if (app()->isProduction()) {
            return response()->json(['error' => 'No disponible en producción.'], 403);
        }

        $tables = [
            'users', 'categories', 'clients', 'products',
            'product_images', 'stock_movements', 'invoices', 'invoice_items',
        ];

        $stats = [];
        foreach ($tables as $table) {
            try {
                $stats[$table] = DB::table($table)->count();
            } catch (\Exception $e) {
                $stats[$table] = 'error';
            }
        }

        return response()->json(['tables' => $stats, 'timestamp' => now()->toIso8601String()]);
    }
}