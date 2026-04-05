<?php

declare(strict_types=1);

require __DIR__ . '/../api/bootstrap.php';

$notes = edgedeck_read_json_file('notes.json', []);
$telemetry = edgedeck_read_json_file('telemetry.json', []);
$jobs = edgedeck_read_json_file('jobs.json', []);
$scans = edgedeck_read_json_file('scans.json', []);
$config = require __DIR__ . '/../config/config.php';
$syncBatches = edgedeck_count_text_file_lines('sync-log.jsonl');
$recentSync = edgedeck_read_jsonl_tail('sync-log.jsonl', 3);
$apiKeyConfigured = ($config['security']['api_key'] ?? '') !== '';

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EdgeDeck Admin</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f7f5;
            --panel: #ffffff;
            --ink: #163028;
            --muted: #577267;
            --accent: #0f8b6d;
            --border: #d6e2dc;
            --warn: #b45309;
        }
        body {
            margin: 0;
            font-family: "SF Mono", "Menlo", monospace;
            background: linear-gradient(180deg, #eef5f1 0%, var(--bg) 100%);
            color: var(--ink);
        }
        .wrap {
            max-width: 960px;
            margin: 0 auto;
            padding: 32px 20px 48px;
        }
        .hero, .panel {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(22, 48, 40, 0.06);
        }
        .hero {
            margin-bottom: 20px;
        }
        .hero h1 {
            margin: 0 0 8px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            margin: 20px 0;
        }
        .stat {
            background: #f9fcfa;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
        }
        .stat strong {
            display: block;
            font-size: 26px;
            margin-top: 8px;
        }
        .panel {
            margin-top: 16px;
        }
        .panel h2 {
            margin-top: 0;
            font-size: 1.05rem;
        }
        code, pre {
            white-space: pre-wrap;
            word-break: break-word;
            font-size: 12px;
        }
        .muted {
            color: var(--muted);
        }
        .tag {
            display: inline-block;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 999px;
            background: #e8f5f0;
            color: var(--accent);
            margin-right: 6px;
        }
        .tag.warn {
            background: #fff7ed;
            color: var(--warn);
        }
        table.api {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        table.api th, table.api td {
            text-align: left;
            padding: 8px 10px;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }
        table.api th {
            color: var(--muted);
            font-weight: 600;
        }
        a {
            color: var(--accent);
        }
    </style>
</head>
<body>
    <div class="wrap">
        <section class="hero">
            <h1>EdgeDeck Admin</h1>
            <p class="muted">Backend status, recent payloads, and API reference. API version <?= htmlspecialchars((string) ($config['api_version'] ?? '?'), ENT_QUOTES, 'UTF-8') ?>.</p>
            <p>
                <?php if ($apiKeyConfigured): ?>
                    <span class="tag warn">API key required</span>
                <?php else: ?>
                    <span class="tag">Open API</span>
                <?php endif; ?>
                <a href="/api/health"><code>GET /api/health</code></a>
            </p>
        </section>

        <section class="grid">
            <div class="stat">
                <span class="muted">Notes</span>
                <strong><?= count($notes) ?></strong>
            </div>
            <div class="stat">
                <span class="muted">Telemetry</span>
                <strong><?= count($telemetry) ?></strong>
            </div>
            <div class="stat">
                <span class="muted">Jobs</span>
                <strong><?= count($jobs) ?></strong>
            </div>
            <div class="stat">
                <span class="muted">Scan snapshots</span>
                <strong><?= count($scans) ?></strong>
            </div>
            <div class="stat">
                <span class="muted">Sync batches (log)</span>
                <strong><?= $syncBatches ?></strong>
            </div>
        </section>

        <section class="panel">
            <h2>API endpoints</h2>
            <table class="api">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Path</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>GET</td><td><code>/api/health</code></td><td>Liveness (always public)</td></tr>
                    <tr><td>GET</td><td><code>/api/config</code></td><td>Feature flags and limits (key redacted)</td></tr>
                    <tr><td>GET</td><td><code>/api/jobs</code></td><td>List job queue</td></tr>
                    <tr><td>PATCH</td><td><code>/api/job</code></td><td>Body: <code>id</code>, <code>status</code>; optional <code>result</code></td></tr>
                    <tr><td>GET</td><td><code>/api/notes?limit=</code></td><td>Recent notes</td></tr>
                    <tr><td>POST</td><td><code>/api/note</code></td><td>Create note</td></tr>
                    <tr><td>GET</td><td><code>/api/telemetry?limit=</code></td><td>Recent telemetry</td></tr>
                    <tr><td>POST</td><td><code>/api/telemetry</code></td><td>Ingest telemetry</td></tr>
                    <tr><td>POST</td><td><code>/api/scan</code></td><td>WiFi/BLE snapshot (<code>kind</code>: wifi|ble|both)</td></tr>
                    <tr><td>GET</td><td><code>/api/scans?limit=</code></td><td>Recent scans</td></tr>
                    <tr><td>POST</td><td><code>/api/sync</code></td><td>Device batch upload</td></tr>
                    <tr><td>GET</td><td><code>/api/sync/history?limit=</code></td><td>Recent accepted batches</td></tr>
                </tbody>
            </table>
            <p class="muted" style="margin-bottom:0;margin-top:12px;font-size:12px;">
                Set <code>EDGEDECK_API_KEY</code> to require <code>X-API-Key</code> or <code>Authorization: Bearer</code> on all routes except <code>/api/health</code>.
                Optional <code>EDGEDECK_CORS_ORIGIN</code> for CORS (default <code>*</code>).
            </p>
        </section>

        <section class="panel">
            <h2>Latest scan snapshot</h2>
            <pre><?= htmlspecialchars(json_encode(end($scans) ?: ['message' => 'No scans yet'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?></pre>
        </section>

        <section class="panel">
            <h2>Recent sync batches</h2>
            <pre><?= htmlspecialchars(json_encode($recentSync, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?></pre>
        </section>

        <section class="panel">
            <h2>Latest note</h2>
            <pre><?= htmlspecialchars(json_encode(end($notes) ?: ['message' => 'No notes yet'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?></pre>
        </section>

        <section class="panel">
            <h2>Latest telemetry</h2>
            <pre><?= htmlspecialchars(json_encode(end($telemetry) ?: ['message' => 'No telemetry yet'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?></pre>
        </section>
    </div>
</body>
</html>
