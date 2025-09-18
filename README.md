# Pomatio Framework

Pomatio Framework lets you describe WordPress admin interfaces with plain PHP arrays and have the library render, validate, and persist every field for you. It bootstraps helper, disk, settings, AJAX, save, and translation services as soon as you instantiate the main class, so assets only load on registered screens and sanitizers always match your field definitions.

Key advantages at a glance:

- **Purpose-built admin UI builder** – Declare tabs, subsections, and fields, then call a single render method to output fully localised markup with nonce handling, repeaters, and dependency metadata baked in.
- **Deterministic configuration** – Settings live alongside your code in PHP files, yet any field can opt into WordPress options or theme mods when runtime overrides are required.
- **Performance-first storage** – OPcache caches the generated settings arrays, reducing database lookups and avoiding autoload bloat while still supporting multisite-aware directories.
- **Extensive field library** – Everything from text inputs to CodeMirror editors, Select2-powered dropdowns, signature pads, font managers, and nested repeaters ships out of the box.
- **Customisable tool cards** – Add wrapper elements, hero images, and onboarding copy to make complex toggle panels look polished without rewriting templates.

![Placeholder – default settings card](screenshots/default-settings-card-placeholder.png)
![Placeholder – custom div wrapper with image](screenshots/custom-wrapper-card-placeholder.png)

> Replace the placeholder screenshots above with real captures from your project once they are available.

## Table of contents

