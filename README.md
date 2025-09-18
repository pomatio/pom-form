# Pomatio Framework

Pomatio Framework is a WordPress helper library that lets you describe admin interfaces with declarative PHP arrays, then takes care of rendering, validating, translating, and persisting the resulting settings. Whether you are building a productised plugin or a bespoke theme, the framework saves you from writing repetitive option-page boilerplate while still giving you hooks into every stage of the lifecycle.

The framework allows to save each field in independent settings PHP files, as WP default options, and as theme mods.

![Placeholder – Framework overview](docs/images/framework-overview-placeholder.png)
<!-- TODO: Replace with an annotated screenshot of a settings page rendered by Pomatio Framework. -->

## Why `php settings files` Are the Right Place for Theme and Plugin Configuration

In professional WordPress projects —with CI/CD, multiple environments, and many collaborators— theme configuration and plugin settings should be **deterministic, versioned, and auditable**. Storing them in a PHP file provides clear advantages over using `theme_mods` or `options` from the database.

# Performance Justification for Using `php settings files`

Using a php settings file for theme and plugin configuration can **significantly improve performance** compared to storing these values in the database (`options` or `theme_mods`). Here’s why — and how to justify it.

## Why It’s (Usually) Faster

### 1. Zero DB Round-Trips on Cold Start
- `require_once` is a single filesystem read.
- `get_option()`/`get_theme_mod()` need database hits unless values are autoloaded *and* the object cache is warm. On cache-cold requests (or after cache evictions), that adds latency.

### 2. OPcache Wins (Compiled PHP vs DB Fetch)
- PHP files are cached in OPcache as bytecode. After the first request, loading the settings array is almost free.
- DB values require deserialization (`unserialize` / `json_decode`), type checking, and often multiple queries.

### 3. Smaller Autoload Payload
- Large configuration stored in `wp_options` with `autoload = yes` inflates the autoload payload loaded on *every* request.
- Moving config to a PHP file trims that payload and improves TTFB across the board.

### 4. No Scattered Lookups
- Multiple `get_option()` calls incur overhead (function call + cache key + serialization) for each read.
- One PHP array gives you O(1) hash lookups in memory.

### 5. More Predictable Cache Behavior
- Files are cached in both the OS page cache and OPcache.
- Object cache (Redis/Memcached) can evict keys under memory pressure, causing random regressions. A file + OPcache is more stable under load.

### 6. Cheaper Deploy-Time Invalidation
- Code deploys naturally invalidate OPcache for the file.
- No need to flush object caches or wait for TTLs to expire.

---

## Extra advantages

### 1. True Version Control
- Configuration travels with code: PRs, diffs, reviews, and rollbacks are trivial.
- Prevents “drift” between environments (dev/staging/production) caused by manual DB edits.

### 2. Reproducible Deployments
- Each release ships with a known configuration state.
- Rolling back means simply checking out a previous commit — no database restores needed.

### 3. Performance and Predictability
- A single PHP array load is faster and more reliable than scattered `get_option()` calls.
- No fragile serialization, no complex schema migrations across environments.

### 4. Operational Safety
- Allows **immutable production environments** (read-only filesystem, GitOps workflow).

### 5. Decoupled from Theme Lifecycle
- `theme_mods` are tied to a specific theme; `php settings files` stays consistent even when switching themes.

### 6. Less Database Bloat
- Avoids polluting `wp_options` or loading dozens of autoloaded keys.
- Keeps the DB for **content and state** while code holds the **behavior**.

---

## Common Objections

**“Marketing needs to change colors via UI.”**  
→ Pomatio Framework is meant for creating cool admin settings UI that **writes to the file**.

**“What about API secrets or passwords?”**  
→ For sensitive information, you should not store secrets in `php settings files`. That is why Pomatio Framework allows storing fields as WP options or as theme_mods, using the `save_as` flag.

---

## What Belongs in `php settings files` (Examples)

- **Design/base**: color palette, typography, spacing, breakpoints, enabled components.
- **Frontend behavior**: pagination defaults, date formats, masks, cache rules.
- **Integrations (non-sensitive)**: container IDs, feature flags, taxonomy slugs, asset paths.

---

## When to **Avoid** Using the File (and Use DB Instead)

- **User preferences** or frequently changing data.
- **Translatable editorial content** managed by non-technical users.
- **Ephemeral data** like counters, temporary tokens, or session info.

---

## Conclusion

`wp-content/php settings files` brings theme configuration back to where it belongs: **alongside the code that uses it**.  
It ensures reproducibility, traceability, and change control, while keeping the database focused on **content and state**.  

## Table of contents

