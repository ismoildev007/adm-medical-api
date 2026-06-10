<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $filename }}</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #eceff4; margin: 0; padding: 24px; color: #1e293b; }
        .container { max-width: 1400px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        a { color: #2563eb; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .meta { margin-bottom: 12px; color: #64748b; font-size: 14px; }
        .diagram { overflow: auto; min-height: 200px; padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; }
        .diagram svg { max-width: 100%; height: auto; }
        .err { color: #b91c1c; white-space: pre-wrap; font-size: 13px; background: #fef2f2; padding: 12px; border-radius: 8px; }
        .panel { margin-top: 20px; }
        .panel h2 { font-size: 15px; margin: 0 0 8px; color: #475569; }
        .panel pre { margin: 0; background: #0f172a; color: #e2e8f0; padding: 16px; border-radius: 8px; overflow-x: auto; font-size: 12px; line-height: 1.5; }
    </style>
</head>
<body>
<div class="container">
    <p><a href="/docs">&larr; Back to docs list</a></p>
    <h1>{{ $name }}</h1>
    <div class="meta">{{ $filename }}@if($via) — diagramma: Kroki ({{ $via }})@endif</div>

    @if ($svg)
        <div class="diagram" aria-label="ER diagram">
            {!! $svg !!}
        </div>
    @endif

    @if ($diagramError)
        <p class="err" role="alert">Diagramma yuklanmadi (Kroki). Quyida DBML manba.</p>
        <p class="err">{{ $diagramError }}</p>
    @endif

    <div class="panel">
        <h2>DBML</h2>
        <pre><code>{{ $dbml }}</code></pre>
    </div>
</div>
</body>
</html>
