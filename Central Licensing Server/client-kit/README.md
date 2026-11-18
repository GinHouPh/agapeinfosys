# Agape Central Licensing — Client Drop-in Integration Kit

This folder contains the ready-to-use client library for integrating any client application (such as **SRS Genie**, **Agent Architect**, or any PHP SaaS/CodeCanyon script) with the **Agape Central Licensing Server**.

---

## Files in this Kit

- **`AgapeLicenseClient.php`** — Single-file, zero-dependency PHP 8.1+ client class. Uses standard PHP cURL, Hash, and ZipArchive.

---

## Quick Usage Guide

### 1. Initialization

```php
require_once __DIR__ . '/AgapeLicenseClient.php';

$client = new AgapeLicenseClient(
    productSlug: 'srs-genie',
    serverUrl: 'https://agapeinfosys.com/api/v1/license' // Or http://localhost:8000/api/v1/license for local testing
);
```

---

### 2. Installation Wizard Activation (Step 3 of `/install`)

When the buyer enters their Envato Purchase Code or Agape License Key:

```php
$result = $client->activate(
    licenseKey: $_POST['license_key'],
    domain: $_SERVER['HTTP_HOST'],
    installedVersion: '2.5.0'
);

if ($result['valid']) {
    // Save to local database (e.g., settings table):
    // $result['activation_hash']
    // $result['client_salt']
    // $result['tier']
    // $result['buyer']
    echo "License verified! Activated for: " . $result['buyer'];
} else {
    echo "Verification failed: " . $result['message'];
}
```

---

### 3. Fast Offline Local Verification (< 0.2ms)

Put this in your application middleware or bootstrap (`license_check.php`):

```php
// Read saved credentials from local DB:
$key  = getSetting('srs_license_key');
$hash = getSetting('srs_activation_hash');
$salt = getSetting('srs_client_salt');
$tier = getSetting('srs_license_tier');

$isValid = $client->verifyLocal(
    licenseKey: $key,
    domain: $_SERVER['HTTP_HOST'],
    tier: $tier,
    salt: $salt,
    expectedHash: $hash
);

if (!$isValid) {
    die("Software license is invalid or domain mismatch.");
}
```

*Note: If running on `localhost`, `127.0.0.1`, or `*.test`, `$client->verifyLocal()` automatically returns `true` so developers testing locally are never locked out.*

---

### 4. Self-Service Deactivation & Domain Transfer

When the buyer clicks "Deactivate License" in their SRS Genie settings:

```php
$success = $client->deactivate($licenseKey, $_SERVER['HTTP_HOST']);

if ($success) {
    // Clear local license settings
    echo "Domain unbound! You may now activate this license on your new server.";
}
```

---

### 5. Check for Updates & 1-Click Auto-Update

```php
$update = $client->checkUpdate($licenseKey, '2.5.0');

if ($update['update_available']) {
    echo "New version available: v" . $update['latest_version'];
    echo "Changelog:\n" . $update['changelog'];

    // Download, verify SHA-256 integrity, and extract automatically:
    $client->downloadAndApplyUpdate(
        downloadUrl: $update['download_url'],
        expectedSha256: $update['sha256'],
        destinationDir: dirname(__DIR__) // Extract to root of installation
    );
}
```
