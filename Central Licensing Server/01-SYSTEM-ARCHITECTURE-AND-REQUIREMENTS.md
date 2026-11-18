# Agape Central Licensing Server — System Architecture & Requirements

**Document Type:** Technical Architecture & Specifications  
**Module:** Central Licensing Engine  
**Target Host:** AgapeInfosys Web Platform (`agapeinfosys.com`)  
**Version:** 1.0.0  

---

## 1. High-Level Architectural Topology

```
+---------------------------------------------------------------------------------------------------------+
|                                        SALES & INGESTION CHANNELS                                       |
+---------------------------------------------------------------------------------------------------------+
       |                                                                                  |
       v                                                                                  v
[Envato / CodeCanyon]                                                            [agapeinfosys.com]
- User purchases Regular/Extended License                                        - User purchases via Cashfree/Stripe
- Receives Purchase Code (UUID)                                                  - Webhook generates Agape License Key
       |                                                                                  |
       +------------------------------------+---------------------------------------------+
                                            |
                                            v
+---------------------------------------------------------------------------------------------------------+
|                                    CENTRAL LICENSING SERVER (Laravel 11+)                               |
|                                         https://agapeinfosys.com                                        |
+---------------------------------------------------------------------------------------------------------+
|                                                                                                         |
|  [Ingestion / API Gateway]                                                                              |
|  - Rate Limiter: 30 req/min per IP                                                                      |
|  - Request Signature / TLS 1.3 Validation                                                                |
|                                                                                                         |
|  [Core Services]                                                                                        |
|  ├── EnvatoVerificationService (Envato API v3 with caching)                                             |
|  ├── AgapeLicenseService (Direct DB validation & Key Generation)                                        |
|  ├── DomainBindingService (Apex extraction, collision check, localhost whitelist)                       |
|  ├── CryptographicSigner (Ed25519 / HMAC-SHA256 Token Issuer)                                           |
|  └── ReleaseManagerService (Signed S3 URLs, SHA-256 Checksums)                                           |
|                                                                                                         |
|  [Data Layer (MySQL / PostgreSQL)]                                                                      |
|  ├── software_products ──< licenses ──< domain_bindings                                                 |
|  ├── software_releases                                                                                  |
|  └── license_audit_logs                                                                                 |
+---------------------------------------------------------------------------------------------------------+
                                            |
                                            | (HTTPS REST JSON)
                                            v
+---------------------------------------------------------------------------------------------------------+
|                                    CLIENT INSTANCE (SRS Genie Installation)                             |
|                                           https://customer-domain.com                                   |
+---------------------------------------------------------------------------------------------------------+
|                                                                                                         |
|  [Installer Wizard: /install] ─── Verifies Server, Enters Key, Dispatches Activation                    |
|                                                                                                         |
|  [Local Verification Layer]                                                                             |
|  - Reads SRS_LICENSE_KEY and SRS_LICENSE_HASH from .env / DB                                             |
|  - Checks domain match and cryptographic signature                                                      |
|  - Localhost / 127.0.0.1 bypass for developer staging                                                   |
|                                                                                                         |
|  [Runtime Application]                                                                                  |
|  - Protected API routes: Spec Generator, Multi-Agent MCP Gateway                                        |
|  - 1-Click Auto-Update Client: pulls delta updates from Agape Server                                    |
+---------------------------------------------------------------------------------------------------------+
```

---

## 2. Dual-Channel Verification Logic

### Channel A: Envato Market (CodeCanyon) Workflow
1. Buyer completes purchase on CodeCanyon.
2. Envato generates a **Purchase Code** in the format `8-4-4-4-12` hex characters (UUID format).
3. Buyer installs SRS Genie on their host and inputs their purchase code.
4. Client sends:
   ```json
   {
     "license_key": "c2f8e120-410a-4b95-a249-1663b6528d9a",
     "domain": "clientportal.agency.com",
     "installed_version": "2.5.0",
     "product_slug": "srs-genie"
   }
   ```
5. Central Licensing Server queries Envato Market API v3:
   - Endpoint: `GET https://api.envato.com/v3/market/author/sale?code={code}`
   - Headers: `Authorization: Bearer {ENVATO_PERSONAL_API_TOKEN}`
6. If Envato responds with `200 OK`:
   - Validates `item.id == {SRS_GENIE_ENVATO_ITEM_ID}`.
   - Extracts buyer username, purchase date, license type (`Regular License` or `Extended License`), and support expiry.
   - Upserts record into `licenses` table.

