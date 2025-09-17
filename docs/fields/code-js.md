# Code JS

The JavaScript code editor loads CodeMirror with the proper settings, reuses saved files when present, and sanitises the payload with `sanitize_pom_form_code_js()` before it is stored or written to disk from within repeaters.【F:src/Fields/Code_JS.php†L9-L52】【F:src/Pomatio_Framework.php†L103-L149】【F:src/class-sanitize.php†L80-L94】 See [Multi-line editors](../fields.md#multi-line-editors) for shared options across code fields.
