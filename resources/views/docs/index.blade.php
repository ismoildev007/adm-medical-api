<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; margin: 0; padding: 24px; color: #222; }
        .container { max-width: 960px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
        h1 { margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #e5e5e5; padding: 10px 12px; text-align: left; }
        th { background: #fafafa; }
        a { color: #0b5ed7; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .empty { padding: 12px; background: #fff3cd; border: 1px solid #ffe69c; border-radius: 8px; }
        .postman-card { margin: 14px 0 18px; padding: 14px; border-radius: 10px; border: 1px solid #ffd7bf; background: linear-gradient(90deg, #fff4ec 0%, #fffaf7 100%); }
        .postman-badge { display: inline-block; font-size: 12px; font-weight: 700; color: #9a3412; background: #ffedd5; border: 1px solid #fdba74; border-radius: 999px; padding: 2px 10px; margin-bottom: 8px; }
        .postman-title { margin: 0 0 6px; font-size: 18px; font-weight: 700; color: #7c2d12; }
        .postman-link { display: inline-block; margin-top: 4px; padding: 8px 12px; border-radius: 8px; background: #f97316; color: #fff; font-weight: 700; }
        .postman-link:hover { text-decoration: none; background: #ea580c; }
        .postman-url { display: block; margin-top: 8px; font-size: 13px; color: #444; word-break: break-all; }
    </style>
</head>
<body>
<div class="container">
    <h1>Docs Files</h1>
    <div class="postman-card">
        <span class="postman-badge">FEATURED</span>
        <p class="postman-title">Postman Docs</p>
        <a class="postman-link" href="https://documenter.getpostman.com/view/53303738/2sBXqRibv8" target="_blank">
            Open Postman Documentation
        </a>
        <span class="postman-url">https://documenter.getpostman.com/view/53303738/2sBXqRibv8</span>
    </div>

    @if (count($docs) === 0)
        <div class="empty">`docs` papkasida `.md` fayl topilmadi.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>url</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($docs as $index => $doc)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ strtoupper($doc['name']) }}</td>
                        <td><a href="{{ $doc['web_url'] }}">{{ $doc['web_url'] }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
</body>
</html>
