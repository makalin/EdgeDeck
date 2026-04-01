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
* MySQL or lightweight DB (Velo-Lite compatible)
* Admin dashboard (optional)

---

## 📡 API (Example)

```
POST /api/sync
GET  /api/jobs
POST /api/note
POST /api/telemetry
GET  /api/config
```

---

## 📁 Project Structure

```
edgedeck/
  firmware/
    src/
    lib/
    data/
  backend/
    api/
    admin/
    config/
  docs/
  hardware/
  README.md
```

---

## ⚡ MVP Scope

* Menu system
* WiFi scanning
* Note creation + storage
* JSON sync queue
* Basic PHP API endpoint

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
