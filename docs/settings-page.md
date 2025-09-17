# Settings page

Pomatio Framework can dynamically generate settings pages for your plugin or theme. You describe the tabs and fields in a PHP array, register a WordPress admin page, and call the framework helpers to render the UI and persist values for you.【F:src/Pomatio_Framework_Settings.php†L67-L371】【F:src/Pomatio_Framework_Save.php†L10-L122】

## Registering the admin page

Create your admin page using `add_submenu_page()` (or any other WordPress admin menu function). Save the return value in `$hook_suffix` and register it with `PomatioFramework\Pomatio_Framework::register_settings_page()` so the framework knows when to enqueue scripts and styles.【F:src/Pomatio_Framework.php†L33-L45】

```php
use PomatioFramework\Pomatio_Framework;
use PomatioFramework\Pomatio_Framework_Settings;

add_action('admin_menu', function () {
    $hook_suffix = add_submenu_page(
        'options-general.php',
        __('Dummy', 'dummy-slug'),
        __('Dummy', 'dummy-slug'),
        'manage_options',
        'dummy-slug',
        'dummy_plugin_settings_page'
    );

    Pomatio_Framework::register_settings_page($hook_suffix);
});
```

## Rendering the settings form

Inside the page callback, require the file that returns the settings array and pass it to `Pomatio_Framework_Settings::render()`. The first parameter is the framework slug (usually your plugin slug), and the second is the settings definition array.【F:src/Pomatio_Framework_Settings.php†L67-L82】

```php
function dummy_plugin_settings_page() {
    $settings_path = require __DIR__ . '/settings.php';
    Pomatio_Framework_Settings::render('dummy-slug', $settings_path);
}
```

## Overriding tab content

You can replace the markup of specific tabs while still relying on the framework to render the rest. Fetch the current tab with `Pomatio_Framework_Settings::get_current_tab()` and conditionally output your own HTML.【F:src/Pomatio_Framework_Settings.php†L10-L34】 For example, this is how we render a custom API key form:

```php
function dummy_plugin_settings_page() {
    $settings_path = require __DIR__ . '/settings.php';
    $current_tab = Pomatio_Framework_Settings::get_current_tab($settings_path);

    if ($current_tab === 'dummy_api_keys') {
        // Your custom markup and form handling here.
    } else {
        Pomatio_Framework_Settings::render('dummy-slug', $settings_path);
    }
}
```

## Rendering the tab navigation

To show the framework tabs above the screen title, hook into `in_admin_header` and call `Pomatio_Framework_Settings::render_tabs()` when the current screen ID matches your settings page.【F:src/Pomatio_Framework_Settings.php†L84-L209】

```php
add_action('in_admin_header', function () {
    $screen = get_current_screen();

    if (substr($screen->id, -strlen('dummy-slug')) === 'dummy-slug') {
        $settings_path = require __DIR__ . '/settings.php';
        Pomatio_Framework_Settings::render_tabs('dummy-slug', $settings_path);
    }
});
```

## Handling callbacks after saving

Each settings definition can include a custom `callback` key alongside the usual `title`, `description`, and `settings_dir`. After the user saves, Pomatio Framework fires the `pomatio_framework_after_save_settings` action with the page slug, current tab, and subsection so you can inspect the active configuration and execute those callbacks.【F:src/Pomatio_Framework_Save.php†L78-L96】 Use `Pomatio_Framework_Helper::get_settings()` to fetch the current settings array and run any callable you stored under the `callback` key.【F:src/Pomatio_Framework_Helper.php†L130-L141】

```php
add_action('pomatio_framework_after_save_settings', function ($page_slug, $tab, $subsection) {
    $settings = require POM_AI_PLUGIN_PATH . 'admin/settings.php';
    $groups = PomatioFramework\Pomatio_Framework_Helper::get_settings($settings, $tab, $subsection);

    foreach ($groups as $setting_key => $group) {
        if (!empty($group['callback']) && is_callable($group['callback'])) {
            call_user_func($group['callback'], $page_slug, $setting_key);
        }
    }
});
```

This pattern lets you invalidate caches, rebuild derived data, or synchronize external services whenever an admin updates a specific section of your settings page.

## Loading tweak definitions

If you use tweak folders or other modular features, call `Pomatio_Framework_Settings::get_effective_enabled_settings()` inside your admin class constructor. The helper loads `enabled_settings.php`, marks any `requires_initialization => false` tweaks as active, and persists the merged result so bootstrapping code sees the default modules immediately.【F:src/Pomatio_Framework_Settings.php†L66-L205】

```php
use PomatioFramework\Pomatio_Framework_Settings;

$settings_definition = require plugin_dir_path(__FILE__) . 'settings.php';
$enabled_settings = Pomatio_Framework_Settings::get_effective_enabled_settings('dummy-slug', $settings_definition);

foreach ($enabled_settings as $tweak => $status) {
    if ($status === '1') {
        $tweak_path = plugin_dir_path(__FILE__) . "settings/$tweak/";

        if (file_exists("{$tweak_path}tweak.php")) {
            include "{$tweak_path}tweak.php";
        }
    }
}
```

## Retrieving saved values

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
$webhook = Pomatio_Framework_Settings::get_setting_value('dummy-slug', 'general', 'webhook-url', 'url');
```

By mirroring the input type you ensure the runtime value has been filtered by the same logic that protects the form submission, guarding against manual edits or tampering in `wp-content/settings/`.

Use these helpers to build fully-featured admin experiences backed by Pomatio Framework.
