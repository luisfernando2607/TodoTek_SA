<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TodoTek SA') }} — DevDash</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,500;0,600;1,400&family=JetBrains+Mono:wght@400;500;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --red:       #F53003;
            --red-dim:   rgba(245,48,3,0.12);
            --red-glow:  rgba(245,48,3,0.18);
            --bg:        #0e0e0c;
            --bg-card:   #161614;
            --bg-raised: #1c1c1a;
            --border:    #272724;
            --border-hi: #3a3a36;
            --text:      #EDEDEC;
            --muted:     #A1A09A;
            --dim:       #555550;
            --green:     #22c55e;
            --amber:     #f59e0b;
            --blue:      #60a5fa;
            --mono:      'JetBrains Mono', monospace;
            --head:      'Syne', sans-serif;
            --body:      'Instrument Sans', sans-serif;
        }
        *{box-sizing:border-box;margin:0;padding:0}
        html{scroll-behavior:smooth}
        body{
            font-family:var(--body);
            background:var(--bg);
            color:var(--text);
            min-height:100vh;
            overflow-x:hidden;
        }
        /* grid bg */
        body::before{
            content:'';position:fixed;inset:0;
            background-image:
                linear-gradient(var(--border) 1px, transparent 1px),
                linear-gradient(90deg, var(--border) 1px, transparent 1px);
            background-size:44px 44px;
            opacity:.28;pointer-events:none;z-index:0;
        }
        /* red glow */
        body::after{
            content:'';position:fixed;top:-15%;left:50%;
            transform:translateX(-50%);
            width:800px;height:500px;
            background:radial-gradient(ellipse,rgba(245,48,3,.1) 0%,transparent 68%);
            pointer-events:none;z-index:0;
        }

        /* ── layout ── */
        .wrap{position:relative;z-index:1;max-width:1120px;margin:0 auto;padding:0 24px 64px}

        /* ── header ── */
        header{
            display:flex;align-items:center;justify-content:space-between;
            padding:26px 0 36px;border-bottom:1px solid var(--border);margin-bottom:44px;
        }
        .logo{display:flex;align-items:center;gap:12px}
        .logo-box{
            width:36px;height:36px;background:var(--red);border-radius:8px;
            display:flex;align-items:center;justify-content:center;
            box-shadow:0 0 24px var(--red-glow);font-size:17px;
        }
        .logo-name{font-family:var(--head);font-size:1.05rem;font-weight:800;letter-spacing:-.03em}
        .logo-name span{color:var(--red)}
        .header-badges{display:flex;gap:8px;align-items:center}
        .badge{
            font-family:var(--mono);font-size:10.5px;padding:4px 10px;
            border-radius:4px;border:1px solid var(--border);background:var(--bg-raised);color:var(--muted);
        }
        .badge.env{background:var(--red-dim);border-color:rgba(245,48,3,.3);color:#ff8066}

        /* ── hero ── */
        .hero{margin-bottom:48px}
        .hero h1{
            font-family:var(--head);font-size:clamp(2rem,4.5vw,3.2rem);
            font-weight:800;letter-spacing:-.04em;line-height:1.08;margin-bottom:10px;
        }
        .hero h1 .hi{color:var(--red)}
        .hero p{color:var(--muted);font-size:.9rem;max-width:500px;line-height:1.65}
        .health-bar{display:flex;align-items:center;gap:8px;margin-top:18px;font-family:var(--mono);font-size:11.5px}
        .dot{width:8px;height:8px;border-radius:50%;background:var(--dim);position:relative;flex-shrink:0}
        .dot.online{background:var(--green)}
        .dot.online::after{
            content:'';position:absolute;inset:-3px;border-radius:50%;
            background:rgba(34,197,94,.3);animation:ping 1.6s ease-out infinite;
        }
        .dot.error{background:var(--red)}
        .dot.checking{background:var(--amber);animation:blink .9s ease infinite}
        @keyframes ping{0%{transform:scale(1);opacity:1}100%{transform:scale(2.6);opacity:0}}
        @keyframes blink{0%,100%{opacity:1}50%{opacity:.25}}
        #htxt{color:var(--muted)}
        #hup{color:var(--dim);margin-left:6px}

        /* ── grid ── */
        .g{display:grid;grid-template-columns:repeat(12,1fr);gap:14px;margin-bottom:14px}
        @media(max-width:760px){
            .g [class*="c"]{grid-column:span 12!important}
        }
        .c4{grid-column:span 4}
        .c5{grid-column:span 5}
        .c6{grid-column:span 6}
        .c7{grid-column:span 7}
        .c8{grid-column:span 8}
        .c12{grid-column:span 12}

        /* ── card ── */
        .card{
            background:var(--bg-card);border:1px solid var(--border);
            border-radius:10px;padding:22px;position:relative;overflow:hidden;
            transition:border-color .2s,box-shadow .2s;
        }
        .card:hover{border-color:var(--border-hi);box-shadow:0 4px 28px rgba(0,0,0,.45)}
        .clabel{
            font-family:var(--mono);font-size:10px;letter-spacing:.12em;
            text-transform:uppercase;color:var(--dim);margin-bottom:14px;
        }
        .ctitle{font-family:var(--head);font-size:1.05rem;font-weight:700;margin-bottom:5px}
        .cdesc{font-size:.8rem;color:var(--muted);line-height:1.55}

        /* action card */
        .acard{cursor:pointer;text-decoration:none;color:inherit;display:block}
        .acard::before{
            content:'';position:absolute;top:0;left:0;right:0;height:2px;
            background:var(--red);transform:scaleX(0);transform-origin:left;transition:transform .3s;
        }
        .acard:hover::before{transform:scaleX(1)}
        .cicon{
            width:38px;height:38px;border-radius:7px;
            background:var(--bg-raised);border:1px solid var(--border);
            display:flex;align-items:center;justify-content:center;
            margin-bottom:14px;font-size:17px;
        }
        .arrow{position:absolute;bottom:18px;right:18px;color:var(--dim);font-size:16px;transition:color .2s,transform .2s}
        .acard:hover .arrow{color:var(--red);transform:translate(2px,-2px)}

        /* ── health metrics ── */
        .metrics{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:18px}
        .metric{background:var(--bg-raised);border:1px solid var(--border);border-radius:6px;padding:12px}
        .mlabel{font-family:var(--mono);font-size:9.5px;text-transform:uppercase;letter-spacing:.1em;color:var(--dim);margin-bottom:5px}
        .mval{font-family:var(--mono);font-size:.95rem;font-weight:700}
        .mval.ok{color:var(--green)} .mval.warn{color:var(--amber)}
        .mval.err{color:var(--red)}  .mval.load{color:var(--dim)}
        .msub{font-family:var(--mono);font-size:9px;color:var(--dim);margin-top:3px}

        /* ── db stats ── */
        .dbtable{width:100%;border-collapse:collapse;font-family:var(--mono);font-size:11.5px;margin-top:14px}
        .dbtable th{text-align:left;color:var(--dim);font-size:9.5px;text-transform:uppercase;
            letter-spacing:.1em;padding:6px 10px;border-bottom:1px solid var(--border)}
        .dbtable td{padding:7px 10px;border-bottom:1px solid rgba(39,39,36,.6);color:var(--muted)}
        .dbtable td.num{color:var(--text);text-align:right}
        .dbtable tr:last-child td{border-bottom:none}
        .dbtable tr:hover td{background:rgba(255,255,255,.025)}

        /* ── buttons ── */
        .btn{
            display:inline-flex;align-items:center;justify-content:center;gap:7px;
            font-family:var(--mono);font-size:11.5px;font-weight:700;letter-spacing:.04em;
            padding:10px 16px;border-radius:6px;border:none;cursor:pointer;
            transition:background .15s,box-shadow .2s,transform .1s;
        }
        .btn:active{transform:scale(.98)}
        .btn:disabled{opacity:.45;cursor:not-allowed}
        .btn-red{background:var(--red);color:#fff;width:100%;margin-top:14px}
        .btn-red:hover:not(:disabled){background:#d42800;box-shadow:0 0 20px var(--red-glow)}
        .btn-ghost{background:var(--bg-raised);color:var(--muted);border:1px solid var(--border);margin-top:10px;width:100%}
        .btn-ghost:hover:not(:disabled){color:var(--text);border-color:var(--border-hi)}
        .spinner{width:11px;height:11px;border:2px solid rgba(255,255,255,.3);
            border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;display:none}
        .running .spinner{display:block} .running .bicon{display:none}
        @keyframes spin{to{transform:rotate(360deg)}}

        /* ── test output ── */
        .tout{
            font-family:var(--mono);font-size:11px;background:#090907;
            border:1px solid var(--border);border-radius:6px;padding:12px;
            max-height:200px;overflow-y:auto;margin-top:12px;display:none;line-height:1.8;
        }
        .tout.show{display:block}
        .tout .p{color:var(--green)} .tout .f{color:var(--red)}
        .tout .w{color:var(--amber)} .tout .i{color:var(--muted)} .tout .b{font-weight:700}

        /* ── log ── */
        .logbox{
            font-family:var(--mono);font-size:11px;background:#090907;
            border:1px solid var(--border);border-radius:6px;padding:14px;
            height:148px;overflow-y:auto;margin-top:12px;line-height:1.85;
        }
        .le{display:flex;gap:10px}
        .lt{color:var(--dim);flex-shrink:0}
        .lm.s{color:var(--green)} .lm.e{color:var(--red)}
        .lm.w{color:var(--amber)} .lm.i{color:var(--blue)} .lm.d{color:var(--muted)}

        /* ── pills row ── */
        .pills{display:flex;gap:8px;flex-wrap:wrap;margin-top:16px}
        .pill{
            display:inline-flex;align-items:center;gap:5px;font-family:var(--mono);
            font-size:10.5px;color:var(--muted);text-decoration:none;
            background:var(--bg-raised);border:1px solid var(--border);
            padding:5px 10px;border-radius:4px;transition:color .15s,border-color .15s;
        }
        .pill:hover{color:var(--text);border-color:var(--border-hi)}

        /* ── status chip ── */
        .chip{
            display:inline-flex;align-items:center;gap:5px;
            font-family:var(--mono);font-size:10px;padding:3px 8px;
            border-radius:3px;border:1px solid;
        }
        .chip.ok{color:var(--green);border-color:rgba(34,197,94,.25);background:rgba(34,197,94,.08)}
        .chip.err{color:var(--red);border-color:rgba(245,48,3,.25);background:var(--red-dim)}
        .chip.warn{color:var(--amber);border-color:rgba(245,158,11,.25);background:rgba(245,158,11,.08)}

        /* ── footer ── */
        footer{margin-top:44px;padding-top:20px;border-top:1px solid var(--border);
            display:flex;justify-content:space-between;font-family:var(--mono);
            font-size:11px;color:var(--dim)}

        /* ── animations ── */
        .fu{opacity:0;transform:translateY(14px);animation:fu .45s ease forwards}
        @keyframes fu{to{opacity:1;transform:translateY(0)}}
        .d1{animation-delay:.05s} .d2{animation-delay:.12s} .d3{animation-delay:.2s}
        .d4{animation-delay:.28s} .d5{animation-delay:.36s}

        ::-webkit-scrollbar{width:4px}
        ::-webkit-scrollbar-track{background:transparent}
        ::-webkit-scrollbar-thumb{background:var(--border-hi);border-radius:2px}
    </style>
</head>
<body>
<div class="wrap">

    {{-- HEADER --}}
    <header class="fu">
        <div class="logo">
            <div class="logo-box">⚡</div>
            <div>
                <div class="logo-name">{{ config('app.name') }} <span>DevDash</span></div>
            </div>
        </div>
        <div class="header-badges">
            <span class="badge">PHP {{ PHP_VERSION }}</span>
            <span class="badge">Laravel {{ app()->version() }}</span>
            <span class="badge env">{{ strtoupper(app()->environment()) }}</span>
        </div>
    </header>

    {{-- HERO --}}
    <div class="hero fu d1">
        <h1>Backend <span class="hi">Dashboard</span></h1>
        <p>Panel de control — salud del sistema, base de datos PostgreSQL, Swagger API, pruebas automáticas y Telescope.</p>
        <div class="health-bar">
            <div class="dot checking" id="dot"></div>
            <span id="htxt">Verificando servicios…</span>
            <span id="hup"></span>
        </div>
    </div>

    {{-- ROW 1: Health + Tests --}}
    <div class="g fu d2">

        {{-- HEALTH --}}
        <div class="card c7" id="hcard">
            <div class="clabel">// sistema</div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                <div class="ctitle">Health Check</div>
                <span id="hchip" class="chip warn">● verificando</span>
            </div>
            <div class="cdesc">Estado en tiempo real — PostgreSQL, Cache, Storage, Queue.</div>

            <div class="metrics">
                <div class="metric">
                    <div class="mlabel">PostgreSQL</div>
                    <div class="mval load" id="m-db">…</div>
                    <div class="msub" id="m-db-ver"></div>
                </div>
                <div class="metric">
                    <div class="mlabel">Cache</div>
                    <div class="mval load" id="m-cache">…</div>
                    <div class="msub" id="m-cache-drv"></div>
                </div>
                <div class="metric">
                    <div class="mlabel">Storage</div>
                    <div class="mval load" id="m-storage">…</div>
                    <div class="msub" id="m-storage-drv"></div>
                </div>
                <div class="metric">
                    <div class="mlabel">Queue</div>
                    <div class="mval load" id="m-queue">…</div>
                    <div class="msub" id="m-queue-drv"></div>
                </div>
                <div class="metric">
                    <div class="mlabel">Migrations</div>
                    <div class="mval load" id="m-mig">…</div>
                </div>
                <div class="metric">
                    <div class="mlabel">Memoria</div>
                    <div class="mval load" id="m-mem">…</div>
                    <div class="msub" id="m-load"></div>
                </div>
            </div>

            <div class="pills">
                <a href="/health" class="pill" target="_blank">↗ /health JSON</a>
                <a href="#" class="pill" id="btn-refresh">⟳ Actualizar</a>
            </div>
        </div>

        {{-- TESTS --}}
        <div class="card c5" id="tcard">
            <div class="clabel">// testing</div>
            <div class="ctitle">Pruebas Automáticas</div>
            <div class="cdesc">Ejecuta la suite PHPUnit del proyecto en entorno de test con SQLite en memoria.</div>

            <button class="btn btn-red" id="btn-tests">
                <span class="bicon">▶&nbsp; RUN TESTS</span>
                <div class="spinner"></div>
            </button>

            <div class="tout" id="tout"></div>

            <div id="tsummary" style="margin-top:10px;display:none">
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <span class="chip ok" id="tpass"></span>
                    <span class="chip err" id="tfail" style="display:none"></span>
                    <span class="chip warn" id="tskip" style="display:none"></span>
                    <span class="chip" style="color:var(--muted);border-color:var(--border);background:var(--bg-raised)" id="tdur"></span>
                </div>
            </div>
        </div>

    </div>

    {{-- ROW 2: Swagger + Telescope + DB Stats --}}
    <div class="g fu d3">

        {{-- SWAGGER --}}
        <div class="card c4 acard" onclick="window.open('/api/documentation','_blank')">
            <div class="clabel">// docs</div>
            <div class="cicon">📄</div>
            <div class="ctitle">Swagger UI</div>
            <div class="cdesc">Documentación interactiva L5-Swagger. Explora y prueba todos los endpoints de la API REST.</div>
            <div class="pills" style="margin-top:12px">
                <span class="chip ok" id="sw-chip">● comprobando</span>
            </div>
            <button class="btn btn-ghost" id="btn-swagger" onclick="event.stopPropagation();regenSwagger()">
                <span class="bicon">↺&nbsp; Regenerar Docs</span>
                <div class="spinner"></div>
            </button>
            <span class="arrow">↗</span>
        </div>

        {{-- TELESCOPE --}}
        <a href="/telescope" target="_blank" class="card c4 acard">
            <div class="clabel">// debug</div>
            <div class="cicon">🔭</div>
            <div class="ctitle">Telescope</div>
            <div class="cdesc">Monitoreo de requests, queries SQL, jobs, excepciones y logs en tiempo real.</div>
            <span class="arrow">↗</span>
        </a>

        {{-- DB STATS --}}
        <div class="card c4" id="dbcard">
            <div class="clabel">// base de datos</div>
            <div class="ctitle">Tablas</div>
            <div class="cdesc">Registros por tabla en PostgreSQL.</div>
            <table class="dbtable" id="dbtbl">
                <thead><tr><th>Tabla</th><th style="text-align:right">Registros</th></tr></thead>
                <tbody id="dbbody">
                    <tr><td colspan="2" style="color:var(--dim);text-align:center;padding:16px">Cargando…</td></tr>
                </tbody>
            </table>
        </div>

    </div>

    {{-- ROW 3: Activity Log --}}
    <div class="g fu d4">
        <div class="card c12">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <div class="clabel">// log stream</div>
                    <div class="ctitle">Activity Feed</div>
                </div>
                <button onclick="clearLog()" class="pill" style="cursor:pointer;border:none">clear</button>
            </div>
            <div class="logbox" id="log"></div>
        </div>
    </div>

    {{-- FOOTER --}}
    <footer class="fu d5">
        <span>{{ config('app.name') }} · DB: {{ config('database.default') }} · PHP {{ PHP_VERSION }}</span>
        <span>{{ now()->format('D d M Y · H:i') }}</span>
    </footer>

</div>

<script>
const $ = id => document.getElementById(id);
const tok = document.querySelector('meta[name="csrf-token"]').content;

// ─── LOG ──────────────────────────────────────────────────────────────
function log(msg, t='d') {
    const el = $('log');
    const now = new Date().toTimeString().slice(0,8);
    el.insertAdjacentHTML('beforeend',
        `<div class="le"><span class="lt">${now}</span><span class="lm ${t}">${msg}</span></div>`);
    el.scrollTop = el.scrollHeight;
}
function clearLog() { $('log').innerHTML=''; log('Log limpiado.','i'); }

// ─── HEALTH ───────────────────────────────────────────────────────────
async function checkHealth() {
    $('dot').className = 'dot checking';
    $('htxt').textContent = 'Verificando servicios…';
    $('hchip').className = 'chip warn'; $('hchip').textContent = '● verificando';

    const setM = (id, val, cls, sub='') => {
        $(id).className = 'mval '+cls;
        $(id).textContent = val;
        if (sub && $(id+'_s')) $(id+'_s').textContent = sub; // no-op
    };

    try {
        const res = await fetch('/health', { headers: { Accept: 'application/json' } });
        const d = await res.json();
        const ok = s => s === 'ok' || s === 'connected';

        // DB
        const db = d.checks?.database ?? {};
        $('m-db').className = 'mval ' + (ok(db.status) ? 'ok' : 'err');
        $('m-db').textContent = ok(db.status) ? 'CONNECTED' : 'FAIL';
        $('m-db-ver').textContent = db.version ? 'v'+db.version : (db.error?.slice(0,28) ?? '');

        // Cache
        const ca = d.checks?.cache ?? {};
        $('m-cache').className = 'mval ' + (ok(ca.status) ? 'ok' : 'err');
        $('m-cache').textContent = ok(ca.status) ? 'OK' : 'FAIL';
        $('m-cache-drv').textContent = ca.driver ?? '';

        // Storage
        const st = d.checks?.storage ?? {};
        $('m-storage').className = 'mval ' + (ok(st.status) ? 'ok' : 'err');
        $('m-storage').textContent = ok(st.status) ? 'OK' : 'FAIL';
        $('m-storage-drv').textContent = st.driver ?? '';

        // Queue
        const qu = d.checks?.queue ?? {};
        $('m-queue').className = 'mval ' + (ok(qu.status) ? 'ok' : 'warn');
        $('m-queue').textContent = (qu.driver ?? 'sync').toUpperCase();
        $('m-queue-drv').textContent = qu.note ?? '';

        // Migrations
        const mg = d.checks?.migrations ?? {};
        $('m-mig').className = 'mval ' + (ok(mg.status) ? 'ok' : 'warn');
        $('m-mig').textContent = ok(mg.status) ? 'OK' : 'PENDING';

        // Memory
        $('m-mem').className = 'mval ok';
        $('m-mem').textContent = d.system?.memory_usage ?? '—';
        $('m-load').textContent = d.system?.load_avg != null ? 'load: '+d.system.load_avg : '';

        // Swagger chip
        const sw = d.checks?.swagger ?? {};
        $('sw-chip').className = 'chip ' + (sw.status==='generated' ? 'ok' : 'warn');
        $('sw-chip').textContent = '● ' + (sw.status==='generated' ? 'generado' : 'no generado');

        // Main status
        const allOk = res.ok;
        $('dot').className = 'dot ' + (allOk ? 'online' : 'error');
        $('htxt').textContent = allOk ? 'Todos los servicios operativos' : 'Sistema degradado';
        $('hchip').className = 'chip ' + (allOk ? 'ok' : 'err');
        $('hchip').textContent = '● ' + (allOk ? 'operativo' : 'degradado');
        $('hup').textContent = d.timestamp ? new Date(d.timestamp).toLocaleTimeString('es') : '';

        log('Health check OK — ' + (allOk ? 'sistema operativo' : 'degradado'), allOk?'s':'e');
    } catch(e) {
        $('dot').className='dot error';
        $('htxt').textContent='Error al conectar con /health';
        $('hchip').className='chip err'; $('hchip').textContent='● sin conexión';
        ['m-db','m-cache','m-storage','m-queue','m-mig','m-mem'].forEach(id=>{
            $(id).className='mval err'; $(id).textContent='ERR';
        });
        log('Health check falló: '+e.message,'e');
    }
}
$('btn-refresh').addEventListener('click', e=>{ e.preventDefault(); checkHealth(); });

// ─── DB STATS ─────────────────────────────────────────────────────────
async function loadDbStats() {
    try {
        const res = await fetch('/dev/db-stats', { headers:{Accept:'application/json'} });
        const d = await res.json();
        if (!d.tables) return;
        const body = $('dbbody');
        body.innerHTML = '';
        const icons = {users:'👤',categories:'🏷️',clients:'🏢',products:'📦',
            product_images:'🖼️',stock_movements:'📊',invoices:'🧾',invoice_items:'📋'};
        Object.entries(d.tables).forEach(([t,n])=>{
            const tr = document.createElement('tr');
            tr.innerHTML=`<td>${icons[t]||''} ${t}</td><td class="num">${typeof n==='number'?n.toLocaleString():'—'}</td>`;
            body.appendChild(tr);
        });
        log('DB stats cargadas — '+Object.keys(d.tables).length+' tablas','i');
    } catch(e) {
        $('dbbody').innerHTML='<tr><td colspan="2" style="color:var(--red);text-align:center">Error cargando stats</td></tr>';
        log('Error DB stats: '+e.message,'e');
    }
}

// ─── TESTS ────────────────────────────────────────────────────────────
$('btn-tests').addEventListener('click', async () => {
    const btn=$('btn-tests'), out=$('tout'), sum=$('tsummary');
    btn.disabled=true; btn.classList.add('running');
    out.className='tout show'; out.innerHTML='<span class="w">▶ Ejecutando suite PHPUnit…</span>\n';
    sum.style.display='none';
    log('Iniciando suite de tests automáticos…','w');

    try {
        const res = await fetch('/dev/run-tests',{
            method:'POST',
            headers:{'X-CSRF-TOKEN':tok,'Accept':'application/json','Content-Type':'application/json'}
        });
        const d = await res.json();
        out.innerHTML='';
        if(d.error){ out.innerHTML=`<span class="f">${d.error}</span>`; }
        else {
            (d.output||'').split('\n').forEach(line=>{
                const el=document.createElement('div');
                el.className = line.includes('PASS')||line.includes('✓')||line.includes('passed') ? 'p'
                    : line.includes('FAIL')||line.includes('✗')||line.includes('failed') ? 'f'
                    : line.includes('Tests:')||line.includes('Duration')||line.includes('OK (') ? 'b'
                    : line.includes('WARNING')||line.includes('deprecated') ? 'w' : 'i';
                el.textContent=line;
                out.appendChild(el);
            });
        }
        out.scrollTop=out.scrollHeight;

        // summary
        const pass=d.passed??0, fail=d.failed??0, skip=d.skipped??0;
        $('tpass').textContent=`✓ ${pass} passed`;
        if(fail>0){$('tfail').textContent=`✗ ${fail} failed`;$('tfail').style.display='';}
        if(skip>0){$('tskip').textContent=`⊘ ${skip} skipped`;$('tskip').style.display='';}
        $('tdur').textContent=`⏱ ${d.duration}`;
        sum.style.display='block';

        log(`Tests: ${pass} passed, ${fail} failed, ${skip} skipped — ${d.duration}`, fail>0?'e':'s');
    } catch(e) {
        out.innerHTML=`<span class="f">Error: ${e.message}</span>`;
        log('Error al ejecutar tests: '+e.message,'e');
    }
    btn.disabled=false; btn.classList.remove('running');
});

// ─── SWAGGER REGEN ────────────────────────────────────────────────────
async function regenSwagger() {
    const btn=$('btn-swagger');
    btn.disabled=true; btn.classList.add('running');
    log('Regenerando documentación Swagger L5…','w');
    try {
        const res = await fetch('/dev/regenerate-swagger',{
            method:'POST',
            headers:{'X-CSRF-TOKEN':tok,'Accept':'application/json','Content-Type':'application/json'}
        });
        const d = await res.json();
        log(d.success ? 'Swagger regenerado correctamente ✓' : 'Error: '+d.output, d.success?'s':'e');
        if(d.success) checkHealth(); // actualiza chip
    } catch(e) { log('Error regenerando Swagger: '+e.message,'e'); }
    btn.disabled=false; btn.classList.remove('running');
}

// ─── INIT ─────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    log('{{ config("app.name") }} DevDash inicializado','i');
    log('Entorno: {{ app()->environment() }} · DB: {{ config("database.default") }}','d');
    checkHealth();
    loadDbStats();
    setInterval(checkHealth, 60000);
    setInterval(loadDbStats, 90000);
});
</script>
</body>
</html>g