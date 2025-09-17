# Fields

Every field in Pomatio Framework is declared as a PHP array and rendered by `Pomatio_Framework::add_field()`, which normalises the arguments and locates the matching renderer under `src/Fields`.【F:src/Pomatio_Framework.php†L58-L149】 The framework injects scripts such as CodeMirror, Select2, colour pickers, and repeater helpers on demand based on the field types present in your definition.【F:src/Pomatio_Framework.php†L103-L149】

## Defining fields

The minimum requirement for a field is the `type` (matching a class name in `src/Fields`) and a unique `name`. Optional keys such as `label`, `description`, `default`, and `value` are set to sensible defaults by `parse_args()` so you only override what you need.【F:src/Pomatio_Framework.php†L58-L75】

```php
[
    'type'        => 'text',
    'name'        => 'cta-title',
    'label'       => __('CTA title', 'demo'),
    'placeholder' => __('Launch offer…', 'demo'),
    'description' => __('Shown on the homepage hero.', 'demo'),
]
```

## Common parameters

| Key | Purpose |
|-----|---------|
| `label`, `description` | Human-friendly text rendered above or below the field; the position defaults to `under_field` but can be switched to `below_label`.【F:src/Pomatio_Framework.php†L61-L75】【F:src/Fields/Text.php†L15-L43】 |
| `default`, `value` | The renderer prefers a saved `value`, then falls back to `default`, so you can pre-populate fields while letting users overwrite them.【F:src/Fields/Text.php†L26-L36】 |
| `class`, `id` | Custom classes/IDs appended to the generated markup after being sanitised, handy for styling or JavaScript hooks.【F:src/Pomatio_Framework.php†L65-L69】【F:src/Fields/Textarea.php†L31-L36】 |
| `disabled` | Pass `true` to render a disabled input without losing the value on save—useful for templates or repeater defaults.【F:src/Fields/Text.php†L10-L44】 |
| `dependency` | Define conditional logic that is serialised into the `data-dependencies` attribute so JavaScript can hide or show the field based on other values.【F:src/Pomatio_Framework_Helper.php†L27-L41】 |
| `save_as` | Override the default on-disk persistence so a field can store its data as a theme mod or option (with explicit autoload flags).【F:src/Pomatio_Framework_Save.php†L43-L118】【F:src/Pomatio_Framework_Settings.php†L46-L434】 |

Because each field posts back under its `name`, `Pomatio_Framework_Save::save_settings()` can detect the field type, run the corresponding sanitizer from `class-sanitize.php`, and persist a clean value to disk.【F:src/Pomatio_Framework_Save.php†L32-L123】【F:src/class-sanitize.php†L9-L360】

### Alternative storage targets

By default, values are written to `wp-content/settings/pomatio-framework/<site>/<slug>/<setting>.php`. Adding a `save_as` key lets you reroute an individual field to the WordPress theme-mod or option APIs while keeping the rendering logic intact.【F:src/Pomatio_Framework_Save.php†L43-L118】 Supported directives are:

| `save_as` value | Storage target |
|-----------------|----------------|
| _(unset)_ | Persist to the generated PHP files (legacy behaviour). |
| `theme_mod` | Use `set_theme_mod( $field_name, $value )`. |
| `option_autoload_yes` | Use `update_option( $field_name, $value, 'yes' )`. |
| `option_autoload_no` | Use `update_option( $field_name, $value, 'no' )`. |
| `option_autoload_auto` | Use `update_option( $field_name, $value, 'auto' )`. |

The save handler normalises the directive, sanitizes the submitted payload with the same per-field callbacks, and either writes the disk file or dispatches the value to the chosen WordPress API.【F:src/Pomatio_Framework_Save.php†L43-L118】【F:src/class-sanitize.php†L9-L360】 For code editors the framework still strips slashes and only writes to disk when the field sticks with file storage, ensuring you are not left with stale files after switching to a theme mod or option.【F:src/Pomatio_Framework_Save.php†L61-L118】

Whenever a field opts into theme mods or options, the framework records a metadata entry containing the field name, target, autoload flag, and declared default inside `fields_save_as.php`. The map lives next to the normal settings files and is regenerated on every save with opcache invalidated automatically, so runtime lookups stay fresh.【F:src/Pomatio_Framework_Save.php†L99-L152】 Removing `save_as` cleans up the metadata and the value is written back to the usual PHP array on the next save.【F:src/Pomatio_Framework_Save.php†L118-L128】

