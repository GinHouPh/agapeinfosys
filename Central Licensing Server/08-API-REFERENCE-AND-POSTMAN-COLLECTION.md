# Agape Central Licensing Server — API Reference & Postman Collection

**Base URL (Production):** `https://agapeinfosys.com/api/v1/license`  
**Base URL (Local Development):** `http://localhost:8000/api/v1/license`  
**Rate Limiting:** `30 requests / minute` per client IP  
**Authentication:** Public REST endpoints secured by License Key / HMAC signatures  

---

## 1. Endpoints Specification

### 1.1 Verify & Activate License (`POST /verify`)
Activates a license key (Agape Key or Envato Purchase Code) and binds it to the requesting domain.

#### Request Headers:
```http
Content-Type: application/json
Accept: application/json
```

#### Request Payload:
```json
{
  "license_key": "c2f8e120-410a-4b95-a249-1663b6528d9a",
  "domain": "customer-portal.agency.com",
  "product_slug": "srs-genie",
  "installed_version": "2.5.0"
}
```

#### Successful Response (`200 OK`):
```json
{
  "valid": true,
  "message": "License successfully verified.",
  "buyer": "john_doe",
  "tier": "regular",
  "channel": "envato",
  "activation_hash": "a1b2c3d4e5f6...7890",
  "client_salt": "secret_salt_string_32_chars",
  "support_until": "2027-05-18T00:00:00.000000Z",
  "features": {
    "mcp_gateway": true,
    "multi_ai_provider": true,
    "white_label": false
  }
}
```

#### Error Response — Domain Seat Limit Breached (`403 Forbidden`):
```json
{
  "valid": false,
  "message": "License domain limit reached (1). Deactivate an existing domain first."
}
```

#### cURL Example:
```bash
curl -X POST "https://agapeinfosys.com/api/v1/license/verify" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{
       "license_key": "AGAPE-SRS-9K2M-7B3X-L8Q1",
       "domain": "portal.agency.com",
       "product_slug": "srs-genie",
       "installed_version": "2.5.0"
     }'
```

---

### 1.2 Heartbeat Ping (`POST /heartbeat`)
Periodic telemetry ping (every 7 days) sent by client software to update the last seen timestamp and installed version.

#### Request Payload:
```json
{
  "license_key": "AGAPE-SRS-9K2M-7B3X-L8Q1",
  "domain": "portal.agency.com",
  "version": "2.5.1"
}
```

#### Response (`200 OK`):
```json
{
  "valid": true,
  "status": "active"
}
```

#### cURL Example:
```bash
curl -X POST "https://agapeinfosys.com/api/v1/license/heartbeat" \
     -H "Content-Type: application/json" \
     -d '{
       "license_key": "AGAPE-SRS-9K2M-7B3X-L8Q1",
       "domain": "portal.agency.com",
       "version": "2.5.1"
     }'
```

---

### 1.3 Deactivate & Unbind Domain (`POST /deactivate`)
Unbinds the client host domain, allowing the buyer to reactivate the software on another server.

#### Request Payload:
```json
{
  "license_key": "AGAPE-SRS-9K2M-7B3X-L8Q1",
  "domain": "portal.agency.com"
}
```

#### Response (`200 OK`):
```json
{
  "success": true,
  "message": "Domain unbound successfully. You may re-activate on a new host."
}
```

#### cURL Example:
```bash
curl -X POST "https://agapeinfosys.com/api/v1/license/deactivate" \
     -H "Content-Type: application/json" \
     -d '{
       "license_key": "AGAPE-SRS-9K2M-7B3X-L8Q1",
       "domain": "portal.agency.com"
     }'
```

---

### 1.4 Check for Updates (`GET /check-update`)
Queries the release catalog to check if an update is available for the given installed version.

#### Query Parameters:
- `product`: Product slug (e.g., `srs-genie`)
- `current_version`: Client installed version (e.g., `2.5.0`)
- `license_key`: Active license key

#### Response — Update Available (`200 OK`):
```json
{
  "update_available": true,
  "current_version": "2.5.0",
  "latest_version": "2.6.0",
  "min_php_version": "8.2.0",
  "changelog": "### Version 2.6.0\n- Enterprise multi-agent updates\n- Performance enhancements",
  "sha256": "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855",
  "download_url": "https://agapeinfosys.com/api/v1/license/download-update?release_id=2&license_key=AGAPE-SRS-9K2M-7B3X-L8Q1&expires=1795000000&signature=abc123def456..."
}
```

#### cURL Example:
```bash
curl "https://agapeinfosys.com/api/v1/license/check-update?product=srs-genie&current_version=2.5.0&license_key=AGAPE-SRS-9K2M-7B3X-L8Q1"
```

---

### 1.5 Download Update Package (`GET /download-update`)
Streams the release zip archive. Verified by signed URL or active license key.

#### cURL Example:
```bash
curl -L -O "https://agapeinfosys.com/api/v1/license/download-update?release_id=2&license_key=AGAPE-SRS-9K2M-7B3X-L8Q1"
```

---

## 2. Postman Collection (v2.1)

Save the block below as `Agape-Licensing.postman_collection.json` and import it into Postman:

```json
{
  "info": {
    "name": "Agape Central Licensing API",
    "description": "Production REST collection for Agape Central Licensing Hub",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000/api/v1/license"
    },
    {
      "key": "license_key",
      "value": "AGAPE-SRS-9K2M-7B3X-L8Q1"
    },
    {
      "key": "product_slug",
      "value": "srs-genie"
    },
    {
      "key": "domain",
      "value": "portal.agency.com"
    }
  ],
  "item": [
    {
      "name": "1. Verify & Activate License",
      "request": {
        "method": "POST",
        "header": [
          { "key": "Content-Type", "value": "application/json" },
          { "key": "Accept", "value": "application/json" }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"license_key\": \"{{license_key}}\",\n  \"domain\": \"{{domain}}\",\n  \"product_slug\": \"{{product_slug}}\",\n  \"installed_version\": \"2.5.0\"\n}"
        },
        "url": {
          "raw": "{{base_url}}/verify",
          "host": ["{{base_url}}"],
          "path": ["verify"]
        }
      }
    },
    {
      "name": "2. Send Heartbeat",
      "request": {
        "method": "POST",
        "header": [
          { "key": "Content-Type", "value": "application/json" }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"license_key\": \"{{license_key}}\",\n  \"domain\": \"{{domain}}\",\n  \"version\": \"2.5.0\"\n}"
        },
        "url": {
          "raw": "{{base_url}}/heartbeat",
          "host": ["{{base_url}}"],
          "path": ["heartbeat"]
        }
      }
    },
    {
      "name": "3. Deactivate Domain",
      "request": {
        "method": "POST",
        "header": [
          { "key": "Content-Type", "value": "application/json" }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"license_key\": \"{{license_key}}\",\n  \"domain\": \"{{domain}}\"\n}"
        },
        "url": {
          "raw": "{{base_url}}/deactivate",
          "host": ["{{base_url}}"],
          "path": ["deactivate"]
        }
      }
    },
    {
      "name": "4. Check for Updates",
      "request": {
        "method": "GET",
        "url": {
          "raw": "{{base_url}}/check-update?product={{product_slug}}&current_version=2.5.0&license_key={{license_key}}",
          "host": ["{{base_url}}"],
          "path": ["check-update"],
          "query": [
            { "key": "product", "value": "{{product_slug}}" },
            { "key": "current_version", "value": "2.5.0" },
            { "key": "license_key", "value": "{{license_key}}" }
          ]
        }
      }
    }
  ]
}
```
