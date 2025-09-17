# Pomatio Framework

Pomatio Framework (pom-form) is a WordPress helper library that makes it easy to define admin settings pages and render complex field collections from simple PHP arrays.  This guide walks you through everything you need to start using the framework inside your plugin or theme, including a complete example that mirrors how we integrate it in production.

## Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher
- Composer (to install the package and its autoload file)

## Installation

Install the package in your plugin or theme directory with Composer:

```bash
composer require pom/form
```

If you ship the framework inside a distributed plugin, remember to include Composer’s autoload file before bootstrapping the framework:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

## Project structure example

A minimal plugin that uses Pomatio Framework to render settings might look like this:

```
wp-content/
└── plugins/
    └── pom-ai/
        ├── admin/
        │   ├── class-admin.php
        │   └── settings.php
        ├── assets/
        │   ├── css/
        │   │   └── ai-dashboard.css
        │   └── js/
        │       └── ai-dashboard.js
        ├── settings/
        │   └── enabled_settings.php
        ├── vendor/
        │   └── autoload.php
        └── pom-ai.php (plugin bootstrap file)
```

The framework stores run‑time configuration under `wp-content/settings/pomatio-framework/sites/<site-id>/<slug>/`.  In the example above the slug is `pom-ai`, so the file `enabled_settings.php` will be read from `wp-content/settings/pomatio-framework/sites/<site-id>/pom-ai/enabled_settings.php`.

## Step-by-step tutorial

The following walk-through reproduces the behaviour of our internal `POM_AI_Admin_Settings` class.  You can copy the snippets into your own plugin and adjust namespaces, slugs and assets as needed.

### 1. Bootstrap your admin class

Create `admin/class-admin.php` and instantiate it from your plugin’s main file:

```php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/admin/class-admin.php';

new POM_AI_Admin_Settings();
```

### 2. Register hooks and load enabled tweaks

Inside `POM_AI_Admin_Settings` add the hooks you need for WordPress and Pomatio Framework.  The snippet below shows how to register scripts, AJAX handlers and dashboard UI pieces.  The important part is calling `PomatioFramework\Pomatio_Framework_Disk::read_file()` to find which tweaks are active and load their `tweak.php` files dynamically.

```php
use PomatioFramework\Pomatio_Framework_Disk;
use PomatioFramework\Pomatio_Framework_Settings;

class POM_AI_Admin_Settings {
    public function __construct() {
        add_action('admin_footer', [$this, 'dashboard_welcome_banner']);
        add_action('wp_ajax_pom_ai_get_credits', [$this, 'get_credits']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_action('admin_menu', [$this, 'options_menu']);
        add_action('in_admin_header', [$this, 'pom_ai_render_tabs']);

        $settings_definition = require POM_AI_PLUGIN_PATH . 'admin/settings.php';
        $enabled_settings = Pomatio_Framework_Settings::get_effective_enabled_settings('pom-ai', $settings_definition);

        foreach ($enabled_settings as $tweak => $status) {
            if ($status === '1') {
                $tweak_path = POM_AI_PLUGIN_PATH . "settings/$tweak/";

                if (file_exists("{$tweak_path}tweak.php")) {
                    include "{$tweak_path}tweak.php";
                }
            }
        }
    }

    // ... other methods (enqueue_scripts, get_credits, etc.) ...
}
```

`Pomatio_Framework_Settings::get_effective_enabled_settings()` resolves the correct storage directory, merges the stored flags with any tweaks marked `requires_initialization => false`, and returns the up-to-date `enabled_settings.php` array so you can bootstrap modules without touching PHP code.

### 3. Register the settings page with WordPress **and** the framework

Use `add_submenu_page()` to create a submenu under “Settings → AI settings”.  Save the return value in `$hook_suffix`, then register that hook with the framework.  This ensures Pomatio Framework can enqueue its assets only when the page is active.

```php
public function options_menu(): void {
    $hook_suffix = add_submenu_page(
        'options-general.php',
        __('AI settings', POM_AI_PLUGIN_SLUG),
        __('AI settings', POM_AI_PLUGIN_SLUG),
        'read',
        'pom-ai',
        [$this, 'pom_ai_settings_page']
    );

    if (class_exists('PomatioFramework\\Pomatio_Framework')) {
        PomatioFramework\Pomatio_Framework::register_settings_page($hook_suffix);
    }
}
```

