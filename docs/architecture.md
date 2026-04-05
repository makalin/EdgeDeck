# EdgeDeck Architecture Notes

## MVP Layers

- `firmware/`: Cardputer ADV application flow, command macro catalog (stubs), simulated scan snapshots, local notes, and a typed sync queue (`note`, `scan`, `telemetry`, `command`).
- `backend/api/`: PHP API for health checks, redacted config, jobs (list + status updates), notes and telemetry (read/write), scan snapshots, sync batch intake, and sync history tail.
- `backend/admin/`: Dashboard with counts, recent scan/sync payloads, and an endpoint reference.
- `backend/storage/`: File-backed persistence for the MVP. This keeps the backend easy to run before introducing MySQL.

## Data Flow

1. The handheld app collects notes, scans, telemetry, or macro results.
2. New records are stored locally in the firmware queue.
3. When connectivity is available, the device posts batched records to `/api/sync` (server enforces `batch_limit`).
4. Direct `/api/note`, `/api/telemetry`, and `/api/scan` endpoints support testing and non-batched clients.

## Next Steps

- Replace file-backed storage with database adapters.
- Authenticated job assignment and multi-tenant device registry.
- Wire M5 Cardputer drivers (display, keyboard, WiFi/BLE) to replace simulation stubs.
