#include <cstdio>
#include <cstdlib>
#include <ctime>
#include <stdint.h>
#include <string>
#include <vector>

namespace {

struct Note {
    std::string text;
    std::vector<std::string> tags;
    std::string createdAt;
};

struct SyncItem {
    std::string type;
    std::string payload;
};

struct CommandMacro {
    const char* id;
    const char* title;
    const char* description;
};

struct ScanRow {
    std::string id;
    int rssi;
};

struct ScanSnapshot {
    std::string kind;
    std::string capturedAt;
    std::vector<ScanRow> wifi;
    std::vector<ScanRow> ble;
};

class EdgeDeckApp {
public:
    void setup()
    {
        currentScreen_ = "menu";
        seedDemoData();
        catalogMacros();
    }

    void loop()
    {
        switch (state_) {
        case State::Menu:
            renderMenu();
            break;
        case State::Notes:
            renderNotes();
            break;
        case State::Scanner:
            renderScanner();
            break;
        case State::Sync:
            renderSync();
            break;
        case State::Tools:
            renderTools();
            break;
        }
    }

    /** Simulated macro run — returns a short JSON payload for logging or sync. */
    std::string runMacro(const char* macroId)
    {
        if (macroId == nullptr) {
            lastToolResult_ = "{\"ok\":false,\"error\":\"null_macro\"}";
            return lastToolResult_;
        }
        for (const auto& m : macros_) {
            if (std::string(macroId) == m.id) {
                lastToolResult_ = std::string("{\"macro\":\"") + macroId + "\",\"ok\":true}";
                syncQueue_.push_back({"command", lastToolResult_});
                return lastToolResult_;
            }
        }
        lastToolResult_ = "{\"ok\":false,\"error\":\"unknown_macro\"}";
        return lastToolResult_;
    }

    const std::vector<CommandMacro>& macros() const { return macros_; }

private:
    enum class State {
        Menu,
        Notes,
        Scanner,
        Sync,
        Tools,
    };

    void catalogMacros()
    {
        macros_ = {
            {"ping", "Ping host", "ICMP-style reachability check (stub)"},
            {"health", "Health probe", "HTTP GET against configured endpoint (stub)"},
            {"wifi_scan", "WiFi scan", "Populate scan buffer and queue snapshot"},
            {"ble_scan", "BLE scan", "Populate BLE rows and queue snapshot"},
            {"note_quick", "Quick note", "Enqueue templated note payload"},
        };
    }

    void seedDemoData()
    {
        notes_.push_back({"Boot check complete", {"system", "startup"}, isoNow()});
        syncQueue_.push_back({"note", R"({"text":"Boot check complete","tags":["system"]})"});
    }

    void renderMenu()
    {
        currentScreen_ = "menu";
    }

    void renderNotes()
    {
        currentScreen_ = "notes";
    }

    void renderScanner()
    {
        currentScreen_ = "scanner";
        if (!scannerPrimed_) {
            scannerPrimed_ = true;
            refreshScanSnapshot();
            enqueueScanForSync();
            lastScanSummary_ = scanSnapshot_.kind + std::string(":") +
                std::to_string(scanSnapshot_.wifi.size() + scanSnapshot_.ble.size()) +
                "_rows";
        }
    }

    void renderSync()
    {
        currentScreen_ = "sync";
        lastSyncStatus_ = syncQueue_.empty() ? "idle" : "queued_items_ready";
    }

    void renderTools()
    {
        currentScreen_ = "tools";
    }

    void refreshScanSnapshot()
    {
        scanSnapshot_.kind = "both";
        scanSnapshot_.capturedAt = isoNow();
        scanSnapshot_.wifi = {
            {"field-ap-1", -48},
            {"harbor-guest", -62},
            {"iot-backhaul", -71},
        };
        scanSnapshot_.ble = {
            {"beacon-7a3f", -55},
            {"tag-container-12", -68},
        };
    }

    void enqueueScanForSync()
    {
        std::string payload = buildScanJson(scanSnapshot_);
        syncQueue_.push_back({"scan", payload});
    }

    static std::string escapeJsonString(const std::string& s)
    {
        std::string out;
        out.reserve(s.size() + 8);
        for (unsigned char c : s) {
            switch (c) {
            case '"':
                out += "\\\"";
                break;
            case '\\':
                out += "\\\\";
                break;
            default:
                out += static_cast<char>(c);
            }
        }
        return out;
    }

    static std::string buildScanJson(const ScanSnapshot& snap)
    {
        std::string j = "{\"kind\":\"" + snap.kind + "\",\"captured_at\":\"" +
            escapeJsonString(snap.capturedAt) + "\",\"wifi\":[";
        for (size_t i = 0; i < snap.wifi.size(); ++i) {
            if (i > 0) {
                j += ',';
            }
            j += "{\"ssid\":\"" + escapeJsonString(snap.wifi[i].id) + "\",\"rssi\":" +
                std::to_string(snap.wifi[i].rssi) + "}";
        }
        j += "],\"ble\":[";
        for (size_t i = 0; i < snap.ble.size(); ++i) {
            if (i > 0) {
                j += ',';
            }
            j += "{\"id\":\"" + escapeJsonString(snap.ble[i].id) + "\",\"rssi\":" +
                std::to_string(snap.ble[i].rssi) + "}";
        }
        j += "]}";
        return j;
    }

    void pushTelemetrySample(const char* deviceId)
    {
        std::string payload = std::string("{\"device_id\":\"") + escapeJsonString(deviceId) +
            "\",\"metrics\":{\"battery_mv\":3700,\"uptime_s\":12}}";
        syncQueue_.push_back({"telemetry", payload});
    }

    static std::string isoNow()
    {
        std::time_t t = std::time(nullptr);
        std::tm* g = std::gmtime(&t);
        char buf[32];
        if (g == nullptr) {
            return "1970-01-01T00:00:00Z";
        }
        std::snprintf(
            buf,
            sizeof buf,
            "%04d-%02d-%02dT%02d:%02d:%02dZ",
            g->tm_year + 1900,
            g->tm_mon + 1,
            g->tm_mday,
            g->tm_hour,
            g->tm_min,
            g->tm_sec);
        return std::string(buf);
    }

    State state_ = State::Menu;
    std::string currentScreen_;
    std::string lastScanSummary_;
    std::string lastSyncStatus_;
    std::string lastToolResult_;
    std::vector<Note> notes_;
    std::vector<SyncItem> syncQueue_;
    std::vector<CommandMacro> macros_;
    ScanSnapshot scanSnapshot_;
    bool scannerPrimed_ = false;
};

EdgeDeckApp app;

} // namespace

void setup()
{
    app.setup();
}

void loop()
{
    app.loop();
}