- [Overview](#overview)
  - [Key features](#key-features)
  - [Quick start](#quick-start)
- [Requirements](#requirements)
- [Installation](#installation)
- [Example project structure](#example-project-structure)
  - [Runtime storage locations](#runtime-storage-locations)
- [Step-by-step integration tutorial](#step-by-step-integration-tutorial)
  - [1. Bootstrap your admin class](#1-bootstrap-your-admin-class)
  - [2. Register hooks and load enabled modules](#2-register-hooks-and-load-enabled-modules)
  - [3. Register the settings page with WordPress and the framework](#3-register-the-settings-page-with-wordpress-and-the-framework)
  - [4. Describe tabs and fields in a settings definition file](#4-describe-tabs-and-fields-in-a-settings-definition-file)
- [Documentation map](#documentation-map)
- [Field reference](#field-reference)
  - [Defining fields](#defining-fields)
  - [Common parameters](#common-parameters)
  - [External storage with `save_as`](#external-storage-with-save_as)
  - [Dependencies and conditional logic](#dependencies-and-conditional-logic)
  - [Working with repeaters](#working-with-repeaters)
  - [Field library](#field-library)
    - [Single-line inputs](#single-line-inputs)
    - [Multi-line editors](#multi-line-editors)
    - [Choice fields](#choice-fields)
    - [Media pickers and assets](#media-pickers-and-assets)
    - [Structural helpers](#structural-helpers)
    - [Per-field notes](#per-field-notes)
- [Helper classes](#helper-classes)
- [Settings pages in depth](#settings-pages-in-depth)
  - [Registering the admin page](#registering-the-admin-page)
  - [Rendering the settings form](#rendering-the-settings-form)
  - [Overriding tab content](#overriding-tab-content)
  - [Rendering the tab navigation](#rendering-the-tab-navigation)
  - [Handling callbacks after saving](#handling-callbacks-after-saving)
  - [Loading tweak definitions](#loading-tweak-definitions)
  - [Retrieving saved values](#retrieving-saved-values)
- [Additional resources](#additional-resources)

## Overview

Pomatio Framework wires together helper, disk, settings, AJAX, save, and translation services as soon as you instantiate the main class. That bootstrap sequence ensures the framework injects scripts and styles only on registered settings pages, keeps sanitizers in sync with field definitions, and exposes helper methods for runtime lookups.

### Key features

- **Declarative settings** – Declare tabs, subsections, and fields in PHP arrays and let `Pomatio_Framework_Settings::render()` output the markup and handle form submission for you.【F:src/Pomatio_Framework_Settings.php†L212-L371】【F:src/Pomatio_Framework_Save.php†L10-L122】
- **Extensive field library** – Text inputs, repeaters, media pickers, icon pickers, code editors, background builders, and more are available as drop-in field types.【F:src/Pomatio_Framework.php†L85-L149】【F:src/Fields/Repeater.php†L15-L209】
- **Automatic sanitization and persistence** – Each field maps to a sanitizer in `class-sanitize.php`, and the save handler writes enabled settings to multisite-aware directories inside `wp-content/settings`.【F:src/Pomatio_Framework_Save.php†L19-L123】【F:src/class-sanitize.php†L9-L360】【F:src/Pomatio_Framework_Disk.php†L128-L189】
- **Flexible storage targets** – Add `save_as` to store individual settings as theme mods or WordPress options while keeping the admin UI, getters, and translation registry in sync.【F:src/Pomatio_Framework_Save.php†L24-L180】【F:src/Pomatio_Framework_Settings.php†L8-L105】【F:src/Pomatio_Framework_Translations.php†L20-L67】
- **Conditional logic and nesting** – Any field can declare dependencies, and repeaters can nest other repeaters to model sophisticated configuration screens without bespoke forms.【F:src/Pomatio_Framework_Helper.php†L27-L41】【F:src/Fields/Repeater.php†L45-L209】

### Quick start

1. **Register your admin page** and tell the framework about the hook suffix so assets load only when required.【F:src/Pomatio_Framework.php†L33-L45】
2. **Return a settings array** from a PHP file describing tabs, subsections, and field definitions.
3. **Render the form** by calling `Pomatio_Framework_Settings::render( $slug, $settings_array )` inside the admin page callback.【F:src/Pomatio_Framework_Settings.php†L212-L371】
4. **React after saving** via the `pomatio_framework_after_save_settings` action, which fires after sanitization and persistence.【F:src/Pomatio_Framework_Save.php†L78-L122】

```php
use PomatioFramework\Pomatio_Framework;
use PomatioFramework\Pomatio_Framework_Settings;

add_action('admin_menu', function () {
    $hook_suffix = add_submenu_page(
        'options-general.php',
        __('Demo settings', 'your-plugin'),
        __('Demo settings', 'your-plugin'),
        'manage_options',
        'your-plugin',
        'your_plugin_render_settings_page'
    );

    Pomatio_Framework::register_settings_page($hook_suffix);
});

function your_plugin_render_settings_page() {
    $settings = require __DIR__ . '/settings.php';
    Pomatio_Framework_Settings::render('your-plugin', $settings);
}
```

## Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher
- Composer (to install the package and its autoload file)

## Installation

Install the package in your plugin or theme directory with Composer:

```bash
composer require pom/form
```

If you bundle the framework inside a distributed plugin or theme, include Composer’s autoload file before bootstrapping the framework:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

## Example project structure

A minimal plugin that uses Pomatio Framework to render settings might look like this:

```
wp-content/
└── plugins/
    └── your-plugin/
        ├── admin/
        │   ├── class-admin.php
        │   └── settings.php
        ├── assets/
        │   ├── css/
        │   └── js/
        ├── settings/
        │   ├── module-subdirectory
        │   │   ├── fields.php -> The settings to be rendered on the admin page for that module configuration.
        │   │   └── bootstrap.php -> What's actually included / executed when that module is enabled. 
        │   │           If it is  not present, you can still use the settings in other parts of your theme or plugin.
        │   └── other-module-subdirectory
        ├── vendor/
        │   └── autoload.php
        └── your-plugin.php (plugin bootstrap file)
```

### Runtime storage locations

At runtime the framework stores configuration files under `wp-content/settings/pomatio-framework/sites/<site-id>/<slug>/`. In the structure above, the slug is `your-plugin`, so the file `enabled_settings.php` lives at `wp-content/settings/pomatio-framework/sites/<site-id>/your-plugin/enabled_settings.php`.

![Placeholder – Settings storage diagram](docs/images/settings-storage-placeholder.png)
<!-- TODO: Replace with a diagram illustrating how slugs map to storage directories. -->

## Step-by-step integration tutorial

The walkthrough below mirrors how a typical admin class wires Pomatio Framework into a plugin. Copy the snippets into your own project and adjust namespaces, slugs, and asset paths as needed.

### 1. Bootstrap your admin class

Create `admin/class-admin.php` and instantiate it from your plugin’s main file:

```php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/admin/class-admin.php';

ew Your_Plugin_Admin_Settings();
```

### 2. Register hooks and load enabled modules

Inside `Your_Plugin_Admin_Settings` add the WordPress and framework hooks you need. The snippet below registers scripts, AJAX handlers, and dashboard UI pieces. The key call is `Pomatio_Framework_Settings::get_effective_enabled_settings()`, which reads the stored module flags and loads their bootstrap files dynamically.

```php
use PomatioFramework\Pomatio_Framework_Disk;
use PomatioFramework\Pomatio_Framework_Settings;

class Your_Plugin_Admin_Settings {
    public function __construct() {
        add_action('admin_footer', [$this, 'render_dashboard_banner']);
        add_action('wp_ajax_your_plugin_get_credits', [$this, 'get_credits']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_action('admin_menu', [$this, 'register_options_menu']);
        add_action('in_admin_header', [$this, 'render_settings_tabs']);

        $settings_definition = require YOUR_PLUGIN_PATH . 'admin/settings.php';
        $enabled_settings = Pomatio_Framework_Settings::get_effective_enabled_settings('your-plugin', $settings_definition);

        foreach ($enabled_settings as $module => $status) {
            if ($status === '1') {
                $module_path = YOUR_PLUGIN_PATH . "settings/{$module}/";

                if (file_exists("{$module_path}bootstrap.php")) {
                    include "{$module_path}bootstrap.php";
                }
            }
        }
    }

    // ... other methods (enqueue_scripts, get_credits, etc.) ...
}
```

`Pomatio_Framework_Settings::get_effective_enabled_settings()` resolves the correct storage directory, merges stored flags with any modules marked `requires_initialization => false`, and returns an up-to-date array you can iterate to bootstrap functionality without touching PHP code.

### 3. Register the settings page with WordPress and the framework

Use `add_submenu_page()` to create a submenu under “Settings → Your Plugin”. Save the return value in `$hook_suffix`, then register that hook with the framework so its assets load only when the page is active.

```php
public function register_options_menu(): void {
    $hook_suffix = add_submenu_page(
        'options-general.php',
        __('Your Plugin settings', 'your-plugin'),
        __('Your Plugin settings', 'your-plugin'),
        'manage_options',
        'your-plugin',
        [$this, 'render_settings_page']
    );

    if (class_exists('PomatioFramework\\Pomatio_Framework')) {
        PomatioFramework\Pomatio_Framework::register_settings_page($hook_suffix);
    }
}
```

### 4. Describe tabs and fields in a settings definition file

The settings definition file returns an associative array grouped by tab.

```php
use PomatioFramework\Pomatio_Framework_Settings;
use PomatioFramework\Pomatio_Framework_Disk;

$your_plugin_settings['config'] = [
    'settings_dir' => YOUR_PLUGIN_PATH . 'settings',
];

$your_plugin_settings['general'] = [
    'title' => __('General setup', 'your-plugin'),
    'allowed_roles' => ['administrator', 'editor'],
    'tab' => [
        'general' => [
            'title' => __('Basic configuration', 'your-plugin'),
            'description' => __('Foundational toggles for Your Plugin.', 'your-plugin'),
            'settings' => [
                'enable-tools' => [
                    'title' => __('Enable tools', 'your-plugin'),
                    'description' => __('Check this to enable core features.', 'your-plugin'),
                    'heading_checkbox' => __('Globally enable features', 'your-plugin'),
                    'label_checkbox' => __('Check this to activate the feature set.', 'your-plugin'),
                    'requires_initialization' => true,
                ],
            ],
        ],
    ],
];

$enabled_settings = Pomatio_Framework_Settings::get_effective_enabled_settings('your-plugin', $your_plugin_settings);

if (!empty($enabled_settings['enable-tools'])) {
    $your_plugin_settings['api'] = [
        'title' => __('API keys', 'your-plugin'),
        'allowed_roles' => ['administrator'],
        'tab' => [
            'api' => [
                'title' => __('Credentials', 'your-plugin'),
                'description' => __('Store API tokens required for integrations.', 'your-plugin'),
                'settings' => [
                    'api-key' => [
                        'type' => 'text',
                        'label' => __('Primary API key', 'your-plugin'),
                        'description' => __('Paste the token issued by your service provider.', 'your-plugin'),
                    ],
                    'api-secret' => [
                        'type' => 'password',
                        'label' => __('API secret', 'your-plugin'),
                        'description' => __('Displayed only once—store it securely.', 'your-plugin'),
                    ],
                ],
            ],
        ],
    ];
}

return $your_plugin_settings;
```

## Documentation map

Use the sections below to explore each facet of the framework in depth. They replace the previous `/docs` folder so you can find everything in one place.

| Topic | Description |
|-------|-------------|
| [Field reference](#field-reference) | Every available field type, shared parameters, and advanced examples (including nested repeaters). |
| [Helper classes](#helper-classes) | Utility methods for dependency data attributes, disk helpers, and runtime access to stored values. |
| [Settings pages in depth](#settings-pages-in-depth) | How to register tabs, subsections, callbacks, and render navigation. |

![Placeholder – Field reference collage](docs/images/field-reference-placeholder.png)
<!-- TODO: Replace with a montage of the various field types rendered in the WordPress admin. -->

## Field reference

This section consolidates the complete field documentation so you can configure interfaces without switching files.

### Defining fields

Every field in Pomatio Framework is declared as a PHP array and rendered by `Pomatio_Framework::add_field()`, which normalises the arguments and locates the matching renderer under `src/Fields`.【F:src/Pomatio_Framework.php†L58-L149】 The framework injects scripts such as CodeMirror, Select2, colour pickers, and repeater helpers on demand based on the field types present in your definition.【F:src/Pomatio_Framework.php†L103-L149】

```php
[
    'type'        => 'text',
    'name'        => 'cta-title',
    'label'       => __('CTA title', 'your-plugin'),
    'placeholder' => __('Launch offer…', 'your-plugin'),
    'description' => __('Shown on the homepage hero.', 'your-plugin'),
]
```

### Common parameters

| Key | Purpose |
|-----|---------|
| `label`, `description` | Human-friendly text rendered above or below the field; the position defaults to `under_field` but can be switched to `below_label`.【F:src/Pomatio_Framework.php†L61-L75】【F:src/Fields/Text.php†L15-L43】 |
| `default`, `value` | The renderer prefers a saved `value`, then falls back to `default`, so you can pre-populate fields while letting users overwrite them.【F:src/Fields/Text.php†L26-L36】 |
| `class`, `id` | Custom classes/IDs appended to the generated markup after being sanitised, handy for styling or JavaScript hooks.【F:src/Pomatio_Framework.php†L65-L69】【F:src/Fields/Textarea.php†L31-L36】 |
| `disabled` | Pass `true` to render a disabled input without losing the value on save—useful for templates or repeater defaults.【F:src/Fields/Text.php†L10-L44】 |
| `dependency` | Define conditional logic that is serialised into `data-dependencies` so JavaScript can hide or show the field based on other values.【F:src/Pomatio_Framework_Helper.php†L27-L41】 |
| `save_as` | Optional string that re-routes persistence to a theme mod or WordPress option. See [External storage with `save_as`](#external-storage-with-save_as) for the supported values.【F:src/Pomatio_Framework_Save.php†L24-L151】 |

Because each field posts back under its `name`, `Pomatio_Framework_Save::save_settings()` can detect the field type, run the corresponding sanitizer from `class-sanitize.php`, and persist a clean value to disk.【F:src/Pomatio_Framework_Save.php†L32-L123】【F:src/class-sanitize.php†L9-L360】

### External storage with `save_as`

Setting `save_as` on a field instructs the saver to sanitise the payload and forward it to either the theme-mod API or the options table instead of appending it to the generated `<setting>.php` file. The saver records the routing in `fields_save_as.php` so the renderer, sanitizers, and translation helpers can look up the correct source later.【F:src/Pomatio_Framework_Save.php†L24-L180】【F:src/Pomatio_Framework_Settings.php†L8-L105】【F:src/Pomatio_Framework_Translations.php†L20-L67】

| `save_as` value | Storage target |
|-----------------|----------------|
| _Omitted / `default`_ | Keeps the standard behaviour and stores values in the PHP array file. |
| `theme_mod` | Uses `set_theme_mod()` / `get_theme_mod()` with the field name as the key. |
| `option_autoload_yes` | Calls `update_option()` with autoload forced to `'yes'`. |
| `option_autoload_no` | Calls `update_option()` with autoload forced to `'no'`. |
| `option_autoload_auto` | Calls `update_option()` without the autoload flag so WordPress chooses the appropriate behaviour. |

Examples:

```php
return [
    [
        'type'    => 'text',
        'name'    => 'footer_note',
        'label'   => __('Footer note', 'your-plugin'),
        // Stored in <setting>.php because save_as is omitted.
    ],
    [
        'type'    => 'Image_Picker',
        'name'    => 'header_logo',
        'label'   => __('Header logo', 'your-plugin'),
        'default' => '',
        'save_as' => 'theme_mod',
    ],
    [
        'type'    => 'Toggle',
        'name'    => 'feature_flag',
        'label'   => __('Enable feature', 'your-plugin'),
        'default' => false,
        'save_as' => 'option_autoload_yes',
    ],
    [
        'type'    => 'Textarea',
        'name'    => 'legal_blurb',
        'label'   => __('Legal blurb', 'your-plugin'),
        'default' => '',
        'save_as' => 'option_autoload_no',
    ],
    [
        'type'    => 'Text',
        'name'    => 'utm_source',
        'label'   => __('UTM source override', 'your-plugin'),
        'save_as' => 'option_autoload_auto',
    ],
];
```

When the administrator saves the page the framework writes the alternative targets, updates `fields_save_as.php`, and invalidates opcache so subsequent requests pick up the change immediately.【F:src/Pomatio_Framework_Save.php†L135-L173】 `Pomatio_Framework_Settings::get_setting_value()` consults that metadata before it sanitises the value, meaning your front-end code can continue to call the helper without caring whether the value lives in a PHP array, a theme mod, or an option. The settings renderer and the translation registrar reuse the same lookup, so fields remain pre-populated in the admin UI and Polylang registrations stay in sync regardless of the storage strategy.【F:src/Pomatio_Framework_Settings.php†L8-L105】【F:src/Pomatio_Framework_Settings.php†L487-L547】【F:src/Pomatio_Framework_Translations.php†L20-L67】

### Dependencies and conditional logic

Any field can react to other inputs by providing a `dependency` array. `Pomatio_Framework_Helper::get_dependencies_data_attr()` JSON-encodes the structure and stores it as a data attribute without forcing you to write boilerplate, so repeaters, selects, and custom inputs can all participate in complex flows.【F:src/Pomatio_Framework_Helper.php†L27-L41】【F:src/Fields/Select.php†L10-L41】

### Working with repeaters

The repeater field is a container that renders a list of child fields, supports defaults, cloning, sorting, nested repeaters, and per-item dependencies.【F:src/Fields/Repeater.php†L15-L209】 It stores its configuration alongside the saved values in a hidden input (`data-type="repeater"`), and during sanitisation each nested field is processed with its own sanitizer so code editors are written to disk while plain inputs remain inline.【F:src/Fields/Repeater.php†L210-L254】【F:src/class-sanitize.php†L264-L327】

```php
return [
    [
        'type'        => 'repeater',
        'name'        => 'rules',
        'label'       => __('Rules', 'your-plugin'),
        'max_items'   => 5,
        'title_field' => 'rule_name',
        'fields'      => [
            [
                'type'           => 'Text',
                'name'           => 'rule_name',
                'label'          => __('Rule name', 'your-plugin'),
                'used_for_title' => true,
            ],
            [
                'type'   => 'repeater',
                'title'  => __('Rule', 'your-plugin'),
                'name'   => 'rule',
                'fields' => [
                    [
                        'type'           => 'Text',
                        'name'           => 'rule_name',
                        'label'          => __('Rule name', 'your-plugin'),
                        'used_for_title' => true,
                    ],
                    [
                        'type'    => 'select',
                        'name'    => 'rule_type',
                        'label'   => __('Rule type', 'your-plugin'),
                        'options' => $rule_types,
                        'default' => 'none',
                    ],
                    [
                        'type'       => 'select',
                        'name'       => 'roles',
                        'multiple'   => true,
                        'label'      => __('Roles', 'your-plugin'),
                        'options'    => your_plugin_get_roles(true),
                        'dependency' => [[[ 'field' => 'rule_type', 'values' => ['role_is', 'role_is_not'] ]]],
                    ],
                    // … additional dependent selects and number fields …
                ],
            ],
            [
                'type'    => 'select',
                'name'    => 'condition_between_rules',
                'label'   => __('Rule condition', 'your-plugin'),
                'options' => [
                    ''   => __('Set a condition', 'your-plugin'),
                    'and' => __('AND', 'your-plugin'),
                    'or'  => __('OR', 'your-plugin'),
                ],
                'default' => 'and',
            ],
        ],
    ],
];
```

When this structure is posted, `sanitize_pom_form_repeater()` walks each layer, enforces the limit, and delegates to the appropriate sanitizers (`sanitize_pom_form_select`, `sanitize_pom_form_number`, etc.) so the saved JSON mirrors the UI without exposing unsanitised input.【F:src/class-sanitize.php†L264-L327】

### Field library

Use the grouped reference below to pick the right control and learn about its quirks.

#### Single-line inputs

Text, Email, Url, Tel, Password, Number, Date, Datetime, and Time all follow the same pattern: they render labelled `<input>` elements, honour placeholders and defaults, propagate dependency attributes, and respect a `disabled` flag.【F:src/Fields/Text.php†L9-L44】【F:src/Fields/Email.php†L9-L42】【F:src/Fields/Url.php†L9-L44】【F:src/Fields/Tel.php†L9-L44】【F:src/Fields/Password.php†L9-L44】【F:src/Fields/Number.php†L9-L44】【F:src/Fields/Date.php†L9-L44】【F:src/Fields/Datetime.php†L9-L44】【F:src/Fields/Time.php†L9-L41】 Their values are cleaned by dedicated sanitizers such as `sanitize_pom_form_text()`, `sanitize_pom_form_email()`, `sanitize_pom_form_url()`, `sanitize_pom_form_tel()`, `sanitize_pom_form_number()`, `sanitize_pom_form_date()`, `sanitize_pom_form_datetime()`, and `sanitize_pom_form_time()` so you can trust the persisted data.【F:src/class-sanitize.php†L108-L205】【F:src/class-sanitize.php†L343-L360】

#### Multi-line editors

Textarea outputs a plain `<textarea>` and honours dependency metadata, whereas TinyMCE wraps `wp_editor()` so you can control toolbar options via `custom_attrs` like `textarea_rows`, `teeny`, `quicktags`, `wpautop`, and `media_buttons`.【F:src/Fields/Textarea.php†L9-L44】【F:src/Fields/Tinymce.php†L10-L45】 Code editors (`code_html`, `code_css`, `code_js`, `code_json`) render CodeMirror-backed textareas, fetch the stored file contents if the value is a path, and enqueue the editor assets automatically.【F:src/Fields/Code_HTML.php†L10-L53】【F:src/Pomatio_Framework.php†L103-L149】 Corresponding sanitizers ensure HTML is KSES-filtered, CSS/JS/JSON are stripped of unsafe content, and CodeMirror values are saved to disk when nested inside repeaters.【F:src/class-sanitize.php†L53-L121】【F:src/class-sanitize.php†L295-L306】

#### Choice fields

Checkbox, Radio, Toggle, Select, Range, Quantity, Color, and Color Palette provide rich selection UIs:

- **Checkbox** renders either a single yes/no toggle or multiple checkboxes with an array value, seeding “no” via a hidden input for unchecked states.【F:src/Fields/Checkbox.php†L7-L52】 `sanitize_pom_form_checkbox()` converts the payload into `yes`/`no` strings or an array of selected keys.【F:src/class-sanitize.php†L29-L45】
- **Radio** and **Radio Icons** output mutually exclusive options, the latter loading SVG icons from disk and providing a restore-default shortcut.【F:src/Fields/Radio.php†L7-L42】【F:src/Fields/Radio_Icons.php†L7-L74】 Both rely on `sanitize_pom_form_radio()`/`sanitize_pom_form_radio_icons()` to strip unwanted characters.【F:src/class-sanitize.php†L215-L224】
- **Toggle** wraps a styled checkbox with automatic IDs and enqueues the CSS needed for the slider UI.【F:src/Fields/Toggle.php†L9-L49】
- **Select** supports single or multiple values, optgroups, dependency metadata, and automatically enqueues the Select2-compatible script when used inside repeaters.【F:src/Fields/Select.php†L9-L62】【F:src/Pomatio_Framework.php†L125-L132】 `sanitize_pom_form_select()` stores multi-select choices as comma-separated strings for convenience.【F:src/class-sanitize.php†L319-L326】
- **Range** renders a slider paired with a numeric input and optional suffix, with helpers to restore the default value.【F:src/Fields/Range.php†L7-L69】 Sanitization allows fractional numbers via `sanitize_pom_form_range()`.【F:src/class-sanitize.php†L227-L231】
- **Quantity** provides plus/minus controls around a numeric input, enqueuing the JS required to bump the value, while `sanitize_pom_form_quantity()` normalises the number.【F:src/Fields/Quantity.php†L9-L48】【F:src/class-sanitize.php†L209-L212】
- **Color** and **Color Palette** integrate with the WordPress color picker and custom palette filters, storing hex values through `sanitize_pom_form_color()`/`sanitize_pom_form_color_palette()`.【F:src/Fields/Color.php†L7-L43】【F:src/Fields/Color_Palette.php†L7-L87】【F:src/class-sanitize.php†L96-L105】

#### Media pickers and assets

File, Image Picker, Gallery, Icon Picker, Signature, Background Image, Font, and Font Picker are specialised helpers for handling uploads:

- **File** exposes a simple `<input type="file">` and delegates further processing to your save callback; the default sanitizer allows you to filter file types via WordPress filters.【F:src/Fields/File.php†L7-L30】【F:src/class-sanitize.php†L130-L147】
- **Image Picker** and **Gallery** open the WordPress media modal, store selected IDs/URLs, and enqueue their own CSS/JS wrappers.【F:src/Fields/Image_Picker.php†L7-L61】【F:src/Fields/Gallery.php†L7-L64】 `sanitize_pom_form_image_picker()` and `sanitize_pom_form_gallery()` ensure URLs are valid and gallery IDs contain only digits/commas.【F:src/class-sanitize.php†L157-L193】
- **Icon Picker** lists the available icon libraries (filterable via `Pomatio_Framework_Helper::get_icon_libraries()`) and lets the user choose an SVG; the stored value is sanitised as a URL.【F:src/Fields/Icon_Picker.php†L7-L113】【F:src/Pomatio_Framework_Helper.php†L79-L92】【F:src/class-sanitize.php†L164-L179】
- **Signature** renders a canvas-based signature pad, saves the base64 image via `Pomatio_Framework_Disk::save_signature_image()`, and sanitises through `sanitize_pom_form_signature()`.【F:src/Fields/Signature.php†L7-L52】【F:src/Pomatio_Framework_Disk.php†L84-L119】【F:src/class-sanitize.php†L329-L333】
- **Background Image** combines multiple sub-fields (image picker, alignment radios, size selectors) inside a wrapper so you can capture every CSS background property from a single composite field.【F:src/Fields/Background_Image.php†L10-L140】 The posted data is JSON-encoded and decoded by `sanitize_pom_form_background_image()`.【F:src/class-sanitize.php†L9-L21】
- **Font** uses nested repeaters to collect font families, fallbacks, and variant metadata, delegating each variant to the `Font_Picker` field for file uploads.【F:src/Fields/Font.php†L9-L111】【F:src/Fields/Font_Picker.php†L9-L52】 Font uploads are restricted to the extensions returned by `Pomatio_Framework_Helper::get_allowed_font_types()` and sanitised via `sanitize_pom_form_font()` / `sanitize_pom_form_font_picker()`.【F:src/Pomatio_Framework_Helper.php†L360-L371】【F:src/class-sanitize.php†L233-L260】

#### Structural helpers

Button, Hidden, Separator, and Background Image wrappers help you compose the layout around your fields:

- **Button** renders either an `<input type="button">`/`submit` or an `<a>` tag when you pass `link => true`, making it ideal for secondary actions like resets or downloads.【F:src/Fields/Button.php†L7-L31】
- **Hidden** stores metadata alongside the form without any UI.【F:src/Fields/Hidden.php†L7-L23】
- **Separator** prints headings and paragraphs to break long forms into digestible sections.【F:src/Fields/Separator.php†L7-L15】

#### Per-field notes

The following subsections consolidate the individual field notes previously stored in `docs/fields/*.md`.

##### Button

Button renders either an `<input>` or `<a>` element depending on the `link` flag, making it ideal for custom actions alongside your settings, and the value passes through `sanitize_pom_form_button()` during saving.【F:src/Fields/Button.php†L7-L31】【F:src/class-sanitize.php†L23-L26】 See [Structural helpers](#structural-helpers) for more context.

##### Checkbox

Checkbox supports both single yes/no toggles and multi-select lists, always posting a hidden “no” value to handle unchecked states and sanitising results through `sanitize_pom_form_checkbox()`.【F:src/Fields/Checkbox.php†L7-L52】【F:src/class-sanitize.php†L29-L45】 See [Choice fields](#choice-fields) for examples.

##### Code CSS

The CSS code editor renders a CodeMirror textarea with dependency support, reloads saved files when present, and hands the raw content to `sanitize_pom_form_code_css()` (which you can further extend) before storing or exporting it.【F:src/Fields/Code_CSS.php†L9-L52】【F:src/Pomatio_Framework.php†L103-L149】【F:src/class-sanitize.php†L53-L63】 See [Multi-line editors](#multi-line-editors) for the complete reference.

##### Code HTML

The HTML code editor injects a CodeMirror textarea, loads any previously saved file contents, and filters the output with `sanitize_pom_form_code_html()` so only whitelisted markup is stored.【F:src/Fields/Code_HTML.php†L9-L53】【F:src/Pomatio_Framework.php†L103-L149】【F:src/class-sanitize.php†L65-L78】 See [Multi-line editors](#multi-line-editors) for advanced configuration.

##### Code JS

The JavaScript code editor loads CodeMirror with the proper settings, reuses saved files when present, and sanitises the payload with `sanitize_pom_form_code_js()` before it is stored or written to disk from within repeaters.【F:src/Fields/Code_JS.php†L9-L52】【F:src/Pomatio_Framework.php†L103-L149】【F:src/class-sanitize.php†L80-L94】 See [Multi-line editors](#multi-line-editors) for shared options across code fields.

##### Code JSON

The JSON code editor works like the other CodeMirror-based fields, preloading saved files and sanitising the content with `sanitize_pom_form_code_json()` before persistence or file export from repeaters.【F:src/Fields/Code_JSON.php†L9-L52】【F:src/Pomatio_Framework.php†L103-L149】【F:src/class-sanitize.php†L88-L94】 See [Multi-line editors](#multi-line-editors) for usage patterns.

##### Color Palette

Color Palette displays a set of predefined swatches (optionally with icons) and persists the selection via `sanitize_pom_form_color_palette()`, including a restore-default shortcut when a fallback is provided.【F:src/Fields/Color_Palette.php†L7-L87】【F:src/class-sanitize.php†L102-L105】 See [Choice fields](#choice-fields) for configuration examples.

##### Color

Color integrates the WordPress colour picker, supports repeater titles, and stores hex values cleaned by `sanitize_pom_form_color()` so you can safely reuse them in CSS.【F:src/Fields/Color.php†L7-L43】【F:src/class-sanitize.php†L96-L99】 See [Choice fields](#choice-fields) for usage guidance.

##### Date

Date renders an `<input type="date">` with optional defaults and dependency attributes, then normalises the value through `sanitize_pom_form_date()` before it is written to disk.【F:src/Fields/Date.php†L9-L44】【F:src/class-sanitize.php†L108-L114】 See [Single-line inputs](#single-line-inputs) for usage guidance.

##### Datetime

Datetime renders an `<input type="datetime-local">` (with defaults and dependencies) and converts the submitted value into a normalised timestamp using `sanitize_pom_form_datetime()` before persistence.【F:src/Fields/Datetime.php†L9-L44】【F:src/class-sanitize.php†L116-L122】 See [Single-line inputs](#single-line-inputs) for more information.

##### Email

Email renders an `<input type="email">` with placeholder and dependency support, and sanitizes submissions with `sanitize_pom_form_email()` to guarantee a valid address.【F:src/Fields/Email.php†L9-L41】【F:src/class-sanitize.php†L124-L128】 See [Single-line inputs](#single-line-inputs) for more context.

##### File

File renders a standard file input that honours labels and descriptions while delegating sanitization to `sanitize_pom_form_file()`, which lets you filter maximum sizes and allowed MIME types through WordPress filters.【F:src/Fields/File.php†L7-L30】【F:src/class-sanitize.php†L130-L147】 See [Media pickers and assets](#media-pickers-and-assets) for integration advice.

##### Gallery

Gallery opens the media modal in multi-select mode, renders thumbnails for each chosen attachment, and stores their IDs in a hidden input that is sanitised by `sanitize_pom_form_gallery()` to contain only digits and commas.【F:src/Fields/Gallery.php†L7-L64】【F:src/class-sanitize.php†L157-L160】 See [Media pickers and assets](#media-pickers-and-assets) for practical tips.

##### Hidden

Hidden fields store metadata alongside your form without any visible UI, using the same default/override logic as other inputs and sanitising values via `sanitize_pom_form_hidden()`.【F:src/Fields/Hidden.php†L7-L23】【F:src/class-sanitize.php†L170-L179】 See [Structural helpers](#structural-helpers) for context.

##### Icon Picker

Icon Picker lists the available icon libraries, previews SVG glyphs, and saves the chosen asset URL while allowing you to filter the libraries via `Pomatio_Framework_Helper::get_icon_libraries()`.【F:src/Fields/Icon_Picker.php†L7-L113】【F:src/Pomatio_Framework_Helper.php†L79-L92】【F:src/class-sanitize.php†L164-L179】 See [Media pickers and assets](#media-pickers-and-assets) for configuration guidance.

##### Image Picker

Image Picker opens the WordPress media modal, renders the selected thumbnail, and stores the image URL via `sanitize_pom_form_image_picker()`, which validates the saved URL before use.【F:src/Fields/Image_Picker.php†L7-L61】【F:src/class-sanitize.php†L184-L194】 See [Media pickers and assets](#media-pickers-and-assets) for more examples.

##### Number

Number renders an HTML5 numeric input that respects defaults, dependencies, and disabled states, with `sanitize_pom_form_number()` ensuring the stored value contains only numeric characters.【F:src/Fields/Number.php†L9-L44】【F:src/class-sanitize.php†L197-L199】 See [Single-line inputs](#single-line-inputs) for parameter details.

##### Password

Password mirrors the Text field but renders an `<input type="password">` and sanitizes user input with `sanitize_pom_form_password()` before persistence.【F:src/Fields/Password.php†L9-L44】【F:src/class-sanitize.php†L203-L207】 See [Single-line inputs](#single-line-inputs) for shared options.

##### Quantity

Quantity wraps a number input with plus/minus controls, honours dependencies and defaults, enqueues its JavaScript helper, and persists the value through `sanitize_pom_form_quantity()` so only numeric data is stored.【F:src/Fields/Quantity.php†L9-L48】【F:src/class-sanitize.php†L209-L212】 See [Choice fields](#choice-fields) for configuration ideas.

##### Radio

Radio renders a group of mutually exclusive options, automatically selecting saved or default values and sanitising the choice with `sanitize_pom_form_radio()` to strip unsafe characters.【F:src/Fields/Radio.php†L7-L42】【F:src/class-sanitize.php†L215-L218】 See [Choice fields](#choice-fields) for advanced usage patterns.

##### Range

Range combines an HTML5 slider and numeric input, supports step/min/max constraints, and offers a restore-default control, with sanitization handled by `sanitize_pom_form_range()` to allow fractional values.【F:src/Fields/Range.php†L7-L69】【F:src/class-sanitize.php†L227-L231】 See [Choice fields](#choice-fields) for usage guidance.

##### Repeater

Repeaters let you define lists of nested field groups that can be cloned, sorted, limited, or seeded with defaults, all while respecting per-field dependencies and nested repeaters.【F:src/Fields/Repeater.php†L15-L209】 Saved values are stored as JSON and cleaned by `sanitize_pom_form_repeater()`, which runs the correct sanitizer for each child field (including writing code-editor content to disk).【F:src/Fields/Repeater.php†L210-L254】【F:src/class-sanitize.php†L264-L316】 See [Working with repeaters](#working-with-repeaters) for a full walkthrough and a production-ready example.

##### Select

Select renders a dropdown (or multiselect) with support for optgroups, dependency attributes, and automatic Select2 assets when used in repeaters, while `sanitize_pom_form_select()` normalises the saved values as comma-separated lists.【F:src/Fields/Select.php†L9-L62】【F:src/Pomatio_Framework.php†L125-L132】【F:src/class-sanitize.php†L319-L326】 See [Choice fields](#choice-fields) for usage notes and examples.

##### Telephone

Telephone renders an `<input type="tel">` with placeholder and dependency support, and validates international numbers using `sanitize_pom_form_tel()` so only well-formed phone numbers are stored.【F:src/Fields/Tel.php†L9-L44】【F:src/class-sanitize.php†L343-L348】 See [Single-line inputs](#single-line-inputs) for configuration patterns.

##### Text

The Text field renders a single-line `<input>` with optional placeholder, dependency metadata, and repeater title integration, then sanitizes the submitted value with `sanitize_pom_form_text()`.【F:src/Fields/Text.php†L9-L44】【F:src/class-sanitize.php†L351-L355】 See [Single-line inputs](#single-line-inputs) for full parameter details and examples.

##### Textarea

Textarea outputs a `<textarea>` element that honours description positioning, dependency metadata, and default values before the content is sanitised with `sanitize_pom_form_textarea()`.【F:src/Fields/Textarea.php†L9-L43】【F:src/class-sanitize.php†L357-L360】 See [Multi-line editors](#multi-line-editors) for configuration tips and examples.

##### Time

Time renders an `<input type="time">` with support for defaults, dependencies, and disabled states, validating the HH:MM format through `sanitize_pom_form_time()` during persistence.【F:src/Fields/Time.php†L9-L41】【F:src/class-sanitize.php†L371-L376】 See [Single-line inputs](#single-line-inputs) for configuration options.

##### TinyMCE

The TinyMCE field embeds `wp_editor()` with optional custom attributes (`textarea_rows`, `teeny`, `quicktags`, `wpautop`, `media_buttons`) and sanitises the rich text through `sanitize_pom_form_tinymce()` using the framework’s allowed HTML list.【F:src/Fields/Tinymce.php†L10-L45】【F:src/class-sanitize.php†L379-L384】【F:src/Pomatio_Framework_Helper.php†L130-L200】 See [Multi-line editors](#multi-line-editors) for guidance.

##### Toggle

Toggle renders a styled on/off switch backed by a checkbox, posts a hidden “no” value for unchecked states, enqueues the required CSS, and sanitises the setting via `sanitize_pom_form_toggle()`.【F:src/Fields/Toggle.php†L9-L49】【F:src/class-sanitize.php†L47-L50】 See [Choice fields](#choice-fields) for examples.

##### URL

URL renders an `<input type="url">` and sanitises submissions with `sanitize_pom_form_url()`, which preserves hash anchors while ensuring any external link is a valid URL.【F:src/Fields/Url.php†L9-L44】【F:src/class-sanitize.php†L387-L395】 See [Single-line inputs](#single-line-inputs) for usage.

## Helper classes

Pomatio Framework exposes several helper classes so you can inspect configuration, generate dependency metadata, and manipulate files without digging into WordPress internals yourself.

### `Pomatio_Framework_Helper`

- **Dependency attributes** – `get_dependencies_data_attr()` converts the `dependency` structure you define in a field into the JSON payload that drives conditional display in JavaScript.【F:src/Pomatio_Framework_Helper.php†L27-L41】
- **Utility conversions** – Use `convert_array_to_html_attributes()` and `convert_html_attributes_to_array()` to move between associative arrays and attribute strings when you extend the framework.【F:src/Pomatio_Framework_Helper.php†L44-L67】
- **Runtime helpers** – Functions such as `generate_random_string()`, `write_log()`, and `path_to_url()` help you build dynamic field IDs, debug issues, and turn filesystem paths into public URLs.【F:src/Pomatio_Framework_Helper.php†L69-L128】
- **Settings lookups** – `get_settings()` returns the raw settings array for a given tab/subsection and `get_allowed_html()` exposes the HTML tags that text editors sanitize against.【F:src/Pomatio_Framework_Helper.php†L130-L200】

### `Pomatio_Framework_Disk`

- **Automatic directories** – `create_settings_dir()` provisions `wp-content/settings/pomatio-framework/<site>/<slug>/` (including `.htaccess`) the first time a page saves values, making the storage multisite-aware.【F:src/Pomatio_Framework_Disk.php†L128-L156】
- **File serialization** – `generate_file_content()` turns an array into a PHP file with metadata headers, while `save_to_file()` writes arbitrary content such as code editor values and returns the saved path.【F:src/Pomatio_Framework_Disk.php†L170-L212】
- **Reading and cleanup** – `read_file()` and `delete_file()` give you convenient access to the stored configuration and let you remove generated assets when a module is disabled.【F:src/Pomatio_Framework_Disk.php†L214-L241】
- **Font and signature support** – The constructor hooks into `upload_dir`/`upload_mimes` so custom fonts are stored under `/fonts`, and the signature helpers persist base64 canvases under a locked-down directory.【F:src/Pomatio_Framework_Disk.php†L10-L119】

### `Pomatio_Framework_Settings`

- **Navigation helpers** – `get_current_tab()` and `get_current_subsection()` inspect the current request (or default to the first entries) so you can render context-aware navigation or run callbacks only on the active screen.【F:src/Pomatio_Framework_Settings.php†L10-L34】
- **Field metadata** – `read_fields()` loads the `fields.php` definition for a given setting, `get_effective_enabled_settings()` merges stored flags with auto-enabled tweaks, and `is_setting_enabled()` can use that data to determine whether a module is active.【F:src/Pomatio_Framework_Settings.php†L36-L205】
- **Value retrieval** – `get_setting_value()` reads the saved PHP file and optionally re-sanitizes the value using the field type, which is perfect for use in templates or business logic.【F:src/Pomatio_Framework_Settings.php†L46-L59】

Leverage these helpers together with the automatic save routine to keep your own code focused on business logic rather than boilerplate persistence.【F:src/Pomatio_Framework_Save.php†L10-L122】

## Settings pages in depth

Pomatio Framework can dynamically generate settings pages for your plugin or theme. You describe the tabs and fields in a PHP array, register a WordPress admin page, and call the framework helpers to render the UI and persist values for you.【F:src/Pomatio_Framework_Settings.php†L67-L371】【F:src/Pomatio_Framework_Save.php†L10-L122】

![Placeholder – Settings page tabs](docs/images/settings-page-tabs-placeholder.png)
<!-- TODO: Replace with a screenshot highlighting the navigation tabs rendered by the framework. -->

### Registering the admin page

Create your admin page using `add_submenu_page()` (or any other WordPress admin menu function). Save the return value in `$hook_suffix` and register it with `PomatioFramework\Pomatio_Framework::register_settings_page()` so the framework knows when to enqueue scripts and styles.【F:src/Pomatio_Framework.php†L33-L45】

```php
use PomatioFramework\Pomatio_Framework;
use PomatioFramework\Pomatio_Framework_Settings;

add_action('admin_menu', function () {
    $hook_suffix = add_submenu_page(
        'options-general.php',
        __('Your Plugin', 'your-plugin'),
        __('Your Plugin', 'your-plugin'),
        'manage_options',
        'your-plugin',
        'your_plugin_settings_page'
    );

    Pomatio_Framework::register_settings_page($hook_suffix);
});
```

### Rendering the settings form

Inside the page callback, require the file that returns the settings array and pass it to `Pomatio_Framework_Settings::render()`. The first parameter is the framework slug (usually your plugin slug), and the second is the settings definition array.【F:src/Pomatio_Framework_Settings.php†L67-L82】

```php
function your_plugin_settings_page() {
    $settings_path = require __DIR__ . '/settings.php';
    Pomatio_Framework_Settings::render('your-plugin', $settings_path);
}
```

### Overriding tab content

You can replace the markup of specific tabs while still relying on the framework to render the rest. Fetch the current tab with `Pomatio_Framework_Settings::get_current_tab()` and conditionally output your own HTML.【F:src/Pomatio_Framework_Settings.php†L10-L34】 For example, this is how you might render a custom API key form:

```php
function your_plugin_settings_page() {
    $settings_path = require __DIR__ . '/settings.php';
    $current_tab = Pomatio_Framework_Settings::get_current_tab($settings_path);

    if ($current_tab === 'your_plugin_api_keys') {
        // Your custom markup and form handling here.
    } else {
        Pomatio_Framework_Settings::render('your-plugin', $settings_path);
    }
}
```

### Rendering the tab navigation

To show the framework tabs above the screen title, hook into `in_admin_header` and call `Pomatio_Framework_Settings::render_tabs()` when the current screen ID matches your settings page.【F:src/Pomatio_Framework_Settings.php†L84-L209】

```php
add_action('in_admin_header', function () {
    $screen = get_current_screen();

    if (substr($screen->id, -strlen('your-plugin')) === 'your-plugin') {
        $settings_path = require __DIR__ . '/settings.php';
        Pomatio_Framework_Settings::render_tabs('your-plugin', $settings_path);
    }
});
```

### Handling callbacks after saving

Each settings definition can include a custom `callback` key alongside the usual `title`, `description`, and `settings_dir`. After the user saves, Pomatio Framework fires the `pomatio_framework_after_save_settings` action with the page slug, current tab, and subsection so you can inspect the active configuration and execute those callbacks.【F:src/Pomatio_Framework_Save.php†L78-L96】 Use `Pomatio_Framework_Helper::get_settings()` to fetch the current settings array and run any callable you stored under the `callback` key.【F:src/Pomatio_Framework_Helper.php†L130-L141】

```php
add_action('pomatio_framework_after_save_settings', function ($page_slug, $tab, $subsection) {
    $settings = require YOUR_PLUGIN_PATH . 'admin/settings.php';
    $groups = PomatioFramework\Pomatio_Framework_Helper::get_settings($settings, $tab, $subsection);

    foreach ($groups as $setting_key => $group) {
        if (!empty($group['callback']) && is_callable($group['callback'])) {
            call_user_func($group['callback'], $page_slug, $setting_key);
        }
    }
});
```

This pattern lets you invalidate caches, rebuild derived data, or synchronize external services whenever an admin updates a specific section of your settings page.

### Loading tweak definitions

If you use tweak folders or other modular features, call `Pomatio_Framework_Settings::get_effective_enabled_settings()` inside your admin class constructor. The helper loads `enabled_settings.php`, marks any `requires_initialization => false` tweaks as active, and persists the merged result so bootstrapping code sees the default modules immediately.【F:src/Pomatio_Framework_Settings.php†L66-L205】

```php
use PomatioFramework\Pomatio_Framework_Settings;

$settings_definition = require plugin_dir_path(__FILE__) . 'settings.php';
$enabled_settings = Pomatio_Framework_Settings::get_effective_enabled_settings('your-plugin', $settings_definition);

foreach ($enabled_settings as $module => $status) {
    if ($status === '1') {
        $module_path = plugin_dir_path(__FILE__) . "settings/{$module}/";

        if (file_exists("{$module_path}bootstrap.php")) {
            include "{$module_path}bootstrap.php";
        }
    }
}
```

### Retrieving saved values

When a settings form is submitted the save handler loops over the payload, infers the field type from the settings definition, and calls the corresponding `sanitize_pom_form_{type}` helper before the data touches disk.【F:src/Pomatio_Framework_Save.php†L32-L80】 That means a URL field always runs through `sanitize_pom_form_url()`, a number field strips non-numeric characters with `sanitize_pom_form_number()`, and a checkbox collapses to either `yes` or `no` regardless of what the browser sends.【F:src/class-sanitize.php†L37-L229】【F:src/class-sanitize.php†L343-L397】

Consider this fragment from a `fields.php` file:

```php
'settings' => [
    'webhook-url' => [
        'type' => 'url',
    ],
    'retry-count' => [
        'type' => 'number',
    ],
    'send-digest' => [
        'type' => 'checkbox',
    ],
],
```

If the admin submits `https://example.com/hooks  ` for the URL, `3 retries` for the number, and anything other than `yes` for the checkbox, the stored PHP array becomes:

```php
[
    'webhook-url' => 'https://example.com/hooks',
    'retry-count' => '3',
    'send-digest' => 'no',
]
```

Anywhere in your plugin you can access the saved settings via `Pomatio_Framework_Settings::get_setting_value()`.【F:src/Pomatio_Framework_Settings.php†L46-L59】 Pass the same type that the field used (`url`, `number`, `checkbox`, etc.) to re-sanitise the stored value at read time.

```php
$webhook = Pomatio_Framework_Settings::get_setting_value('your-plugin', 'general', 'webhook-url', 'url');
```

By mirroring the input type you ensure the runtime value has been filtered by the same logic that protects the form submission, guarding against manual edits or tampering in `wp-content/settings/`.

Use these helpers to build fully-featured admin experiences backed by Pomatio Framework.

## Additional resources

- Browse the `src/Fields` directory for renderer implementations you can extend.
- Inspect `class-sanitize.php` to understand how custom sanitizers should be structured before registering them with the framework.
- Review the JavaScript assets enqueued from `assets/js` to customise behaviours such as repeater drag-and-drop or Select2 initialisation.

![Placeholder – Additional resources illustration](docs/images/additional-resources-placeholder.png)
<!-- TODO: Replace with a graphic pointing readers to code, sanitizers, and assets. -->

