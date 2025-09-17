# TinyMCE

The TinyMCE field embeds `wp_editor()` with optional custom attributes (`textarea_rows`, `teeny`, `quicktags`, `wpautop`, `media_buttons`) and sanitises the rich text through `sanitize_pom_form_tinymce()` using the framework’s allowed HTML list.【F:src/Fields/Tinymce.php†L10-L45】【F:src/class-sanitize.php†L379-L384】【F:src/Pomatio_Framework_Helper.php†L130-L200】 See [Multi-line editors](../fields.md#multi-line-editors) for guidance.
