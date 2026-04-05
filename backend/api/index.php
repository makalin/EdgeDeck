<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$config = require __DIR__ . '/../config/config.php';
edgedeck_apply_api_http_layer($config);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = edgedeck_request_path();
$path = preg_replace('#^/api#', '', $path) ?: '/';

$limitDefault = (int) ($config['limits']['list_default'] ?? 20);
$limitMax = (int) ($config['limits']['list_max'] ?? 100);
$syncHistoryMax = (int) ($config['limits']['sync_history_max'] ?? 50);

if ($method === 'GET' && $path === '/health') {
    $storageOk = is_dir(EDGEDECK_STORAGE_DIR) && is_writable(EDGEDECK_STORAGE_DIR);

    if (!is_dir(EDGEDECK_STORAGE_DIR)) {
        @mkdir(EDGEDECK_STORAGE_DIR, 0775, true);
        $storageOk = is_writable(EDGEDECK_STORAGE_DIR);
    }

    edgedeck_json_response([
        'ok' => true,
        'status' => 'up',
        'api_version' => $config['api_version'] ?? '0',
        'server_time' => gmdate(DATE_ATOM),
        'storage_writable' => $storageOk,
    ]);
    return;
}

if ($method === 'GET' && $path === '/config') {
    edgedeck_json_response([
        'ok' => true,
        'config' => edgedeck_public_config($config),
        'server_time' => gmdate(DATE_ATOM),
    ]);
    return;
}

if ($method === 'GET' && $path === '/jobs') {
    $jobs = edgedeck_read_json_file('jobs.json', [
        [
            'id' => 'job-demo-001',
            'title' => 'Run connectivity survey',
            'type' => 'network_scan',
            'priority' => 'normal',
            'status' => 'queued',
            'created_at' => gmdate(DATE_ATOM),
        ],
    ]);

    edgedeck_json_response([
        'ok' => true,
        'jobs' => $jobs,
        'count' => count($jobs),
    ]);
    return;
}

if ($method === 'PATCH' && $path === '/job') {
    $input = edgedeck_read_json_input();
    $id = isset($input['id']) ? (string) $input['id'] : '';
    $status = isset($input['status']) ? (string) $input['status'] : '';

    if ($id === '' || $status === '') {
        edgedeck_json_response([
            'ok' => false,
            'error' => 'Fields "id" and "status" are required',
        ], 422);
        return;
    }

    $allowed = ['queued', 'in_progress', 'completed', 'failed', 'cancelled'];

    if (!in_array($status, $allowed, true)) {
        edgedeck_json_response([
            'ok' => false,
            'error' => 'Invalid status; use one of: ' . implode(', ', $allowed),
        ], 422);
        return;
    }

    $jobs = edgedeck_read_json_file('jobs.json', []);
    $found = false;

    foreach ($jobs as $i => $job) {
        if (is_array($job) && ($job['id'] ?? '') === $id) {
            $jobs[$i]['status'] = $status;
            $jobs[$i]['updated_at'] = gmdate(DATE_ATOM);

            if (isset($input['result']) && is_array($input['result'])) {
                $jobs[$i]['result'] = $input['result'];
            }

            $found = true;
            break;
        }
    }

    if (!$found) {
        edgedeck_json_response([
            'ok' => false,
            'error' => 'Job not found',
        ], 404);
        return;
    }

    edgedeck_write_json_file('jobs.json', $jobs);

    edgedeck_json_response([
        'ok' => true,
        'job' => $jobs[$i],
    ]);
    return;
}

if ($method === 'GET' && $path === '/notes') {
    $limit = edgedeck_clamp_int(
        isset($_GET['limit']) ? (int) $_GET['limit'] : null,
        $limitDefault,
        1,
        $limitMax
    );

    $notes = edgedeck_read_json_file('notes.json', []);
    $slice = array_slice(array_reverse($notes), 0, $limit);

    edgedeck_json_response([
        'ok' => true,
        'notes' => $slice,
        'count' => count($slice),
    ]);
    return;
}

if ($method === 'POST' && $path === '/note') {
    $input = edgedeck_read_json_input();

    if (!isset($input['text']) || trim((string) $input['text']) === '') {
        edgedeck_json_response([
            'ok' => false,
            'error' => 'Field "text" is required',
        ], 422);
        return;
    }

    $notes = edgedeck_read_json_file('notes.json', []);
    $note = [
        'id' => 'note-' . bin2hex(random_bytes(4)),
        'text' => trim((string) $input['text']),
        'tags' => array_values(array_filter($input['tags'] ?? [], 'is_string')),
        'created_at' => gmdate(DATE_ATOM),
        'source' => $input['source'] ?? 'device',
    ];
    $notes[] = $note;
    edgedeck_write_json_file('notes.json', $notes);

    edgedeck_json_response([
        'ok' => true,
        'note' => $note,
    ], 201);
    return;
}