- [Quick start](#quick-start)
- [Benefits of PHP settings files](#benefits-of-php-settings-files)
  - [Performance profile](#performance-profile)
  - [Operational advantages](#operational-advantages)
  - [When to use the database instead](#when-to-use-the-database-instead)
  - [Common objections](#common-objections)
- [Requirements](#requirements)
- [Installation](#installation)
- [Example project structure](#example-project-structure)
  - [Runtime storage locations](#runtime-storage-locations)
- [Step-by-step integration tutorial](#step-by-step-integration-tutorial)
  - [1. Bootstrap your admin class](#1-bootstrap-your-admin-class)
  - [2. Register hooks and load enabled modules](#2-register-hooks-and-load-enabled-modules)
  - [3. Register the settings page with WordPress and the framework](#3-register-the-settings-page-with-wordpress-and-the-framework)
  - [4. Describe tabs and fields in a settings definition file](#4-describe-tabs-and-fields-in-a-settings-definition-file)
  - [Cooler tool cards with wrappers and images](#cooler-tool-cards-with-wrappers-and-images)
- [Field reference](#field-reference)
  - [Defining fields](#defining-fields)
  - [Common parameters](#common-parameters)
  - [External storage with `save_as`](#external-storage-with-save_as)
  - [Dependencies and conditional logic](#dependencies-and-conditional-logic)
  - [Working with repeaters](#working-with-repeaters)
  - [Field library](#field-library)
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

## Quick start

1. **Register your admin page** and keep the hook suffix so framework assets only load where needed.
2. **Return a settings array** from a PHP file describing tabs, subsections, and field definitions.
3. **Render the form** by calling `Pomatio_Framework_Settings::render( $slug, $settings_array )` inside the admin page callback.
4. **React after saving** with the `pomatio_framework_after_save_settings` action.

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

## Benefits of PHP settings files

### Performance profile

- **Zero database round-trips on cold start** – `require_once` loads a compiled PHP array, whereas `get_option()` must query the database unless caches are already warm.
- **OPcache acceleration** – Once deployed, PHP bytecode lives in memory; subsequent requests simply reference the cached array.
- **Smaller autoload payload** – Large option arrays no longer inflate `wp_options` autoload data, improving TTFB across the board.
- **Predictable caching** – Files benefit from both the OS page cache and OPcache, avoiding sudden cache evictions that affect options.

### Operational advantages

- **True version control** – Configuration travels with Git history, enabling diffs, reviews, and instant rollbacks.
- **Reproducible deployments** – Every environment receives the same configuration as part of the release artifact.
- **Immutable production friendly** – Read-only filesystems and GitOps workflows work seamlessly when configuration is code.
- **Decoupled from theme lifecycle** – Unlike `theme_mods`, PHP settings files survive theme switches.
- **Database hygiene** – Keep `wp_options` focused on stateful content while code governs behaviour.

### When to use the database instead

- Per-user preferences or rapidly changing state.
- Translatable editorial content maintained by non-technical users.
- Ephemeral values such as counters, temporary tokens, or session data.

### Common objections

- **“Marketing needs to change colours via UI.”** Pomatio Framework produces the admin UI that writes straight to the file, so non-developers still use familiar forms.
- **“Where do API secrets live?”** Store sensitive values as WordPress options or theme mods using the `save_as` flag so they stay out of version control.

## Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher
- Composer (for installing the package and autoload file)

## Installation

Install the package in your plugin or theme directory with Composer:

```bash
composer require pom/form
```

If you bundle the framework in a distributed plugin or theme, include Composer’s autoload file before bootstrapping the framework:

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
        │   │   ├── fields.php -> Settings rendered on the admin page for that module.
        │   │   └── bootstrap.php -> Code executed when the module is enabled.
        │   └── other-module-subdirectory
        ├── vendor/
        │   └── autoload.php
        └── your-plugin.php (plugin bootstrap file)
```

### Runtime storage locations

At runtime the framework stores configuration files under `wp-content/settings/pomatio-framework/sites/<site-id>/<slug>/`. In the structure above, the slug is `your-plugin`, so the file `enabled_settings.php` lives at `wp-content/settings/pomatio-framework/sites/<site-id>/your-plugin/enabled_settings.php`.

![Placeholder – storage diagram](screenshots/storage-diagram-placeholder.png)

## Step-by-step integration tutorial

The walkthrough below mirrors how a typical admin class wires Pomatio Framework into a plugin. Copy the snippets into your own project and adjust namespaces, slugs, and asset paths as needed.

### 1. Bootstrap your admin class

Create `admin/class-admin.php` and instantiate it from your plugin’s main file:

```php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/admin/class-admin.php';
new Your_Plugin_Admin_Settings();
```

### 2. Register hooks and load enabled modules

Inside `Your_Plugin_Admin_Settings` add the WordPress and framework hooks you need. The snippet below registers scripts, AJAX handlers, and dashboard UI pieces. The key call is `Pomatio_Framework_Settings::get_effective_enabled_settings()`, which reads stored module flags and loads their bootstrap files dynamically.

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

### Cooler tool cards with wrappers and images

Settings arrays support presentation hints to help you design eye-catching layouts. Set `'wrapper' => 'div'` to render a `<div class="pomatio-framework-setting">` container and provide an `'img'` URL for a hero graphic. Combined with the checkbox labels, you can build multi-step toggles that read like product cards.

```php
$pom_ai_settings['ai_tools']['tab']['ai_tools']['settings']['ai-write-long-posts'] = [
    'title' => __('Write ultra-long posts (2-step)', POM_AI_PLUGIN_SLUG),
    'description' => __('Create thousands-word articles with a plan-first workflow: Step 1 generates a detailed outline, Step 2 writes the full post optimized for SEO and LLM retrieval (clear structure, citations, entities, and schema hints). Ideal for cornerstone content and topic clusters.', POM_AI_PLUGIN_SLUG),
    'heading_checkbox' => __('AI long-form post builder', POM_AI_PLUGIN_SLUG),
    'label_checkbox' => __('Enable 2-step structure → content', POM_AI_PLUGIN_SLUG),
    'description_checkbox' => __('First build the outline, then generate thousands of words optimized for SEO & LLMs.', POM_AI_PLUGIN_SLUG),
    'wrapper' => 'div',
    'img' => POM_AI_PLUGIN_URL . '/assets/img/tool-write-long-posts.jpg',
    'requires_initialization' => true,
];
```

Use the two screenshots at the top of this README to show both the default table layout and the enhanced wrapper layout once your captures are ready.

## Field reference

This section consolidates the complete field documentation so you can configure interfaces without switching files.

### Defining fields

Every field is declared as a PHP array and rendered by `Pomatio_Framework::add_field()`, which normalises arguments and locates the matching renderer under `src/Fields`. The framework enqueues scripts such as CodeMirror, Select2, colour pickers, and repeater helpers on demand based on the field types present in your definition.

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
| `label`, `description` | Human-friendly text rendered above or below the field; use `description_position` to switch between `below_label` and `under_field`. |
| `default`, `value` | Renderers prefer a saved `value`, then fall back to `default`, so you can pre-populate fields while letting users overwrite them. |
| `class`, `id` | Custom classes/IDs appended to the generated markup after being sanitised, handy for styling or JavaScript hooks. |
| `disabled` | Pass `true` to render a disabled input without losing the value on save—useful for templates or repeater defaults. |
| `dependency` | Define conditional logic that is serialised into a `data-dependencies` attribute so JavaScript can hide or show the field based on other values. |
| `save_as` | Optional string that re-routes persistence to a theme mod or WordPress option. See below for the supported values. |

Because each field posts back under its `name`, the save handler can detect the field type, run the corresponding sanitizer from `class-sanitize.php`, and persist a clean value to disk.

### External storage with `save_as`

Setting `save_as` on a field instructs the saver to sanitise the payload and forward it to either the theme-mod API or the options table instead of appending it to the generated `<setting>.php` file. The saver records the routing in `fields_save_as.php` so the renderer, sanitizers, and translation helpers can look up the correct source later.

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

When the administrator saves the page the framework writes the alternative targets, updates `fields_save_as.php`, and invalidates OPcache so subsequent requests pick up the change immediately. `Pomatio_Framework_Settings::get_setting_value()` consults that metadata before it sanitises the value, meaning your front-end code can continue to call the helper without caring whether the value lives in a PHP array, a theme mod, or an option.

### Dependencies and conditional logic

Any field can react to other inputs by providing a `dependency` array. `Pomatio_Framework_Helper::get_dependencies_data_attr()` JSON-encodes the structure and stores it as a data attribute without forcing you to write boilerplate, so repeaters, selects, and custom inputs can all participate in complex flows.

### Working with repeaters

The repeater field is a container that renders a list of child fields, supports defaults, cloning, sorting, nested repeaters, and per-item dependencies. It stores its configuration alongside the saved values in a hidden input (`data-type="repeater"`), and during sanitisation each nested field is processed with its own sanitizer so code editors are written to disk while plain inputs remain inline.

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
                        'default' => 'none'
                    ],
                    [
                        'type'       => 'select',
                        'name'       => 'roles',
                        'multiple'   => true,
                        'label'      => __('Roles', 'your-plugin'),
                        'options'    => pom_get_roles(true),
                        'dependency' => [[[ 'field' => 'rule_type', 'values' => ['role_is', 'role_is_not'] ]]],
                    ],
                    // … additional dependent selects and number fields …
                ],
            ],
            [
                'type'    => 'select',
                'name'    => 'condition_between_rules',
                'label'   => __('Rule type', 'your-plugin'),
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

When this structure is posted, `sanitize_pom_form_repeater()` walks each layer, enforces the limit, and delegates to the appropriate sanitizers (`sanitize_pom_form_select`, `sanitize_pom_form_number`, etc.) so the saved JSON mirrors the UI without exposing unsanitised input.

### Field library

The framework ships the following field types. Use this table to pick the right control and learn about its quirks.

| Field type | What it renders | Notable options & behaviour |
|------------|-----------------|-----------------------------|
| `background_image` | Composite background designer combining image picker, alignment radios, size selectors, repeat controls, and colour pickers. | Stores JSON describing every background property. Sanitized via `sanitize_pom_form_background_image()` and enqueues its own scripts/styles. |
| `button` | `<input type="button">`, submit button, or `<a>` element. | Use `submit => true` for submit buttons, `link => true` with `href` for anchor tags, and `text` for the label. Optional `description` renders helper text. |
| `checkbox` | Single yes/no toggle or multiple checkboxes. | Hidden input posts `no` when unchecked. Accepts `options` array for multi-select mode and honours dependencies and disabled state. |
| `code_css` | CodeMirror-powered CSS editor. | Loads existing file contents when `value` is a path, supports `custom_attrs` like `placeholder`, and saves through the CSS sanitizer with optional compression. |
| `code_html` | CodeMirror HTML editor. | Retrieves file contents when given a path, enqueues CodeMirror assets, and sanitises output with WordPress KSES-compatible rules. |
| `code_js` | CodeMirror JavaScript editor. | Enqueues JS mode, writes files to disk when necessary, and sanitises script input to prevent malformed payloads. |
| `code_json` | CodeMirror JSON editor. | Validates JSON structure during sanitisation and is ideal for structured configuration blobs. |
| `color` | WordPress colour picker input. | Outputs an `<input type="text">` with the WP colour picker attached and sanitises to a valid hex value. |
| `color_palette` | Palette selector using colour chips. | Provide `options` with hex codes and labels; sanitisation ensures selections are restricted to allowed values. |
| `date` | `<input type="date">` field. | Accepts `min`, `max`, and default values; sanitiser enforces the `Y-m-d` format. |
| `datetime` | `<input type="datetime-local">` field. | Supports `min`, `max`, and step attributes. Sanitised to `Y-m-d H:i`. |
| `email` | `<input type="email">`. | Validates addresses, honours placeholders and default values, and integrates with dependencies. |
| `file` | Native file upload control. | Exposes `pom_form_max_file_size` and `pom_form_allowed_mime_types` filters so you can restrict uploads; the default sanitiser returns the raw payload for your own handling. |
| `font` | Composite font manager with nested repeaters for families, fallbacks, and variants. | Persists metadata describing each font variant, restricts uploads to allowed MIME types, and leverages the disk helper for storage. |
| `font_picker` | Upload field for individual font files inside the font manager. | Sanitises file arrays, stores assets under the custom font directory, and exposes URLs for front-end usage. |
| `gallery` | WordPress media modal gallery selector. | Returns comma-separated attachment IDs, allows reordering, and sanitises IDs to digits and commas. |
| `hidden` | Hidden metadata field. | Useful for storing configuration without UI; sanitiser strips unexpected content. |
| `icon_picker` | SVG and icon font selector. | Lists available icon libraries, supports search, and sanitises selected icon URLs. |
| `image_picker` | WordPress media modal image selector. | Stores attachment IDs or URLs, supports custom button text, and sanitises to valid URLs/IDs. |
| `number` | `<input type="number">`. | Provides placeholder support, respects dependencies, and sanitises the submission to numeric characters. |
| `password` | `<input type="password">`. | Displays saved values when provided, honours dependency metadata, and sanitises submissions using `sanitize_text_field()`. |
| `quantity` | Numeric input with plus/minus controls. | JavaScript increments/decrements values, while the sanitiser normalises numeric output. |
| `radio` | Radio button group. | Provide `options` as value/label pairs; sanitiser ensures only allowed values persist. |
| `radio_icons` | Radio group rendered with SVG icons. | Expects `options` with `label`, `icon`, and `value`. Includes a restore-default shortcut and sanitises icon selections. |
| `range` | Slider paired with a numeric input. | Supports `min`, `max`, `step`, optional `suffix`, and a “restore default” action. Enqueues dedicated JS/CSS assets. |
| `repeater` | Dynamic group of nested fields. | Configure `fields`, `min_items`, `max_items`, `title_field`, and `button_labels`. Supports drag-and-drop sorting and nested repeaters. |
| `select` | Select dropdown with optional multi-select and optgroups. | Accepts `options`, `multiple`, and `placeholder`, supports optgroup structures, enqueues the framework select script, and stores multi-select choices as comma-separated lists. |
| `separator` | Heading and description block. | Use to break long forms into sections; no value is stored. |
| `signature` | Canvas-based signature pad. | Saves base64 images through the disk helper, stores the generated file path, and leaves the value untouched during sanitisation because validation happens when writing the file. |
| `tel` | `<input type="tel">`. | Validates against an international phone pattern (optional leading `+` and 8–15 digits) and returns `false` when the input is invalid. |
| `text` | Single-line text input. | Supports `placeholder`, `used_for_title` (for repeater headings), and dependency metadata. Sanitises to plain text. |
| `textarea` | Multi-line textarea. | Accepts `rows`, `placeholder`, and dependency metadata. Sanitiser removes HTML entirely, keeping plain text for storage. |
| `time` | `<input type="time">`. | Supports step increments; sanitiser ensures valid time strings. |
| `tinymce` | WordPress TinyMCE editor. | Pass `custom_attrs` such as `textarea_rows`, `teeny`, `quicktags`, `wpautop`, or `media_buttons` to control the toolbar. Sanitiser cleans output using the configured allowed HTML. |
| `toggle` | Styled on/off switch built on a checkbox. | Provides automatic IDs, enqueues slider CSS, and sanitises to `yes`/`no`. |
| `url` | `<input type="url">`. | Sanitises to absolute URLs, trims whitespace, and cooperates with dependency logic. |

## Helper classes

Pomatio Framework exposes several helper classes so you can inspect configuration, generate dependency metadata, and manipulate files without digging into WordPress internals yourself.

### `Pomatio_Framework_Helper`

- **Dependency attributes** – `get_dependencies_data_attr()` converts the `dependency` structure you define in a field into the JSON payload that drives conditional display in JavaScript.
- **Utility conversions** – Use `convert_array_to_html_attributes()` and `convert_html_attributes_to_array()` to move between associative arrays and attribute strings when you extend the framework.
- **Runtime helpers** – Functions such as `generate_random_string()`, `write_log()`, and `path_to_url()` help you build dynamic field IDs, debug issues, and turn filesystem paths into public URLs.
- **Settings lookups** – `get_settings()` returns the raw settings array for a given tab/subsection and `get_allowed_html()` exposes the HTML tags that text editors sanitise against.

### `Pomatio_Framework_Disk`

- **Automatic directories** – `create_settings_dir()` provisions `wp-content/settings/pomatio-framework/<site>/<slug>/` (including `.htaccess`) the first time a page saves values, making the storage multisite-aware.
- **File serialization** – `generate_file_content()` turns an array into a PHP file with metadata headers, while `save_to_file()` writes arbitrary content such as code editor values and returns the saved path.
- **Reading and cleanup** – `read_file()` and `delete_file()` provide convenient access to stored configuration and let you remove generated assets when a module is disabled.
- **Font and signature support** – The constructor hooks into `upload_dir`/`upload_mimes` so custom fonts are stored under `/fonts`, and the signature helpers persist base64 canvases under a locked-down directory.

### `Pomatio_Framework_Settings`

- **Navigation helpers** – `get_current_tab()` and `get_current_subsection()` inspect the current request (or default to the first entries) so you can render context-aware navigation or run callbacks only on the active screen.
- **Field metadata** – `read_fields()` loads the `fields.php` definition for a given setting, `get_effective_enabled_settings()` merges stored flags with auto-enabled tweaks, and `is_setting_enabled()` checks whether a module is active.
- **Value retrieval** – `get_setting_value()` reads the saved PHP file and optionally re-sanitises the value using the field type, which is perfect for use in templates or business logic.

Leverage these helpers together with the automatic save routine to keep your own code focused on business logic rather than boilerplate persistence.

## Settings pages in depth

Pomatio Framework can dynamically generate settings pages for your plugin or theme. You describe the tabs and fields in a PHP array, register a WordPress admin page, and call the framework helpers to render the UI and persist values for you.

### Registering the admin page

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

```php
function your_plugin_settings_page() {
    $settings_path = require __DIR__ . '/settings.php';
    Pomatio_Framework_Settings::render('your-plugin', $settings_path);
}
```

### Overriding tab content

You can replace the markup of specific tabs while still relying on the framework to render the rest. Fetch the current tab with `Pomatio_Framework_Settings::get_current_tab()` and conditionally output your own HTML.

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

Hook into `in_admin_header` and call `Pomatio_Framework_Settings::render_tabs()` when the current screen ID matches your settings page to display the navigation tabs above the screen title.

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

Each settings definition can include a custom `callback` key alongside the usual `title`, `description`, and `settings_dir`. After the user saves, Pomatio Framework fires the `pomatio_framework_after_save_settings` action with the page slug, current tab, and subsection so you can inspect the active configuration and execute those callbacks.

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

This pattern lets you invalidate caches, rebuild derived data, or synchronise external services whenever an admin updates a specific section of your settings page.

### Loading tweak definitions

If you use tweak folders or other modular features, call `Pomatio_Framework_Settings::get_effective_enabled_settings()` inside your admin class constructor. The helper loads `enabled_settings.php`, marks any `requires_initialization => false` tweaks as active, and persists the merged result so bootstrapping code sees the default modules immediately.

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

When a settings form is submitted the save handler loops over the payload, infers the field type from the settings definition, and calls the corresponding `sanitize_pom_form_{type}` helper before the data touches disk. That means a URL field always runs through `sanitize_pom_form_url()`, a number field strips non-numeric characters with `sanitize_pom_form_number()`, and a checkbox collapses to either `yes` or `no` regardless of what the browser sends.

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

Anywhere in your plugin you can access the saved settings via `Pomatio_Framework_Settings::get_setting_value()`. Pass the same type that the field used (`url`, `number`, `checkbox`, etc.) to re-sanitise the stored value at read time.

```php
$webhook = Pomatio_Framework_Settings::get_setting_value('your-plugin', 'general', 'webhook-url', 'url');
```

By mirroring the input type you ensure the runtime value has been filtered by the same logic that protects the form submission, guarding against manual edits or tampering in `wp-content/settings/`.

## Additional resources

- Browse the `src/Fields` directory for renderer implementations you can extend.
- Inspect `class-sanitize.php` to understand how custom sanitizers should be structured before registering them with the framework.
- Review the JavaScript assets enqueued from `assets/js` to customise behaviours such as repeater drag-and-drop or Select2 initialisation.

![Placeholder – resources collage](screenshots/resources-placeholder.png)