### 4. Describe the tabs and fields in `admin/settings.php`

The settings definition file returns an associative array grouped by tab.  You can hide entire groups unless a prerequisite tweak is enabled by checking the `enabled_settings.php` array.

```php
use PomatioFramework\Pomatio_Framework_Settings;
use PomatioFramework\Pomatio_Framework_Disk;

$pom_ai_settings['config'] = [
    'settings_dir' => POM_AI_PLUGIN_PATH . 'settings'
];

$pom_ai_settings['ai_base_config'] = [
    'title' => __('AI Setup', POM_AI_PLUGIN_SLUG),
    'allowed_roles' => ['administrator', 'editor'],
    'tab' => [
        'ai_base_config' => [
            'title' => __('Basic AI setup', POM_AI_PLUGIN_SLUG),
            'description' => __('Basic configuration for AI tools.', POM_AI_PLUGIN_SLUG),
            'settings' => [
                'ai-base-setup' => [
                    'title' => __('Enable AI tools', POM_AI_PLUGIN_SLUG),
                    'description' => __('Check this to enable AI features...', POM_AI_PLUGIN_SLUG),
                    'heading_checkbox' => __('Globally enable AI features', POM_AI_PLUGIN_SLUG),
                    'label_checkbox' => __('Check this to enable AI features.', POM_AI_PLUGIN_SLUG),
                    'requires_initialization' => true
                ],
            ]
        ],
    ]
];

$enabled_settings = Pomatio_Framework_Settings::get_effective_enabled_settings('pom-ai', $pom_ai_settings);

if (!empty($enabled_settings['ai-base-setup'])) {
    $pom_ai_settings['pom_ai_api_keys'] = [
        'title' => __('API keys', POM_AI_PLUGIN_SLUG),
        'allowed_roles' => ['administrator'],
        'tab' => [
            'pom_ai_api_keys' => [
                'title' => __('POM AI API keys', POM_AI_PLUGIN_SLUG),
                'description' => __('Here you can setup your API keys...', POM_AI_PLUGIN_SLUG),
                'settings' => []
            ],
        ]
    ];
}

return apply_filters('pom_ai_settings', $pom_ai_settings);
```

Each field definition supports multiple parameters (`title`, `description`, `img`, checkboxes, etc.) that are described in detail in [`docs/index.md`](docs/index.md).

### 5. Override the content of specific tabs

By default, `Pomatio_Framework_Settings::render()` draws every tab and field using the definition array.  If you want to replace the markup of a tab (for example the API keys screen), detect the active tab and output your own HTML before calling the renderer for the rest.

```php
public function pom_ai_settings_page(): void {
    $settings_path = require POM_AI_PLUGIN_PATH . 'admin/settings.php';
    $current_tab = Pomatio_Framework_Settings::get_current_tab($settings_path);

    if ($current_tab === 'pom_ai_api_keys') {
        // Custom markup, form handling and persistence
        $pom_ai_keys = get_option('pom_ai_api_keys', '');
        $keys = explode(':', $pom_ai_keys);
        $public_key = isset($keys[0]) ? esc_attr($keys[0]) : '';
        $private_key = isset($keys[1]) ? esc_attr($keys[1]) : '';

        ?>
        <div id="pom-ai-panel">
            <p><?php _e('Your site AI credits', POM_AI_PLUGIN_SLUG); ?>:
                <span id="pom-ai-available-credits" class="spinner"><?php _e('Loading your credits...', POM_AI_PLUGIN_SLUG); ?></span>
            </p>
            <hr>
            <h2><?php _e('POM AI API Keys', POM_AI_PLUGIN_SLUG); ?></h2>
            <form method="post" action="">
                <label for="pom_ai_public_key"><?php _e('Public Key', POM_AI_PLUGIN_SLUG); ?></label>
                <input type="text" name="pom_ai_public_key" id="pom_ai_public_key" value="<?php echo $public_key; ?>" required>
                <label for="pom_ai_private_key"><?php _e('Private Key', POM_AI_PLUGIN_SLUG); ?></label>
                <input type="text" name="pom_ai_private_key" id="pom_ai_private_key" value="<?php echo $private_key; ?>" required>
                <input type="submit" name="pom_ai_save_keys" class="button button-primary" value="<?php _e('Save API Keys', POM_AI_PLUGIN_SLUG); ?>">
            </form>
        </div>
        <?php

        if (isset($_POST['pom_ai_save_keys'])) {
            $combined_keys = sanitize_text_field($_POST['pom_ai_public_key']) . ':' . sanitize_text_field($_POST['pom_ai_private_key']);
            update_option('pom_ai_api_keys', $combined_keys);
            echo "<script>location.replace('" . admin_url('options-general.php?page=pom-ai&section=pom_ai_api_keys') . "');</script>";
            exit;
        }
    } else {
        Pomatio_Framework_Settings::render('pom-ai', $settings_path);
    }
}
```