### Channel B: Direct Purchase (`agapeinfosys.com`) Workflow
1. Customer purchases SRS Genie via Cashfree Payment Gateway on `agapeinfosys.com`.
2. Cashfree triggers server-to-server webhook `POST /api/webhooks/cashfree`.
3. Cashfree signature is verified using `appId` and `secretKey`.
4. System automatically calls `AgapeLicenseService::issueLicense()`:
   - Format: `AGAPE-SRS-{RANDOM_BLOCK_1}-{RANDOM_BLOCK_2}-{CHECKSUM}`
   - Example: `AGAPE-SRS-9K2M-7B3X-L8Q1`
5. Automated email dispatched to customer containing the key, invoice, and download link.
6. When customer enters this key during installation, it is verified directly against the `licenses` table.

---

## 3. Domain Locking Rules & Exceptions

To provide a seamless developer experience while strictly enforcing single-seat licenses:

### 1. Apex Domain Normalization
The server normalizes incoming domains to avoid false mismatches:
- Input: `https://www.example.com/srs-genie/` -> Normalized: `example.com`
- Input: `sub.demo.company.co.uk` -> Normalized: `sub.demo.company.co.uk`
- Port numbers (e.g., `:8080`, `:3000`) are stripped for comparison.

### 2. Localhost & Development Staging Whitelist
Development environments are automatically exempt from binding limits:
- `localhost`
- `127.0.0.1`
- `::1`
- Any domain ending in `.test`, `.local`, `.invalid`, `.example`
- Any domain matching staging patterns: `dev.*`, `staging.*`, `test.*`

### 3. Deactivation & Domain Transfer
Buyers can transfer their license to a new domain:
- **Self-Service in Client App:** Clicking "Deactivate License" in SRS Genie Admin Panel sends `POST /api/v1/license/deactivate`. The server unbinds the domain, allowing re-activation on a new host.
- **Admin Portal Override:** Agape Support staff can reset bound domains from the central admin dashboard.

---

## 4. Envato CodeCanyon Compliance Specification

To ensure SRS Genie is approved by CodeCanyon reviewers on the first submission, the licensing implementation strictly obeys Envato requirements:

| Envato Submission Rule | Compliance Implementation in SRS Genie |
| :--- | :--- |
| **No IonCube / Encrypted PHP** | 100% plain PHP 8.2+ code. No byte-code encryption or binary loaders. |
| **Readable Source Code** | Standard PSR-12 compliant code structure. Clean MVC pattern. |
| **No Phone-Home Blockers without Notification** | Installation Wizard explicitly informs the user: *"Your purchase code and domain will be verified with the licensing server at agapeinfosys.com."* |
| **Offline / Localhost Installation Allowed** | If running on `localhost` or if verification server is unreachable during local evaluation, system enters permissive dev mode. |
| **No Third-Party Advertising / Bloatware** | Zero ads or unsolicited third-party scripts. |

---

## 5. Functional & Non-Functional Requirements

### Functional Requirements (FR)
- **FR-1 [License Verification]:** System must validate keys against Envato API or local DB in `< 400ms`.
- **FR-2 [Domain Binding]:** Enforce maximum domain count (Default: 1 for Regular / Standard, 5 for Agency, unlimited for Enterprise).
- **FR-3 [Heartbeat Sync]:** Periodically (every 7 days) check license status during normal admin activity.
- **FR-4 [Auto-Updates]:** Expose version check endpoint returning release notes and temporary signed download URLs.
- **FR-5 [Audit Logging]:** Log every activation, failed attempt, heartbeat, IP address, and HTTP user agent.
- **FR-6 [Self-Service Reset]:** Allow license transfer via customer account portal on `agapeinfosys.com`.

### Non-Functional Requirements (NFR)
- **NFR-1 [Availability]:** Licensing API must maintain `99.9%` uptime (hosted on Serverbyt / Cloudflare protected).
- **NFR-2 [Security]:** All endpoints enforced over TLS 1.3; rate limiting enforced via Redis cache.
- **NFR-3 [Cryptographic Integrity]:** Activation tokens signed with HMAC-SHA256 with rotation capabilities.
- **NFR-4 [Idempotency]:** Repeated verification requests for the same key and domain return cached valid tokens without re-hitting Envato rate limits.
