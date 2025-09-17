# Pomatio Framework

Pomatio Framework lets you describe WordPress admin interfaces with plain PHP arrays and have the library render, validate, and persist every field for you.【F:src/Pomatio_Framework.php†L11-L149】【F:src/Pomatio_Framework_Save.php†L10-L122】 The core bootstrap wires the helper, disk, settings, AJAX, save, and translation services on construction and automatically injects the required assets only on registered settings pages.【F:src/Pomatio_Framework.php†L14-L45】

## Key features

- **Declarative settings** – Declare tabs, subsections, and fields in PHP arrays and let `Pomatio_Framework_Settings::render()` output the markup and handle form submission.【F:src/Pomatio_Framework_Settings.php†L212-L371】【F:src/Pomatio_Framework_Save.php†L10-L122】
- **Dozens of reusable fields** – Everything from simple text inputs to repeaters, media pickers, icon pickers, code editors, and background builders are provided as drop-in field types.【F:src/Pomatio_Framework.php†L85-L149】【F:src/Fields/Repeater.php†L15-L209】
- **Automatic sanitization and persistence** – Each field maps to a sanitizer in `class-sanitize.php`, and the save handler writes enabled settings to the WordPress content directory in a multisite-aware location—or, when a field declares `save_as`, routes the clean value through `set_theme_mod()`/`update_option()` and records the metadata so runtime lookups know where to fetch it.【F:src/Pomatio_Framework_Save.php†L19-L152】【F:src/class-sanitize.php†L9-L360】【F:src/Pomatio_Framework_Settings.php†L46-L434】
- **Conditional logic and nesting** – Any field can declare dependencies and repeaters can contain other repeaters, allowing you to model complex configuration screens without bespoke PHP forms.【F:src/Pomatio_Framework_Helper.php†L27-L41】【F:src/Fields/Repeater.php†L45-L209】

## Quick start

1. **Register your admin page** and tell the framework about the hook suffix so assets load only where they are needed.【F:src/Pomatio_Framework.php†L33-L45】
2. **Return a settings array** from a PHP file describing your tabs, subsections, and field definitions.
3. **Render the form** by calling `Pomatio_Framework_Settings::render( $slug, $settings_array )` inside the page callback.【F:src/Pomatio_Framework_Settings.php†L212-L371】
4. **React after saving** via the `pomatio_framework_after_save_settings` action which fires after sanitization and persistence.【F:src/Pomatio_Framework_Save.php†L78-L122】

A minimal settings callback therefore looks like this:

```php
use PomatioFramework\Pomatio_Framework;
use PomatioFramework\Pomatio_Framework_Settings;

add_action('admin_menu', function () {
    $hook_suffix = add_submenu_page(
        'options-general.php',
        __('Demo settings', 'demo-slug'),
        __('Demo settings', 'demo-slug'),
        'manage_options',
        'demo-slug',
        'demo_settings_page'
    );

    Pomatio_Framework::register_settings_page($hook_suffix);
});

function demo_settings_page() {
    $settings = require __DIR__ . '/settings.php';
    Pomatio_Framework_Settings::render('demo-slug', $settings);
}
```

## Documentation map

| Topic | Description |
|-------|-------------|
| [Field reference](fields.md) | Every available field type, shared parameters, and advanced examples (including nested repeaters). |
| [Helper catalogue](helpers.md) | Utility methods for dependency data attributes, disk helpers, and runtime access to stored values. |
| [Settings pages](settings-page.md) | How to register tabs, subsections, callbacks, and render navigation. |

Use these guides together with the rich field library in `src/Fields` to assemble sophisticated configuration panels without rebuilding boilerplate each time.【F:src/Pomatio_Framework.php†L85-L149】