### 6. Render tabs in the admin header

To show the framework tab navigation above your page content, hook into `in_admin_header` and call `Pomatio_Framework_Settings::render_tabs()` when the screen ID matches your page slug.

```php
public function pom_ai_render_tabs(): void {
    $screen = get_current_screen();

    if (substr($screen->id, -strlen('pom-ai')) === 'pom-ai') {
        $settings_path = require POM_AI_PLUGIN_PATH . 'admin/settings.php';
        Pomatio_Framework_Settings::render_tabs('pom-ai', $settings_path);
    }
}
```

### How input sanitization works

Every time the settings form is submitted the framework looks up the field definitions, detects the field type, and calls the matching `sanitize_pom_form_{type}` helper before writing the value to disk.【F:src/Pomatio_Framework_Save.php†L32-L80】 This guarantees that each control is cleaned according to its semantics – URLs are normalised, numbers lose stray characters, and checkboxes collapse to `yes`/`no` flags.【F:src/class-sanitize.php†L37-L229】【F:src/class-sanitize.php†L343-L397】

For example, imagine the “Requests” tab contains these fields:

```php
'settings' => [
    'company-url' => [
        'type' => 'url',
    ],
    'request-limit' => [
        'type' => 'number',
    ],
    'notify-team' => [
        'type' => 'checkbox',
    ],
],
```

If the administrator submits the form with:

```php
$_POST = [
    'requests_company-url' => ' https://example.com/docs ',
    'requests_request-limit' => '25 tickets',
    'requests_notify-team' => 'maybe',
];
```

`Pomatio_Framework_Save::save_settings()` strips the prefix, determines the input types, and persists the following array:

```php
[
    'company-url'   => 'https://example.com/docs', // sanitize_pom_form_url()
    'request-limit' => '25',                       // sanitize_pom_form_number()
    'notify-team'   => 'no',                       // sanitize_pom_form_checkbox()
]
```

When you read the value later you can (and should) pass the same field type to `Pomatio_Framework_Settings::get_setting_value()` so the raw file contents are re-sanitized before you use them.【F:src/Pomatio_Framework_Settings.php†L46-L58】

```php
$limit = Pomatio_Framework_Settings::get_setting_value('pom-ai', 'requests', 'request-limit', 'number');
```

Because the helper reuses the same sanitizers, the value you receive matches what the framework would save if the admin resubmitted the form, which prevents stale or tampered data from leaking into your business logic.【F:src/class-sanitize.php†L197-L229】【F:src/class-sanitize.php†L343-L397】

### 7. Read saved values anywhere in your code

Once the form is rendered, you can retrieve values using `Pomatio_Framework_Settings::get_setting_value()`.  Pass the framework slug, the tab identifier, the field key and the field type.

```php
$copyright = Pomatio_Framework_Settings::get_setting_value(
    'pom-ai',
    'ai_base_config',
    'ai-base-setup',
    'checkbox'
);

if (!empty($copyright)) {
    // Do something with the saved value.
}
```

### 8. Optional: enqueue assets and AJAX endpoints

You are free to add the scripts, styles and AJAX callbacks your plugin needs.  The example class above enqueues a dashboard panel, fetches API credits via `wp_ajax_pom_ai_get_credits`, and displays a welcome banner on the WordPress dashboard.  Those helpers are standard WordPress code and live alongside the framework integration.

## Next steps

- Browse the [`docs/`](docs/index.md) folder for field definitions and advanced configuration examples.
- Inspect the `settings/` directory in your plugin to see how tweaks are structured.
- Combine the settings forms with REST endpoints or background jobs to build rich AI-assisted features.

With these building blocks you can replicate the full POM AI administration experience or adapt it to your own project.
