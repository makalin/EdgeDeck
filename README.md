# **EdgeDeck**

**EdgeDeck is a handheld field terminal built on Cardputer ADV for scanning nearby devices, collecting structured data, executing quick actions, and syncing operational insights from the edge.**

---

## ✨ Overview

EdgeDeck transforms the Cardputer ADV into a real-world pocket tool — not a toy.
It acts as a **portable ops console** for developers, technicians, and field operators.

Use it to:

* Scan networks and nearby devices
* Collect structured field data
* Run predefined commands
* Monitor systems in real-time
* Work offline and sync later

---

## 🚀 Features

### 📡 Device Scanner

* WiFi SSID discovery + RSSI
* BLE scanning (beacons, devices)
* Save scan snapshots

### 🧾 Quick Notes

* Fast text input via keyboard
* Tagging system
* Timestamped entries
* Offline-first storage

### ⚙️ Command Terminal

* Predefined macros (ping, health check, API call)
* Custom command profiles
* Remote execution via API

### 📊 Telemetry Viewer

* Display incoming device data
* Monitor metrics (temp, status, signals)
* Lightweight dashboards

### 🔄 Sync Engine

* Offline queue (JSON-based)
* Retry logic
* Push to remote API when online

### 🧩 Jobs / Tasks

* Fetch tasks from server
* Execute and report back
* Field workflow support

---

## 🧠 Use Cases

* **DevOps Pocket Tool**
  Check server status, trigger endpoints, monitor services

* **Marine / Vehicle Debugging**
  Read telemetry (NMEA, OBD via bridge), log conditions

* **IoT Maintenance Device**
  Discover devices, test connectivity, send commands

* **Field Data Collector**
  Collect structured data (e.g. marina prices, site notes)

* **RF / Network Mapping**
  Capture signal density and environment snapshots

---

## 🏗 Architecture

### Device (Cardputer ADV)

* ESP32-S3
* WiFi + BLE
* Local storage (LittleFS / SD)
* Lightweight UI (LVGL or minimal renderer)
* JSON-based queue system

### Backend

* PHP API (minimal, no heavy frameworks)
* File-backed storage for the MVP (`backend/storage/`); DB-ready later
* Admin dashboard at `/admin/`
* Optional API key (`EDGEDECK_API_KEY`) and CORS (`EDGEDECK_CORS_ORIGIN`)

---

## 📡 API

```
GET   /api/health          # liveness (public even when API key is set)
GET   /api/config          # feature flags & limits (key redacted)
GET   /api/jobs
PATCH /api/job             # body: id, status; optional result
GET   /api/notes?limit=
POST  /api/note
GET   /api/telemetry?limit=
POST  /api/telemetry
POST  /api/scan            # WiFi/BLE snapshots (kind: wifi | ble | both)
GET   /api/scans?limit=
POST  /api/sync
GET   /api/sync/history?limit=
```

See `backend/README.md` for security notes and smoke tests.

---

## 🏃 Quick start (backend)

From the repo root:

```sh
make serve
```

Open `http://127.0.0.1:8080/admin/` or `http://127.0.0.1:8080/api/health`. With the server running, `make api-smoke` runs basic `curl` checks.

---

## 📁 Project Structure

```
edgedeck/
  Makefile
  firmware/
    platformio.ini
    src/
    lib/
    data/
  backend/
    api/
    admin/
    config/
    scripts/
    storage/
  docs/
  hardware/
  README.md
```

---

## ⚡ MVP Scope

* Firmware skeleton: macros, simulated scans, JSON-shaped sync queue
* WiFi scanning (real hardware integration pending)
* Note creation + storage
* JSON sync queue and batch POST to `/api/sync`
* PHP API: health, config, jobs, notes, telemetry, scans, sync history
* Admin status page and `api-smoke` script

---

## 🔐 Design Principles

* Offline-first
* Minimal dependencies
* Fast interaction (keyboard-first)
* Modular features
* Edge-native (no cloud lock-in)

---

## 🔮 Future Extensions

* Camera integration (object detection, snapshots)
* QR scanner / generator
* OBD / NMEA bridges
* Encrypted credential vault
* Plugin system
* Multi-device mesh sync

---

## 🧩 Philosophy

EdgeDeck is not another dashboard.
It is a **physical interface to your infrastructure** — something you carry, not open in a browser.

---

## 🏷 Tagline

**"Operate from anywhere."**
