# Local Environment Fixes Log

The following changes were made to fix compatibility issues in a local Windows testing environment (`ramesolutions.test`).

## 1. `wp-config.php`
**Path:** `F:\Projects\ramesolutions\wp-config.php`
**Why:** The local `.test` domain was being forcefully redirected or upgraded to `https://`, resulting in an `ERR_SSL_PROTOCOL_ERROR` because the local server does not have an SSL certificate.
**Change:**
```php
// --- LOCAL DEV CHANGES START ---
// These lines were added to fix ERR_SSL_PROTOCOL_ERROR on local env
define('WP_HOME', 'http://ramesolutions.test');
define('WP_SITEURL', 'http://ramesolutions.test');
define('FORCE_SSL_ADMIN', false);
// --- LOCAL DEV CHANGES END ---
```

## 2. `Utils.php`
**Path:** `F:\Projects\ramesolutions\wp-content\plugins\hostinger-ai-assistant\vendor\hostinger\hostinger-wp-helper\src\Utils.php`
**Why:** Windows directories use backslashes (`\`), but the script was exploding on forward slashes (`/`), causing `Undefined array key` warnings and fatal errors.
**Change:**
- Added a default value for `$apiTokenFile`.
- Normalized directory separators before exploding.
- Protected `file_exists` against an empty string.

```php
// private static string $apiTokenFile; // OLD
private static string $apiTokenFile = ''; // NEW

// $hostingerDirParts = explode( '/', __DIR__ ); // OLD
$hostingerDirParts = explode( '/', str_replace('\\', '/', __DIR__) ); // NEW

// if ( file_exists( self::$apiTokenFile ) ) { // OLD
if ( ! empty( self::$apiTokenFile ) && file_exists( self::$apiTokenFile ) ) { // NEW
```

## 3. `hostinger-ai-assistant.php`
**Path:** `F:\Projects\ramesolutions\wp-content\plugins\hostinger-ai-assistant\hostinger-ai-assistant.php`
**Why:** Same Windows directory slash issue causing undefined array keys.
**Change:**
```php
// $path             = explode( '/', __DIR__ ); // OLD
// $server_root_path = '/' . $path[1] . '/' . $path[2]; // OLD
$path             = explode( '/', str_replace('\\', '/', __DIR__) ); // NEW
$server_root_path = '/' . ($path[1] ?? '') . '/' . ($path[2] ?? ''); // NEW
```

## 4. `hostinger-easy-onboarding.php`
**Path:** `F:\Projects\ramesolutions\wp-content\plugins\hostinger-easy-onboarding\hostinger-easy-onboarding.php`
**Why:** Same Windows directory slash issue causing undefined array keys.
**Change:**
```php
// $hostinger_dir_parts        = explode( '/', __DIR__ ); // OLD
// $hostinger_server_root_path = '/' . $hostinger_dir_parts[1] . '/' . $hostinger_dir_parts[2]; // OLD
$hostinger_dir_parts        = explode( '/', str_replace('\\', '/', __DIR__) ); // NEW
$hostinger_server_root_path = '/' . ($hostinger_dir_parts[1] ?? '') . '/' . ($hostinger_dir_parts[2] ?? ''); // NEW
```

## 5. `hostinger-reach.php`
**Path:** `F:\Projects\ramesolutions\wp-content\plugins\hostinger-reach\hostinger-reach.php`
**Why:** Same Windows directory slash issue causing undefined array keys.
**Change:**
```php
// $hostinger_dir_parts        = explode( '/', __DIR__ ); // OLD
// $hostinger_server_root_path = '/' . $hostinger_dir_parts[1] . '/' . $hostinger_dir_parts[2]; // OLD
$hostinger_dir_parts        = explode( '/', str_replace('\\', '/', __DIR__) ); // NEW
$hostinger_server_root_path = '/' . ($hostinger_dir_parts[1] ?? '') . '/' . ($hostinger_dir_parts[2] ?? ''); // NEW
```

## 6. `hostinger.php`
**Path:** `F:\Projects\ramesolutions\wp-content\plugins\hostinger\hostinger.php`
**Why:** Same Windows directory slash issue causing undefined array keys.
**Change:**
```php
// $hostinger_dir_parts        = explode( '/', __DIR__ ); // OLD
// $hostinger_server_root_path = '/' . $hostinger_dir_parts[1] . '/' . $hostinger_dir_parts[2]; // OLD
$hostinger_dir_parts        = explode( '/', str_replace('\\', '/', __DIR__) ); // NEW
$hostinger_server_root_path = '/' . ($hostinger_dir_parts[1] ?? '') . '/' . ($hostinger_dir_parts[2] ?? ''); // NEW
```

## 7. `hostinger-preview-domain.php`
**Path:** `F:\Projects\ramesolutions\wp-content\mu-plugins\hostinger-preview-domain.php`
**Why:** This "must-use" plugin was forcefully redirecting all URLs to `https://`, making local HTTP access impossible.
**Change:**
```php
// $filtered_url = str_replace( [ 'http://' . $this->site_domain, 'https://' . $this->site_domain ], 'https://' . $this->current_domain, $url ); // OLD
$protocol = is_ssl() ? 'https://' : 'http://'; // NEW
$filtered_url = str_replace( [ 'http://' . $this->site_domain, 'https://' . $this->site_domain ], $protocol . $this->current_domain, $url ); // NEW
```
