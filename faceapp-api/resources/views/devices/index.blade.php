<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="30">
    <title>FaceApp Device Monitor</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f1ea;
            --panel: #fffdfa;
            --ink: #1f2430;
            --muted: #6a6f7b;
            --line: #ddd4c6;
            --accent: #1f6f78;
            --accent-soft: #dff2f3;
            --good: #20744a;
            --good-soft: #dff5e8;
            --bad: #ae2d2d;
            --bad-soft: #fbe4e4;
            --warn: #9c6a11;
            --warn-soft: #fff1d6;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, #f0eadf 0%, var(--bg) 100%);
            color: var(--ink);
        }

        a {
            color: var(--accent);
        }

        .page {
            width: min(1180px, calc(100% - 32px));
            margin: 32px auto 48px;
        }

        .hero,
        .panel {
            background: rgba(255, 253, 250, 0.94);
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 16px 40px rgba(47, 45, 38, 0.08);
        }

        .hero {
            padding: 28px;
            margin-bottom: 24px;
        }

        .hero h1 {
            margin: 0 0 8px;
            font-size: 30px;
        }

        .hero p,
        .panel p,
        .panel li,
        table {
            color: var(--muted);
        }

        .grid {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(12, 1fr);
            margin-bottom: 20px;
        }

        .span-4 {
            grid-column: span 4;
        }

        .span-6 {
            grid-column: span 6;
        }

        .span-8 {
            grid-column: span 8;
        }

        .span-12 {
            grid-column: span 12;
        }

        .panel {
            padding: 22px;
        }

        .panel h2 {
            margin: 0 0 14px;
            font-size: 20px;
            color: var(--ink);
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 11px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
        }

        .pill.good {
            background: var(--good-soft);
            color: var(--good);
        }

        .pill.bad {
            background: var(--bad-soft);
            color: var(--bad);
        }

        .pill.warn {
            background: var(--warn-soft);
            color: var(--warn);
        }

        .flash {
            margin-bottom: 16px;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid transparent;
        }

        .flash.good {
            background: var(--good-soft);
            border-color: #9fd3b2;
            color: var(--good);
        }

        .flash.bad {
            background: var(--bad-soft);
            border-color: #e4b4b4;
            color: var(--bad);
        }

        .kv {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 10px 14px;
            margin: 0;
        }

        .kv dt {
            color: var(--ink);
            font-weight: 600;
        }

        .kv dd {
            margin: 0;
            overflow-wrap: anywhere;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 18px;
        }

        button {
            border: 0;
            border-radius: 12px;
            padding: 12px 16px;
            background: var(--accent);
            color: white;
            font: inherit;
            cursor: pointer;
        }

        code,
        pre {
            font-family: Consolas, Monaco, "Courier New", monospace;
        }

        pre {
            margin: 0;
            padding: 14px;
            border-radius: 14px;
            background: #f8f5ef;
            border: 1px solid var(--line);
            color: #37404a;
            overflow: auto;
            font-size: 12px;
            line-height: 1.45;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th,
        td {
            text-align: left;
            padding: 12px 10px;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }

        th {
            color: var(--ink);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .tiny {
            font-size: 12px;
            color: var(--muted);
        }

        .empty {
            padding: 18px;
            border-radius: 14px;
            background: #faf7f1;
            border: 1px dashed var(--line);
        }

        @media (max-width: 900px) {
            .span-4,
            .span-6,
            .span-8,
            .span-12 {
                grid-column: span 12;
            }

            .kv {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <section class="hero">
            <h1>FaceApp Device Monitor</h1>
            <p>This page shows what the Laravel app currently knows about your devices, recent callback traffic, and the latest enrollment attempts. It refreshes every 30 seconds.</p>
        </section>

        @if (session('status'))
            <div class="flash good">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="flash bad">{{ session('error') }}</div>
        @endif

        <div class="grid">
            <section class="panel span-6">
                <h2>Gateway Live Check</h2>
                <dl class="kv">
                    <dt>Configured device key</dt>
                    <dd><code>{{ $configuredDeviceKey ?: 'Not set' }}</code></dd>
                    <dt>Heartbeat interval</dt>
                    <dd>{{ $heartbeatIntervalSeconds }} seconds</dd>
                    <dt>Online window</dt>
                    <dd>{{ $onlineWindowSeconds }} seconds</dd>
                    <dt>Live gateway result</dt>
                    <dd>
                        @if ($gatewayError)
                            <span class="pill bad">Gateway Error</span>
                            <div class="tiny" style="margin-top: 8px;">{{ $gatewayError }}</div>
                        @elseif ($gatewayStatus)
                            <span class="pill good">Gateway Reachable</span>
                        @else
                            <span class="pill warn">No Response Yet</span>
                        @endif
                    </dd>
                </dl>

                <div class="actions">
                    <form method="post" action="{{ route('devices.monitor.configure') }}">
                        @csrf
                        <button type="submit">Push Callback URLs To Device</button>
                    </form>
                </div>

                @if ($gatewayStatus)
                    <div style="margin-top: 16px;">
                        <div class="tiny" style="margin-bottom: 6px;">Raw gateway response</div>
                        <pre>{{ json_encode($gatewayStatus, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @endif

                @if (session('gateway_config_response'))
                    <div style="margin-top: 16px;">
                        <div class="tiny" style="margin-bottom: 6px;">Latest callback config response</div>
                        <pre>{{ json_encode(session('gateway_config_response'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @endif
            </section>

            <section class="panel span-6">
                <h2>Callback URLs</h2>
                <dl class="kv">
                    <dt>Access records</dt>
                    <dd><code>{{ $callbackUrls['record'] }}</code></dd>
                    <dt>Heartbeat</dt>
                    <dd><code>{{ $callbackUrls['heartbeat'] }}</code></dd>
                    <dt>Person registration</dt>
                    <dd><code>{{ $callbackUrls['person_registration'] }}</code></dd>
                </dl>

                <p class="tiny" style="margin-top: 16px;">If you are using the old cloud middleware flow, these URLs need to be configured on the device through the middleware server configuration. The button on this page pushes them using <code>device/setSevConfig</code>.</p>
            </section>
        </div>

        <div class="grid">
            <section class="panel span-12">
                <h2>Devices</h2>
                @if ($devices->isEmpty())
                    <div class="empty">No device callbacks have been received yet. If your device is already configured, the fastest signal to expect is a heartbeat.</div>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th>Device Key</th>
                                <th>Status</th>
                                <th>Last Seen</th>
                                <th>IP</th>
                                <th>Version</th>
                                <th>People</th>
                                <th>Faces</th>
                                <th>Last Record</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($devices as $device)
                                <tr>
                                    <td><code>{{ $device->device_key }}</code></td>
                                    <td>
                                        @if ($device->is_online)
                                            <span class="pill good">Online</span>
                                        @else
                                            <span class="pill warn">No Recent Heartbeat</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($device->last_seen_at)
                                            {{ $device->last_seen_at->toDateTimeString() }}
                                            <div class="tiny">{{ $device->last_seen_at->diffForHumans() }}</div>
                                        @else
                                            <span class="tiny">Never</span>
                                        @endif
                                    </td>
                                    <td>{{ $device->last_ip ?: '-' }}</td>
                                    <td>{{ $device->last_version ?: '-' }}</td>
                                    <td>{{ $device->person_count ?? '-' }}</td>
                                    <td>{{ $device->face_count ?? '-' }}</td>
                                    <td>
                                        @if ($device->last_record_at)
                                            {{ $device->last_record_at->toDateTimeString() }}
                                        @else
                                            <span class="tiny">No access record yet</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>

            <section class="panel span-6">
                <h2>Recent Device Events</h2>
                @if ($recentEvents->isEmpty())
                    <div class="empty">No heartbeat or access record callbacks have been stored yet.</div>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Type</th>
                                <th>Device</th>
                                <th>Person</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentEvents as $event)
                                <tr>
                                    <td>
                                        @if ($event->event_time)
                                            {{ $event->event_time->toDateTimeString() }}
                                        @else
                                            {{ $event->created_at->toDateTimeString() }}
                                        @endif
                                    </td>
                                    <td>{{ str_replace('_', ' ', $event->event_type) }}</td>
                                    <td><code>{{ $event->device_key }}</code></td>
                                    <td>{{ $event->person_sn ?: '-' }}</td>
                                    <td>{{ $event->result_flag ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>

            <section class="panel span-6">
                <h2>Recent Enrollments</h2>
                @if ($recentEnrollments->isEmpty())
                    <div class="empty">No enrollments have been stored yet.</div>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Status</th>
                                <th>Device</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentEnrollments as $enrollment)
                                <tr>
                                    <td>
                                        <strong>{{ $enrollment->name }}</strong>
                                        <div class="tiny">{{ $enrollment->employee_id }}</div>
                                    </td>
                                    <td>
                                        <span class="pill {{ $enrollment->status === 'verified' ? 'good' : ($enrollment->status === 'failed' ? 'bad' : 'warn') }}">
                                            {{ $enrollment->status }}
                                        </span>
                                        @if ($enrollment->error_message)
                                            <div class="tiny" style="margin-top: 8px;">{{ $enrollment->error_message }}</div>
                                        @endif
                                        @if ($enrollment->gateway_face_response || $enrollment->verification_response)
                                            <details style="margin-top: 10px;">
                                                <summary class="tiny" style="cursor: pointer;">Raw gateway response</summary>
                                                <div style="margin-top: 8px;">
                                                    <pre>{{ json_encode([
                                                        'gateway_person_response' => $enrollment->gateway_person_response,
                                                        'gateway_face_response' => $enrollment->gateway_face_response,
                                                        'verification_response' => $enrollment->verification_response,
                                                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                </div>
                                            </details>
                                        @endif
                                    </td>
                                    <td><code>{{ $enrollment->device_key ?: '-' }}</code></td>
                                    <td>{{ optional($enrollment->updated_at)->toDateTimeString() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>
        </div>
    </div>
</body>
</html>