`Pomatio_Framework_Settings::get_setting_value()` consults the metadata map before hitting the filesystem: if a field is mapped to a theme mod or option it fetches the external value (falling back to the stored default when empty) and only then applies the requested sanitizer.【F:src/Pomatio_Framework_Settings.php†L46-L78】 The admin renderer relies on the same getter, so forms are pre-populated from theme mods/options without duplicating logic, and code editors continue to read from disk only when they actually persisted a file.【F:src/Pomatio_Framework_Settings.php†L206-L434】 This means translation registration, repeaters, and other consumers automatically see the up-to-date value regardless of where it lives.

```php
[
    'type'    => 'toggle',
    'name'    => 'hero_cta',
    'label'   => __('Enable hero CTA', 'demo'),
    'default' => false,
    'save_as' => 'option_autoload_yes',
],

[
    'type'    => 'image-picker',
    'name'    => 'header_logo',
    'label'   => __('Header logo', 'demo'),
    'save_as' => 'theme_mod',
],
```

Both fields continue to use the existing sanitizers; they simply persist and load through WordPress’ option/theme-mod APIs instead of the generated PHP arrays.

## Dependencies

Any field can react to other inputs by providing a `dependency` array. `Pomatio_Framework_Helper::get_dependencies_data_attr()` JSON-encodes the structure and stores it as a data attribute without forcing you to write boilerplate, so repeaters, selects, and custom inputs can all participate in complex flows.【F:src/Pomatio_Framework_Helper.php†L27-L41】【F:src/Fields/Select.php†L10-L41】

## Working with repeaters

The repeater field is a container that renders a list of child fields, supports defaults, cloning, sorting, nested repeaters, and per-item dependencies.【F:src/Fields/Repeater.php†L15-L209】 It stores its configuration alongside the saved values in a hidden input (`data-type="repeater"`), and during sanitisation each nested field is processed with its own sanitizer so code editors are written to disk while plain inputs remain inline.【F:src/Fields/Repeater.php†L210-L254】【F:src/class-sanitize.php†L264-L316】 Useful options include:

- `fields` – Array of child field definitions to render inside each item.
- `title` – Heading shown in the draggable item header.
- `limit` – Maximum number of items; the sanitizer honours the limit during the save loop.【F:src/Fields/Repeater.php†L23-L61】【F:src/class-sanitize.php†L273-L280】
- `defaults` – Seed items that can be restored individually or en masse, including `can_be_removed` flags for mandatory rows.【F:src/Fields/Repeater.php†L62-L118】
- `cloneable`, `sortable`, `disable_new` – Toggle cloning, drag-and-drop ordering, or the “Add new” button.【F:src/Fields/Repeater.php†L29-L104】【F:src/Fields/Repeater.php†L199-L209】

Inside a repeater you can still use dependencies (`used_for_title` on text fields updates the handle label) and nested repeaters, and when a child is a code editor the framework automatically enqueues CodeMirror before the Ajax templates are requested.【F:src/Fields/Text.php†L23-L44】【F:src/Pomatio_Framework.php†L103-L149】 Nested code editor values are written to files whose paths are stored in the saved JSON, so your callback can read the actual contents later.【F:src/class-sanitize.php†L295-L306】

### Comprehensive repeater example

The snippet below mirrors a real-world permission matrix. It contains nested repeaters, dependencies, WooCommerce lookups, and conditional subscription fields. All of the options come straight from the framework—no custom rendering needed.

```php
return [
    [
        'type'  => 'repeater',
        'title' => __('Permission rules', POM_THEME_SLUG),
        'name'  => 'permission_rules',
        'fields' => [
            [
                'type'           => 'Text',
                'name'           => 'rule_name',
                'label'          => __('Rule name', POM_THEME_SLUG),
                'used_for_title' => true,
            ],
            [
                'type'   => 'repeater',
                'title'  => __('Rule', POM_THEME_SLUG),
                'name'   => 'rule',
                'fields' => [
                    [
                        'type'           => 'Text',
                        'name'           => 'rule_name',
                        'label'          => __('Rule name', POM_THEME_SLUG),
                        'used_for_title' => true,
                    ],
                    [
                        'type'    => 'select',
                        'name'    => 'rule_type',
                        'label'   => __('Rule type', POM_THEME_SLUG),
                        'options' => $rule_types,
                        'default' => 'none'
                    ],
                    [
                        'type'       => 'select',
                        'name'       => 'roles',
                        'multiple'   => true,
                        'label'      => __('Roles', POM_THEME_SLUG),
                        'options'    => pom_get_roles(true),
                        'dependency' => [[[ 'field' => 'rule_type', 'values' => ['role_is', 'role_is_not'] ]]],
                    ],
                    // … additional dependent selects and number fields …
                ],
            ],
            [
                'type'    => 'select',
                'name'    => 'condition_between_rules',
                'label'   => __('Rule type', POM_THEME_SLUG),
                'options' => [
                    ''   => __('Set a condition', POM_THEME_SLUG),
                    'and' => __('AND', POM_THEME_SLUG),
                    'or'  => __('OR', POM_THEME_SLUG),
                ],
                'default' => 'and',
            ],
        ],
    ],
];
```

