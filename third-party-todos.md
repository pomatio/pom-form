# Theme field definition fixes

- **Use a non-numeric slug default.** The repeater "Button slug" field currently defaults to `2`, which produces a `.web-btn-2` class that starts with a digit. Replace the default with a sanitized, human-readable slug (for example `primary` or leave empty) to avoid CSS class escaping problems and accidental duplicate slugs across items.
- **Correct the border style option key.** The border style list includes `ridged`, but the valid CSS keyword is `ridge`. Update the option key to `ridge` (and any related defaults) so saved values map to the actual CSS border style.
