<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'FaceApp Admin' }}</title>
    <style>
        :root {
            --bg: #f3efe7;
            --panel: rgba(255, 252, 247, 0.92);
            --line: #ddd4c6;
            --text: #1f2430;
            --muted: #6c7180;
            --accent: #0b6e6e;
            --accent-soft: #dcf2ef;
            --good: #1e7a45;
            --good-soft: #e1f5e7;
            --warn: #9c6a11;
            --warn-soft: #fff1d6;
            --bad: #b13b3b;
            --bad-soft: #f9e1e1;
            --shadow: 0 20px 40px rgba(34, 31, 24, 0.08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top right, rgba(11, 110, 110, 0.08), transparent 24rem),
                radial-gradient(circle at bottom left, rgba(170, 120, 40, 0.08), transparent 24rem),
                var(--bg);
        }
        a { color: inherit; text-decoration: none; }
        .shell { min-height: 100vh; display: grid; grid-template-columns: 260px 1fr; }
        .sidebar {
            padding: 28px 22px;
            border-right: 1px solid rgba(221, 212, 198, 0.8);
            background: rgba(255, 253, 249, 0.75);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            height: 100vh;
        }
        .brand { margin-bottom: 28px; }
        .brand h1 { margin: 0; font-size: 24px; letter-spacing: -0.04em; }
        .brand p { margin: 6px 0 0; color: var(--muted); font-size: 13px; line-height: 1.5; }
        .nav { display: grid; gap: 10px; }
        .nav a {
            padding: 12px 14px;
            border-radius: 14px;
            color: var(--muted);
            font-weight: 600;
        }
        .nav a.active, .nav a:hover { background: var(--accent-soft); color: var(--accent); }
        .main { padding: 28px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; margin-bottom: 22px; }
        .header h2 { margin: 0; font-size: 30px; letter-spacing: -0.04em; }
        .header p { margin: 8px 0 0; color: var(--muted); max-width: 760px; line-height: 1.6; }
        .grid { display: grid; gap: 20px; }
        .grid.cols-2 { grid-template-columns: 1.1fr 1fr; }
        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 22px;
        }
        .card h3 { margin: 0 0 14px; font-size: 18px; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
        .stat {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: var(--shadow);
            padding: 18px;
        }
        .stat-label { display: block; color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; }
        .stat-value { font-size: 30px; font-weight: 700; letter-spacing: -0.05em; }
        .flash {
            padding: 14px 16px;
            border-radius: 14px;
            margin-bottom: 16px;
            border: 1px solid transparent;
        }
        .flash.good { background: var(--good-soft); color: var(--good); border-color: #a7d7b7; }
        .flash.bad { background: var(--bad-soft); color: var(--bad); border-color: #e0b8b8; }
        .pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 700;
        }
        .pill.good { background: var(--good-soft); color: var(--good); }
        .pill.warn { background: var(--warn-soft); color: var(--warn); }
        .pill.bad { background: var(--bad-soft); color: var(--bad); }
        .pill.info { background: var(--accent-soft); color: var(--accent); }
        .muted, .subtle { color: var(--muted); font-size: 12px; line-height: 1.5; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 12px 10px; border-bottom: 1px solid rgba(221, 212, 198, 0.75); vertical-align: top; text-align: left; }
        th { text-transform: uppercase; letter-spacing: 0.08em; font-size: 11px; color: var(--muted); }
        .actions, .inline-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .inline-actions { gap: 8px; }
        .btn, button, input[type="submit"] {
            appearance: none;
            border: 0;
            border-radius: 12px;
            background: var(--accent);
            color: white;
            padding: 11px 14px;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }
        .btn.secondary, button.secondary { background: #e9e1d3; color: var(--text); }
        .btn.danger, button.danger { background: var(--bad); }
        .btn.small, button.small { padding: 8px 10px; border-radius: 10px; font-size: 13px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .field { display: grid; gap: 8px; }
        .field.full { grid-column: 1 / -1; }
        label { font-size: 13px; font-weight: 700; }
        input, textarea, select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 11px 12px;
            font: inherit;
            color: var(--text);
            background: rgba(255,255,255,0.8);
        }
        textarea { min-height: 110px; resize: vertical; }
        .checkbox { display: flex; align-items: center; gap: 10px; padding-top: 28px; }
        .checkbox input { width: 18px; height: 18px; }
        .tabs { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
        .tabs a {
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.7);
            color: var(--muted);
            font-weight: 700;
        }
        .tabs a.active { background: var(--accent-soft); color: var(--accent); border-color: rgba(11, 110, 110, 0.2); }
        .split { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        details pre {
            margin-top: 10px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: #faf7f1;
            padding: 12px;
            overflow: auto;
            font-size: 12px;
            line-height: 1.45;
        }
        @media (max-width: 1080px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { position: static; height: auto; border-right: 0; border-bottom: 1px solid rgba(221, 212, 198, 0.8); }
            .stats { grid-template-columns: repeat(2, 1fr); }
            .grid.cols-2 { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .main { padding: 18px; }
            .header h2 { font-size: 24px; }
            .stats { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
            .checkbox { padding-top: 0; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">
                <h1>FaceApp Admin</h1>
                <p>Manage devices, users, and gateway settings for multi-branch face access.</p>
            </div>

            <nav class="nav">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Overview</a>
                <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">Settings</a>
                <a href="{{ route('admin.devices.index') }}" class="{{ request()->routeIs('admin.devices.*') ? 'active' : '' }}">Devices</a>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Users</a>
                <a href="{{ route('devices.monitor.index') }}" class="{{ request()->routeIs('devices.monitor.*') ? 'active' : '' }}">Callback Monitor</a>
            </nav>
        </aside>

        <main class="main">
            @if (session('status'))
                <div class="flash good">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="flash bad">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="flash bad">{{ $errors->first() }}</div>
            @endif

            @yield('content')

            @if (session('action_response'))
                <section class="card" style="margin-top: 18px;">
                    <h3>Latest Action Response</h3>
                    <details open>
                        <summary class="subtle">Raw gateway response</summary>
                        <pre>{{ json_encode(session('action_response'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </details>
                </section>
            @endif
        </main>
    </div>
</body>
</html>
