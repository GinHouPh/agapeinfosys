# Agape Central Licensing Server — Anti-Piracy & Protection Strategy

**Document Type:** Anti-Piracy Architecture & IP Protection Guidelines  
**Applicability:** CodeCanyon Market Submissions & Direct Commercial Sales  
**Target:** SRS Genie & Agape Web Software  
**Version:** 1.0.0  

---

## 1. The CodeCanyon Protection Paradox

CodeCanyon (Envato) enforces a strict policy:
> *"Files submitted must not be encrypted with IonCube, SourceGuardian, or similar binary obfuscators. The code must be human-readable for security review and customer customization."*

This means traditional binary code encryption is **strictly prohibited**. Anyone can theoretically open `license_check.php` and comment out the `enforce()` check (known in piracy circles as "nulling").

To build real protection without getting rejected by Envato, top-tier CodeCanyon authors (earning \$100k–\$1M+) use the **Three-Pillar "Value-Add" Protection Model**:

```
                                [THE THREE PILLARS]
                                         │
        ┌────────────────────────────────┼────────────────────────────────┐
        ▼                                ▼                                ▼
[Pillar 1: Cloud-Synced Assets]  [Pillar 2: 1-Click Auto-Updates]  [Pillar 3: Watermarking & Leak Tracking]
Core templates, models, &         Nulled copies get stuck on old    Unique buyer tokens embedded
prompt catalogs synced from       versions; legitimate buyers get   in downloads allow tracking
agapeinfosys.com cloud API        instant patches & new features     leaks back to the pirate buyer
```

---

## 2. Pillar 1: Cloud-Synced Dynamic Assets

Do not bundle all 22-tier `.specs` prompt templates statically inside the local codebase. Instead, keep the template definitions and newest AI agent rules synced dynamically from `agapeinfosys.com`:

```php
// In SRS Genie Spec Generator Service:
public function fetchLatestSpecTemplates(): array
{
    $cached = $this->getLocalCachedTemplates();
    
    // Attempt dynamic sync from Agape Cloud with active license
    $remoteTemplates = $this->queryAgapeCloudTemplates();
    if ($remoteTemplates) {
        return array_merge($cached, $remoteTemplates);
    }

    return $cached; // Fallback to baseline templates if offline
}
```

**Impact on Piracy:**
- A pirated or nulled copy will only ever have the baseline/initial templates.
- As you release new agent modules (e.g., Cursor Composer v2, Claude 3.7 Sonnet prompts, Windsurf Cascade specs), only validated installations receive them automatically.

---

## 3. Pillar 2: The 1-Click Auto-Updater Incentive

Software buyers on CodeCanyon strongly prefer products with seamless 1-click updates over pirated versions that require manual patching and database migrations.

### Auto-Updater Security Architecture:
1. When user clicks **"Update to v2.6.0"** in Admin Dashboard, the request passes their license key to `https://agapeinfosys.com/api/v1/license/download-update`.
2. The server verifies the license status:
   - If license is `active`, it generates a **temporary signed download URL** valid for 15 minutes.
   - If license is `revoked`, `refunded`, or `suspended`, download is refused with `403 Forbidden`.
3. Client downloads the zip, verifies the `sha256_checksum` against the server response, extracts files, and runs any pending database migrations automatically.

---

## 4. Pillar 3: Buyer Watermarking & Leak Traceability

When generating the downloadable package for a buyer (or upon initial purchase):
1. Embed an invisible digital watermark in a non-critical file (e.g. `metadata.json` or `config/version.php`):
   ```json
   {
     "build_id": "bld_98a7f21b",
     "distribution_channel": "codecanyon",
     "watermark": "sha256(buyer_username + purchase_code + secret)"
   }
   ```
2. If the package is leaked onto piracy/nulled forums (such as Babiato or Festinger Vault), download the nulled zip, inspect the watermark, and immediately:
   - Identify the exact Envato buyer account.
   - Revoke their license key in `agapeinfosys.com`.
   - Report the buyer to Envato for license breach and submit immediate DMCA takedowns.

---

## 5. Telemetry, Anomaly Detection & Auto-Blacklisting

Your Central Licensing Server monitors `license_audit_logs` for fraudulent usage patterns:

### Automatic Anomaly Detection Triggers:
1. **IP Velocity Anomaly:** More than 10 distinct public IP addresses pinging the verification or heartbeat API for the same purchase code within 24 hours.
2. **Domain Flooding:** A single Regular License (allowed 1 domain) attempting activations across 5+ different domains in a 48-hour window.
3. **Refunded / Disputed Purchases:**
   - If an Envato buyer files a refund request or chargeback, Envato API will report `supported_until: null` or revoke the sale.
   - System automatically sets `status = 'refunded'` and invalidates all bound domains.

### Server-Side Auto-Action:
```php
// In AgapeLicenseService:
if ($distinctIpsIn24Hours > 10 && $license->tier === 'regular') {
    $license->update(['status' => 'suspended']);
    // Notify Agape Admin via email/Telegram
    Notification::route('mail', 'security@agapeinfosys.com')
        ->notify(new LicenseAbuseAlert($license, $distinctIpsIn24Hours));
}
```

---

## 6. Offline Grace Period Protocol (Zero Customer Friction)

To prevent legitimate buyers from being locked out during cloud maintenance or ISP disruptions:
- **Local Validation First:** All requests are validated locally in `< 1ms` using the HMAC-SHA256 signature.
- **7-Day Heartbeat:** Remote verification pings occur asynchronously in the background only once every 7 days.
- **14-Day Offline Grace:** If `agapeinfosys.com` is unreachable during a heartbeat, the software continues operating normally for up to 14 days before showing a warning banner in the Admin panel.
- **Localhost Developer Immunity:** Complete bypass for local development domains (`localhost`, `127.0.0.1`, `*.test`), guaranteeing that buyers testing in staging never face friction.
