<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $filename }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; margin: 0; padding: 24px; color: #222; }
        .container { max-width: 960px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
        a { color: #0b5ed7; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .meta { margin-bottom: 16px; color: #555; }
        .markdown h1, .markdown h2, .markdown h3 { margin-top: 20px; }
        .markdown p { line-height: 1.7; }
        .markdown code { background: #f1f3f5; padding: 2px 4px; border-radius: 4px; }
        .markdown pre { background: #111827; color: #e5e7eb; padding: 12px; border-radius: 8px; overflow-x: auto; }
        .markdown pre code { background: transparent; padding: 0; color: inherit; }
        .markdown table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        .markdown th, .markdown td { border: 1px solid #e5e5e5; padding: 8px; text-align: left; }
    </style>
</head>
<body>
<div class="container">
    <p><a href="/docs">&larr; Back to docs list</a></p>
    <h1>{{ $name }}</h1>
    <div class="meta">{{ $filename }}</div>

    <div class="markdown">
        {!! $html !!}
    </div>
</div>
</body>
</html>
