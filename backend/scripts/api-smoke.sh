#!/usr/bin/env bash
# Quick API checks against a running server (default http://127.0.0.1:8080).
set -eo pipefail
BASE="${EDGEDECK_BASE:-http://127.0.0.1:8080}"

curl_api() {
  if [[ -n "${EDGEDECK_API_KEY:-}" ]]; then
    curl -sfS -H "X-API-Key: ${EDGEDECK_API_KEY}" "$@"
  else
    curl -sfS "$@"
  fi
}

echo "== GET ${BASE}/api/health"
curl_api "${BASE}/api/health" | head -c 500
echo -e "\n"

echo "== GET ${BASE}/api/config"
curl_api "${BASE}/api/config" | head -c 600
echo -e "\n"

echo "== GET ${BASE}/api/notes?limit=2"
curl_api "${BASE}/api/notes?limit=2" | head -c 600
echo -e "\n"

echo "== POST ${BASE}/api/scan"
curl_api -X POST "${BASE}/api/scan" \
  -H 'Content-Type: application/json' \
  -d '{"kind":"wifi","device_id":"smoke-test","networks":[{"ssid":"demo","rssi":-42}]}' | head -c 500
echo -e "\n"

echo "== PATCH ${BASE}/api/job (noop if demo job missing)"
curl_api -X PATCH "${BASE}/api/job" \
  -H 'Content-Type: application/json' \
  -d '{"id":"job-demo-001","status":"in_progress"}' | head -c 400
echo -e "\n"

echo "Smoke tests finished."
