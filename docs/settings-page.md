# Settings page
Pomatio Framework has a simple tool with which to dynamically generate settings pages.

The framework has a method that, by passing the plugin/theme identifier and the settings array,
dynamically generates the tabs and fields of each section.

Example of how to generate a settings page within a plugin:

```PHP
use POM\Form\POM_Framework_Settings;

add_action('admin_menu', function() {
    add_submenu_page(
        'options-general.php',
        __('Dummy', 'dummy-slug'),
        __('Dummy', 'dummy-slug'),
        'manage_options',
        'dummy-slug',
        'dummy_plugin_settings_page'
    );
});

function dummy_plugin_settings_page() {
    $settings_path = require '/path/to/settings/file.php'; // The value of the variable must be the settings array.
    POM_Framework_Settings::render(POMATIO_TWEAKS_SLUG, $settings_path);
}
```

As shown in the example, we first import the class that the 'render' method is in, 
and then in the callback of the ```add_submenu_page action```, call the static render function, 
passing it the two required parameters.
