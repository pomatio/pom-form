# POM Form
An easy way to render form fields in WordPress.

## Install
```
composer install --save pom-form
```

## Requirements
* PHP 7 or higher
* WordPress 5.0 or higher.

## Dependencies
This package uses the following third party libraries:
* [jQuery](https://jquery.com/): Version included in WordPress
* [Bootstrap](https://getbootstrap.com/): v4.6.0
* [select2](https://select2.org/): v4.0.13
* [CodeMirror](https://codemirror.net/): Version included in WordPress

## How to use
Example of how to render a field

```PHP
echo (new \POM\Form\Form())::add_field([
    'type' => 'text',
    'label' => 'Lorem Ipsum',
    'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit',
    'placeholder' => 'Lorem Ipsum',
    'name' => 'name',
    'class' => 'regular-text',
    'value' => '',
    'description_position' => 'below_label'
]);
```

The parameters added in the array when generating the fields are converted into attributes of the rendered field.
They can be standard parameters such as name, id, class, data- * or customized to your needs.

### Personalization parameters
| Parameter key        | Description                                                                                                                                                      |
|----------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| description_position | It allows two values (below_label / under_field) and it is used to specify if we want the description of the field to appear below the label or below the field. |

## Allowed field types
| Field                           | Description                                                                                                                                                                                                                                                                      |
|---------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [Button](#button)               |                                                                                                                                                                                                                                                                                  |
| [Checkbox](#checkbox)           | Allows to render multiple checkboxes at the same time passing them as options parameter. If this option is chosen, the value parameter can be an array to establish as checked more than one checkbox.<br>The checkbox has the value 'yes' if it is selected and 'no' otherwise. |
| [Code CSS](#code-css)           | Render a CodeMirror editor with CSS autocomplete and CSS inspection.                                                                                                                                                                                                             |
| [Code HTML](#code-html)         | Render a CodeMirror editor with HTML autocomplete and HTML inspection.                                                                                                                                                                                                           |
| [Code JS](#code-js)             | Render a CodeMirror editor with JS autocomplete and JS inspection.                                                                                                                                                                                                               |                |                                                                                                                                                                                                                                                                                  |
| [Color](#color)                 | Renders the native WordPress color picker.                                                                                                                                                                                                                                       |
| [Color palette](#color-palette) | Shows a color picker set using the 'pom_form_color_palette' filter.                                                                                                                                                                                                              |
| [Date](#date)                   |                                                                                                                                                                                                                                                                                  |
| [Datetime](#datetime)           |                                                                                                                                                                                                                                                                                  |
| [E-mail](#email)                |                                                                                                                                                                                                                                                                                  |
| [File](#file)                   |                                                                                                                                                                                                                                                                                  |
| [Gallery](#gallery)             |                                                                                                                                                                                                                                                                                  |
| [Hidden](#hidden)               |                                                                                                                                                                                                                                                                                  |
| [Icon picker](#icon-picker)     | Select icons in a simple way among all icon libraries.<br/> It has the filters 'pom_form_icon_libraries' and 'pom_form_icon_libraries_path' to set the libraries.                                                                                                                |
| [Image picker](#image-picker)   | Select an image from the media gallery.                                                                                                                                                                                                                                          |
| [Number](#number)               |                                                                                                                                                                                                                                                                                  |
| [Password](#password)           |                                                                                                                                                                                                                                                                                  |
| [Quantity](#quantity)           |                                                                                                                                                                                                                                                                                  |
| [Radio](#radio)                 |                                                                                                                                                                                                                                                                                  |
| [Range](#range)                 |                                                                                                                                                                                                                                                                                  |
| [Repeater](#repeater)           |                                                                                                                                                                                                                                                                                  |
| [Select](#select)               | Allows optgroup, compatible with select2 (In the class parameter you have to add the class select2)                                                                                                                                                                              |
| [Telephone](#telephone)         |                                                                                                                                                                                                                                                                                  |
| [Text](#text)                   |                                                                                                                                                                                                                                                                                  |
| [Textarea](#textarea)           |                                                                                                                                                                                                                                                                                  |
| [Time](#time)                   |                                                                                                                                                                                                                                                                                  |
| [Tinymce](#tinymce)             | This field supports the following specific parameters: textarea_rows (number), teeny (boolean), quicktags (boolean), wpautop (boolean), media_buttons (boolean)                                                                                                                  |
| [Toggle](#toggle)               |                                                                                                                                                                                                                                                                                  |
| [URL](#url)                     |                                                                                                                                                                                                                                                                                  |

# Button
# Checkbox
# Code CSS     
# Code HTML    
# Code JS      
# Date         
# Color        
# Color palette
# Datetime     
# Email        
# File         
# Gallery      
# Hidden       
# Icon picker  
The icon selector has the ```pom_form_icon_libraries``` filter to be able to add as many libraries as desired.

In an example to add libraries it would be the following:
```PHP
add_filter('pom_form_icon_libraries', 'add_icon_libraries');
function add_icon_libraries($libraries) {
    $libraries['test1'] = [
        'name' => 'Test 1',
        'path' => '/path/to/icons/dir'
    ];
    
    $libraries['test2'] = [
        'name' => 'Test 2',
        'path' => '/path/to/icons/dir'
    ];
    
    return $libraries;
}
```

The indexes of the array must be the literal name of the folder that contains the SVGs.

The path must be the path to the parent folder.

# Image picker
# Number
# Password     
# Quantity     
# Radio        
# Range        
# Repeater        
# Select       
# Telephone          
# Text         
# Textarea     
# Time         
# Tinymce      
# Toggle       
# URL          

## Functions to sanitize
The framework has built-in functions to sanitize each of the fields.
You can call these functions before saving values to ensure that the value you receive is safe to tamper with.

```PHP
sanitize_pom_form_button($value);
```

```PHP
sanitize_pom_form_checkbox($value);
```

```PHP
sanitize_pom_form_code_css($value);
```

```PHP
sanitize_pom_form_code_html($value);
```

```PHP
sanitize_pom_form_code_js($value);
```

```PHP
sanitize_pom_form_color($value);
```

```PHP
sanitize_pom_form_color_palette($value);
```

```PHP
sanitize_pom_form_date($value);
```

```PHP
sanitize_pom_form_datetime($value);
```

```PHP
sanitize_pom_form_email($value);
```

```PHP
sanitize_pom_form_file($value);
```

```PHP
sanitize_pom_form_gallery($value);
```

```PHP
sanitize_pom_form_hidden($value);
```

```PHP
sanitize_pom_form_icon_picker($value);
```

```PHP
sanitize_pom_form_image_picker($value);
```

```PHP
sanitize_pom_form_number($value);
```

```PHP
sanitize_pom_form_password($value);
```

```PHP
sanitize_pom_form_quantity($value);
```

```PHP
sanitize_pom_form_radio($value);
```

```PHP
sanitize_pom_form_range($value);
```

```PHP
sanitize_pom_form_repeater($value);
```

```PHP
sanitize_pom_form_select($value);
```

```PHP
sanitize_pom_form_tel($value);
```

```PHP
sanitize_pom_form_text($value);
```

```PHP
sanitize_pom_form_textarea($value);
```

```PHP
sanitize_pom_form_time($value);
```

```PHP
sanitize_pom_form_tinymce($value);
```

```PHP
sanitize_pom_form_toggle($value);
```

```PHP
sanitize_pom_form_url($value);
```