When this structure is posted, `sanitize_pom_form_repeater()` walks each layer, enforces the limit, and delegates to the appropriate sanitizers (`sanitize_pom_form_select`, `sanitize_pom_form_number`, etc.) so the saved JSON mirrors the UI without exposing unsanitised input.【F:src/class-sanitize.php†L264-L327】

## Field reference

The framework ships a broad collection of fields. Use the grouped reference below to pick the right control and learn about its quirks.

### Single-line inputs

Text, Email, Url, Tel, Password, Number, Date, Datetime, and Time all follow the same pattern: they render labelled `<input>` elements, honour placeholders and defaults, propagate dependency attributes, and respect a `disabled` flag.【F:src/Fields/Text.php†L9-L44】【F:src/Fields/Email.php†L9-L42】【F:src/Fields/Url.php†L9-L44】【F:src/Fields/Tel.php†L9-L44】【F:src/Fields/Password.php†L9-L44】【F:src/Fields/Number.php†L9-L44】【F:src/Fields/Date.php†L9-L44】【F:src/Fields/Datetime.php†L9-L44】【F:src/Fields/Time.php†L9-L41】 Their values are cleaned by dedicated sanitizers such as `sanitize_pom_form_text()`, `sanitize_pom_form_email()`, `sanitize_pom_form_url()`, `sanitize_pom_form_tel()`, `sanitize_pom_form_number()`, `sanitize_pom_form_date()`, `sanitize_pom_form_datetime()`, and `sanitize_pom_form_time()` so you can trust the persisted data.【F:src/class-sanitize.php†L108-L160】【F:src/class-sanitize.php†L180-L205】【F:src/class-sanitize.php†L343-L360】

### Multi-line editors

Textarea outputs a plain `<textarea>` and honours dependency metadata, whereas Tinymce wraps `wp_editor()` so you can control toolbar options via `custom_attrs` like `textarea_rows`, `teeny`, `quicktags`, `wpautop`, and `media_buttons`.【F:src/Fields/Textarea.php†L9-L44】【F:src/Fields/Tinymce.php†L10-L45】 Code editors (`code_html`, `code_css`, `code_js`, `code_json`) render CodeMirror-backed textareas, fetch the stored file contents if the value is a path, and enqueue the editor assets automatically.【F:src/Fields/Code_HTML.php†L10-L53】【F:src/Pomatio_Framework.php†L103-L149】 Corresponding sanitizers ensure HTML is KSES-filtered, CSS/JS/JSON are stripped of unsafe content, and CodeMirror values are saved to disk when nested inside repeaters.【F:src/class-sanitize.php†L53-L121】【F:src/class-sanitize.php†L295-L306】

### Choice fields

Checkbox, Radio, Toggle, Select, Range, Quantity, Color, and Color Palette provide rich selection UIs:

