<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class StockController extends Controller
{
    public function __construct(private readonly StockService $stockService) {}

    #[OA\Post(
        path: '/api/products/{id}/stock',
        summary: 'Registrar movimiento de stock',
        tags: ['Stock'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['type', 'quantity'],
                properties: [
                    new OA\Property(property: 'type',     type: 'string', enum: ['entry', 'exit', 'adjustment']),
                    new OA\Property(property: 'quantity', type: 'integer', example: 10),
                    new OA\Property(property: 'reason',   type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Movimiento registrado'),
            new OA\Response(response: 422, description: 'Stock insuficiente'),
        ]
    )]
    public function move(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'type'     => 'required|in:entry,exit,adjustment',
            'quantity' => 'required|integer|min:1',
            'reason'   => 'nullable|string',
        ]);

        try {
            $result = $this->stockService->move($product, $data['type'], $data['quantity'], $data['reason'] ?? '');
            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    #[OA\Get(
        path: '/api/products/{id}/stock',
        summary: 'Historial de movimientos',
        tags: ['Stock'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Historial')]
    )]
    public function history(Product $product): JsonResponse
    {
        return response()->json(
            $product->stockMovements()->with('user')->paginate(20)
        );
    }
}