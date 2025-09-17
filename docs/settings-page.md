# Settings page

Pomatio Framework can dynamically generate settings pages for your plugin or theme.  You describe the tabs and fields in a PHP array, register a WordPress admin page, and call the framework helpers to render the UI.

## Registering the admin page

Create your admin page using `add_submenu_page()` (or any other WordPress admin menu function).  Save the return value in `$hook_suffix` and register it with `PomatioFramework\Pomatio_Framework::register_settings_page()` so the framework knows when to enqueue scripts and styles.

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

Inside the page callback, require the file that returns the settings array and pass it to `Pomatio_Framework_Settings::render()`.  The first parameter is the framework slug (usually your plugin slug), and the second is the settings definition array.

```php
function dummy_plugin_settings_page() {
    $settings_path = require __DIR__ . '/settings.php';
    Pomatio_Framework_Settings::render('dummy-slug', $settings_path);
}
```

## Overriding tab content

You can replace the markup of specific tabs while still relying on the framework to render the rest.  Fetch the current tab with `Pomatio_Framework_Settings::get_current_tab()` and conditionally output your own HTML.  For example, this is how we render a custom API key form:

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

To show the framework tabs above the screen title, hook into `in_admin_header` and call `Pomatio_Framework_Settings::render_tabs()` when the current screen ID matches your settings page.

```php
add_action('in_admin_header', function () {
    $screen = get_current_screen();

    if (substr($screen->id, -strlen('dummy-slug')) === 'dummy-slug') {
        $settings_path = require __DIR__ . '/settings.php';
        Pomatio_Framework_Settings::render_tabs('dummy-slug', $settings_path);
    }
});
```

## Loading tweak definitions

If you use tweak folders or other modular features, read the `enabled_settings.php` file provided by the Pomatio storage directory and include the corresponding PHP files inside your admin class constructor.

```php
use PomatioFramework\Pomatio_Framework_Disk;

$enabled_settings = Pomatio_Framework_Disk::read_file('enabled_settings.php', 'dummy-slug', 'array');

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

Anywhere in your plugin you can access saved settings via `Pomatio_Framework_Settings::get_setting_value()`:

```php
$value = Pomatio_Framework_Settings::get_setting_value('dummy-slug', 'general', 'feature-toggle', 'checkbox');
```

Use these helpers to build fully-featured admin experiences backed by Pomatio Framework.