- **Checkbox** renders either a single yes/no toggle or multiple checkboxes with an array value, seeding “no” via a hidden input for unchecked states.【F:src/Fields/Checkbox.php†L7-L52】 `sanitize_pom_form_checkbox()` converts the payload into `yes`/`no` strings or an array of selected keys.【F:src/class-sanitize.php†L29-L45】
- **Radio** and **Radio Icons** output mutually exclusive options, the latter loading SVG icons from disk and providing a restore-default shortcut.【F:src/Fields/Radio.php†L7-L42】【F:src/Fields/Radio_Icons.php†L7-L74】 Both rely on `sanitize_pom_form_radio()`/`sanitize_pom_form_radio_icons()` to strip unwanted characters.【F:src/class-sanitize.php†L215-L224】
- **Toggle** wraps a styled checkbox with automatic IDs and enqueues the CSS needed for the slider UI.【F:src/Fields/Toggle.php†L9-L49】
- **Select** supports single or multiple values, optgroups, dependency metadata, and automatically enqueues the Select2-compatible script when used inside repeaters.【F:src/Fields/Select.php†L9-L62】【F:src/Pomatio_Framework.php†L125-L132】 `sanitize_pom_form_select()` stores multi-select choices as comma-separated strings for convenience.【F:src/class-sanitize.php†L319-L326】
- **Range** renders a slider paired with a numeric input and optional suffix, with helpers to restore the default value.【F:src/Fields/Range.php†L7-L69】 Sanitization allows fractional numbers via `sanitize_pom_form_range()`.【F:src/class-sanitize.php†L227-L231】
- **Quantity** provides plus/minus controls around a numeric input, enqueuing the JS required to bump the value, while `sanitize_pom_form_quantity()` normalises the number.【F:src/Fields/Quantity.php†L9-L48】【F:src/class-sanitize.php†L209-L212】
- **Color** and **Color Palette** integrate with the WordPress color picker and custom palette filters, storing hex values through `sanitize_pom_form_color()`/`sanitize_pom_form_color_palette()`.【F:src/Fields/Color.php†L7-L43】【F:src/Fields/Color_Palette.php†L7-L87】【F:src/class-sanitize.php†L96-L105】

### Media pickers and assets

File, Image Picker, Gallery, Icon Picker, Signature, Background Image, Font, and Font Picker are specialised helpers for handling files:

- **File** exposes a simple `<input type="file">` and delegates further processing to your save callback; the default sanitizer allows you to filter file types via WordPress filters.【F:src/Fields/File.php†L7-L30】【F:src/class-sanitize.php†L130-L147】
- **Image Picker** and **Gallery** open the WordPress media modal, store selected IDs/URLs, and enqueue their own CSS/JS wrappers.【F:src/Fields/Image_Picker.php†L7-L61】【F:src/Fields/Gallery.php†L7-L64】 `sanitize_pom_form_image_picker()` and `sanitize_pom_form_gallery()` ensure URLs are valid and gallery IDs contain only digits/commas.【F:src/class-sanitize.php†L157-L193】
- **Icon Picker** lists the available icon libraries (filterable via `Pomatio_Framework_Helper::get_icon_libraries()`) and lets the user choose an SVG; the stored value is sanitised as a URL.【F:src/Fields/Icon_Picker.php†L7-L113】【F:src/Pomatio_Framework_Helper.php†L79-L92】【F:src/class-sanitize.php†L164-L179】
- **Signature** renders a canvas-based signature pad, saves the base64 image via `Pomatio_Framework_Disk::save_signature_image()`, and sanitizes through `sanitize_pom_form_signature()`.【F:src/Fields/Signature.php†L7-L52】【F:src/Pomatio_Framework_Disk.php†L84-L119】【F:src/class-sanitize.php†L329-L333】
- **Background Image** combines multiple sub-fields (image picker, alignment radios, size selectors) inside a wrapper so you can capture every CSS background property from a single composite field.【F:src/Fields/Background_Image.php†L10-L140】 The posted data is JSON-encoded and decoded by `sanitize_pom_form_background_image()`.【F:src/class-sanitize.php†L9-L21】
- **Font** uses nested repeaters to collect font families, fallbacks, and variant metadata, delegating each variant to the `Font_Picker` field for file uploads.【F:src/Fields/Font.php†L9-L111】【F:src/Fields/Font_Picker.php†L9-L52】 Font uploads are restricted to the extensions returned by `Pomatio_Framework_Helper::get_allowed_font_types()` and sanitised via `sanitize_pom_form_font()` / `sanitize_pom_form_font_picker()`.【F:src/Pomatio_Framework_Helper.php†L360-L371】【F:src/class-sanitize.php†L233-L260】

### Structural helpers

Button, Hidden, Separator, and Background Image wrappers help you compose the layout around your fields:

- **Button** renders either an `<input type="button">`/`submit` or an `<a>` tag when you pass `link => true`, making it ideal for secondary actions like resets or downloads.【F:src/Fields/Button.php†L7-L31】
- **Hidden** stores metadata alongside the form without any UI.【F:src/Fields/Hidden.php†L7-L23】
- **Separator** prints headings and paragraphs to break long forms into digestible sections.【F:src/Fields/Separator.php†L7-L15】

With these building blocks you can mix declarative field definitions, conditional logic, repeaters, and post-save callbacks to construct complex admin interfaces while keeping your PHP code concise and maintainable.
