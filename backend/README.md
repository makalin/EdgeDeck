# EdgeDeck Backend

PHP API and admin UI for the EdgeDeck MVP (file-backed storage under `storage/`).

## Endpoints

| Method | Path | Description |
| --- | --- | --- |
| GET | `/api/health` | Liveness, version, storage writable (always public) |
| GET | `/api/config` | Device-oriented config (API key value redacted) |
| GET | `/api/jobs` | Job queue |
| PATCH | `/api/job` | Update job `status` and optional `result` JSON |
| GET | `/api/notes` | Recent notes (`limit` query, capped) |
| POST | `/api/note` | Create note |
| GET | `/api/telemetry` | Recent telemetry rows |
| POST | `/api/telemetry` | Ingest telemetry |
| POST | `/api/scan` | Store WiFi/BLE snapshot (`kind`: `wifi`, `ble`, or `both`) |
| GET | `/api/scans` | Recent scan records |
| POST | `/api/sync` | Batch upload from device (respects `sync.batch_limit`) |
| GET | `/api/sync/history` | Tail of accepted sync batches |

## Security and CORS

- Set environment variable `EDGEDECK_API_KEY` to require `X-API-Key: …` or `Authorization: Bearer …` on every route except `GET /api/health`.
- Optional `EDGEDECK_CORS_ORIGIN` (default `*`) for browser or hybrid clients.

## Local run

From the repository root:

```sh
make serve
# or
php -S 127.0.0.1:8080 -t backend backend/router.php
```

Then open `http://127.0.0.1:8080/admin/` or hit `http://127.0.0.1:8080/api/health`.

## Smoke test

With the server running:

```sh
make api-smoke
# or
EDGEDECK_API_KEY=secret backend/scripts/api-smoke.sh
```

Set `EDGEDECK_BASE` if the server listens elsewhere (default `http://127.0.0.1:8080`).
