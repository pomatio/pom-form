# Helpers

Pomatio Framework exposes several helper classes so you can inspect configuration, generate dependency metadata, and manipulate files without digging into WordPress internals yourself.

## `Pomatio_Framework_Helper`

* **Dependency attributes** – `get_dependencies_data_attr()` converts the `dependency` structure you define in a field into the JSON payload that drives conditional display in JavaScript.【F:src/Pomatio_Framework_Helper.php†L27-L41】
* **Utility conversions** – Use `convert_array_to_html_attributes()` and `convert_html_attributes_to_array()` to move between associative arrays and attribute strings when you extend the framework.【F:src/Pomatio_Framework_Helper.php†L44-L67】
* **Runtime helpers** – Functions such as `generate_random_string()`, `write_log()`, and `path_to_url()` help you build dynamic field IDs, debug issues, and turn filesystem paths into public URLs.【F:src/Pomatio_Framework_Helper.php†L69-L128】
* **Settings lookups** – `get_settings()` returns the raw settings array for a given tab/subsection and `get_allowed_html()` exposes the HTML tags that text editors sanitize against.【F:src/Pomatio_Framework_Helper.php†L130-L200】

## `Pomatio_Framework_Disk`

* **Automatic directories** – `create_settings_dir()` provisions `wp-content/settings/pomatio-framework/<site>/<slug>/` (including `.htaccess`) the first time a page saves values, making the storage multisite-aware.【F:src/Pomatio_Framework_Disk.php†L128-L156】
* **File serialization** – `generate_file_content()` turns an array into a PHP file with metadata headers, while `save_to_file()` writes arbitrary content such as code editor values and returns the saved path.【F:src/Pomatio_Framework_Disk.php†L170-L212】
* **Reading and cleanup** – `read_file()` and `delete_file()` give you convenient access to the stored configuration and let you remove generated assets when a tweak is disabled.【F:src/Pomatio_Framework_Disk.php†L214-L241】
* **Font and signature support** – The constructor hooks into `upload_dir`/`upload_mimes` so custom fonts are stored under `/fonts`, and the signature helpers persist base64 canvases under a locked-down directory.【F:src/Pomatio_Framework_Disk.php†L10-L119】

## `Pomatio_Framework_Settings`

* **Navigation helpers** – `get_current_tab()` and `get_current_subsection()` inspect the current request (or default to the first entries) so you can render context-aware navigation or run callbacks only on the active screen.【F:src/Pomatio_Framework_Settings.php†L10-L34】
* **Field metadata** – `read_fields()` loads the `fields.php` definition for a given setting, and `is_setting_enabled()` checks `enabled_settings.php` to see whether a tweak is turned on.【F:src/Pomatio_Framework_Settings.php†L36-L65】
* **Value retrieval** – `get_setting_value()` inspects the `fields_save_as.php` metadata, pulls values from theme mods or options when a field declares `save_as`, falls back to the generated PHP arrays, and optionally re-sanitizes the result—ideal for templates and background jobs.【F:src/Pomatio_Framework_Settings.php†L46-L78】

Leverage these helpers together with the automatic save routine to keep your own code focused on business logic rather than boilerplate persistence—the save handler also updates the metadata file whenever a field switches between disk storage and external APIs.【F:src/Pomatio_Framework_Save.php†L10-L152】