if ($method === 'GET' && $path === '/telemetry') {
    $limit = edgedeck_clamp_int(
        isset($_GET['limit']) ? (int) $_GET['limit'] : null,
        $limitDefault,
        1,
        $limitMax
    );

    $telemetry = edgedeck_read_json_file('telemetry.json', []);
    $slice = array_slice(array_reverse($telemetry), 0, $limit);

    edgedeck_json_response([
        'ok' => true,
        'telemetry' => $slice,
        'count' => count($slice),
    ]);
    return;
}

if ($method === 'POST' && $path === '/telemetry') {
    $input = edgedeck_read_json_input();

    if (!isset($input['device_id']) || !isset($input['metrics']) || !is_array($input['metrics'])) {
        edgedeck_json_response([
            'ok' => false,
            'error' => 'Fields "device_id" and object "metrics" are required',
        ], 422);
        return;
    }

    $telemetry = edgedeck_read_json_file('telemetry.json', []);
    $record = [
        'id' => 'telemetry-' . bin2hex(random_bytes(4)),
        'device_id' => (string) $input['device_id'],
        'metrics' => $input['metrics'],
        'received_at' => gmdate(DATE_ATOM),
    ];
    $telemetry[] = $record;
    edgedeck_write_json_file('telemetry.json', $telemetry);

    edgedeck_json_response([
        'ok' => true,
        'telemetry' => $record,
    ], 201);
    return;
}

if ($method === 'POST' && $path === '/scan') {
    $input = edgedeck_read_json_input();
    $kind = isset($input['kind']) ? (string) $input['kind'] : '';

    if ($kind === '' || !in_array($kind, ['wifi', 'ble', 'both'], true)) {
        edgedeck_json_response([
            'ok' => false,
            'error' => 'Field "kind" must be "wifi", "ble", or "both"',
        ], 422);
        return;
    }

    $scans = edgedeck_read_json_file('scans.json', []);
    $record = [
        'id' => 'scan-' . bin2hex(random_bytes(4)),
        'kind' => $kind,
        'device_id' => (string) ($input['device_id'] ?? 'unknown-device'),
        'networks' => is_array($input['networks'] ?? null) ? $input['networks'] : [],
        'devices' => is_array($input['devices'] ?? null) ? $input['devices'] : [],
        'meta' => is_array($input['meta'] ?? null) ? $input['meta'] : [],
        'captured_at' => isset($input['captured_at']) && is_string($input['captured_at'])
            ? $input['captured_at']
            : gmdate(DATE_ATOM),
        'received_at' => gmdate(DATE_ATOM),
    ];
    $scans[] = $record;
    edgedeck_write_json_file('scans.json', $scans);

    edgedeck_json_response([
        'ok' => true,
        'scan' => $record,
    ], 201);
    return;
}

if ($method === 'GET' && $path === '/scans') {
    $limit = edgedeck_clamp_int(
        isset($_GET['limit']) ? (int) $_GET['limit'] : null,
        $limitDefault,
        1,
        $limitMax
    );

    $scans = edgedeck_read_json_file('scans.json', []);
    $slice = array_slice(array_reverse($scans), 0, $limit);

    edgedeck_json_response([
        'ok' => true,
        'scans' => $slice,
        'count' => count($slice),
    ]);
    return;
}

if ($method === 'GET' && $path === '/sync/history') {
    $limit = edgedeck_clamp_int(
        isset($_GET['limit']) ? (int) $_GET['limit'] : null,
        min($limitDefault, $syncHistoryMax),
        1,
        $syncHistoryMax
    );

    $entries = edgedeck_read_jsonl_tail('sync-log.jsonl', $limit);

    edgedeck_json_response([
        'ok' => true,
        'sync_batches' => $entries,
        'count' => count($entries),
    ]);
    return;
}

if ($method === 'POST' && $path === '/sync') {
    $input = edgedeck_read_json_input();
    $items = $input['items'] ?? null;
    $batchLimit = (int) ($config['sync']['batch_limit'] ?? 50);

    if (!is_array($items) || $items === []) {
        edgedeck_json_response([
            'ok' => false,
            'error' => 'Field "items" must be a non-empty array',
        ], 422);
        return;
    }

    if (count($items) > $batchLimit) {
        edgedeck_json_response([
            'ok' => false,
            'error' => 'Batch exceeds limit of ' . $batchLimit . ' items',
        ], 422);
        return;
    }

    $record = [
        'device_id' => (string) ($input['device_id'] ?? 'unknown-device'),
        'item_count' => count($items),
        'items' => $items,
        'synced_at' => gmdate(DATE_ATOM),
    ];
    edgedeck_append_json_record('sync-log.jsonl', $record);

    edgedeck_json_response([
        'ok' => true,
        'accepted' => count($items),
        'retry_after_seconds' => $config['sync']['retry_seconds'],
    ], 202);
    return;
}

edgedeck_json_response([
    'ok' => false,
    'error' => 'Endpoint not found',
    'method' => $method,
    'path' => $path,
], 404);
