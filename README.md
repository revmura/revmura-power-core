# Revmura Power Core

Engine plugin for Revmura’s hybrid architecture.

- CPT/Tax **registry** (schema-first, no UI)
- **Import/Export** JSON via REST
- **Last-Known-Good (LKG)** cache for safe fallback
- Works with an MU Guard (if present) to keep CPT/Tax alive when Core is down

Tested with **WordPress 6.8.3** and **PHP 8.3**.

---

## Requirements
- WordPress **6.5+**
- PHP **8.3+**

---

## Install & Activate
1. Copy to `wp-content/plugins/revmura-power-core`
2. Activate **Revmura Power Core** first  
3. (If you use a Guard) place your MU guard at `wp-content/mu-plugins/revmura-guard.php`

**Order with other pieces**  
1) Revmura Power Core → 2) Revmura Manager (UI) → 3) Your modules

---

## REST API

Base: `/wp-json/revmura/v1`

| Route               | Method | Notes                                |
|---------------------|--------|--------------------------------------|
| `/health`           | GET    | `{ ok: true, core_api, ver }`        |
| `/export`           | GET    | Returns current CPT/Tax JSON         |
| `/import/dry-run`   | POST   | Validates JSON, returns simple diff  |
| `/import/apply`     | POST   | Persists snapshot (LKG), flushes rewrites |

**Headers required (the Manager UI sets these for you):**
- `X-WP-Nonce` = `wp_create_nonce('wp_rest')`
- `X-Revmura-Nonce` = `wp_create_nonce('revmura_import')` (for import endpoints)

---

## JSON format (v1.0) — example

```json
{
  "schema_version": "1.0",
  "cpts": {
    "offer": {
      "label": "Offers",
      "supports": ["title","editor","thumbnail","revisions"],
      "rewrite": { "slug": "offers", "with_front": false }
    }
  },
  "taxes": {}
}
```

Core sanitizes slugs (`sanitize_key`), whitelists `supports`, normalizes booleans, and sanitizes `rewrite.slug` (`sanitize_title`).

---

## LKG cache & Guard

On commit/import, Core writes:
```
wp-content/uploads/<site>/revmura/cpt-cache.json
```
An MU Guard (if present) can read that file on `init` and register CPT/Tax when Core is unhealthy; admins see a notice.

---

## Example module (minimal)

```php
/**
 * Plugin Name: Revmura Offers (Example)
 * Requires Plugins: revmura-power-core
 */
declare(strict_types=1);

add_action('plugins_loaded', function () {
    if (!function_exists('revmura_register_cpt')) { return; }

    revmura_register_cpt('offer', [
        'label'    => 'Offers',
        'supports' => ['title','editor','thumbnail','revisions'],
        'rewrite'  => ['slug' => 'offers', 'with_front' => false],
    ]);

    // Write LKG so Guard can fall back if Core goes down.
    revmura_commit();
});
```

---

## Development

```bash
# install dev tools (PHPCS/WPCS/etc.)
composer install

# auto-fix what can be fixed, then check (cross-platform)
composer run lint:fix
composer run lint
```

PHPCS uses the project ruleset (`phpcs.xml.dist`) and also runs in CI via `.github/workflows/phpcs.yml`.

**Raw commands (only if you’re not using composer scripts):**

**Windows (PowerShell):**
```powershell
vendor\bin\phpcbf.bat -p -s --standard=phpcs.xml.dist .
vendor\bin\phpcs.bat  -q -p -s --standard=phpcs.xml.dist .
```

**macOS/Linux:**
```bash
vendor/bin/phpcbf -p -s --standard=phpcs.xml.dist .
vendor/bin/phpcs  -q -p -s --standard=phpcs.xml.dist .
```
