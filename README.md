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
| [button](#button)               |                                                                                                                                                                                                                                                                                  |
| [checkbox](#checkbox)           | Allows to render multiple checkboxes at the same time passing them as options parameter. If this option is chosen, the value parameter can be an array to establish as checked more than one checkbox.<br>The checkbox has the value 'yes' if it is selected and 'no' otherwise. |
| [code_css](#code-css)           | Render a CodeMirror editor with CSS autocomplete and CSS inspection.                                                                                                                                                                                                             |
| [code_html](#code-html)         | Render a CodeMirror editor with HTML autocomplete and HTML inspection.                                                                                                                                                                                                           |
| [code_js](#code-js)             | Render a CodeMirror editor with JS autocomplete and JS inspection.                                                                                                                                                                                                               |                |                                                                                                                                                                                                                                                                                  |
| [color](#color)                 | Renders the native WordPress color picker.                                                                                                                                                                                                                                       |
| [color_palette](#color-palette) | Shows a color picker set using the 'pom_form_color_palette' filter.                                                                                                                                                                                                              |
| [date](#date)                   |                                                                                                                                                                                                                                                                                  |
| [datetime](#datetime)           |                                                                                                                                                                                                                                                                                  |
| [email](#email)                 |                                                                                                                                                                                                                                                                                  |
| [file](#file)                   |                                                                                                                                                                                                                                                                                  |
| [gallery](#gallery)             |                                                                                                                                                                                                                                                                                  |
| [hidden](#hidden)               |                                                                                                                                                                                                                                                                                  |
| [icon_picker](#icon-picker)     | Select icons in a simple way among all icon libraries.<br/> It has the filters 'pom_form_icon_libraries' and 'pom_form_icon_libraries_path' to set the libraries.                                                                                                                |
| [image_picker](#image-picker)   | Select an image from the media gallery.                                                                                                                                                                                                                                          |
| [number](#number)               |                                                                                                                                                                                                                                                                                  |
| [password](#password)           |                                                                                                                                                                                                                                                                                  |
| [quantity](#quantity)           |                                                                                                                                                                                                                                                                                  |
| [radio](#radio)                 |                                                                                                                                                                                                                                                                                  |
| [range](#range)                 |                                                                                                                                                                                                                                                                                  |
| [repeater](#repeater)           |                                                                                                                                                                                                                                                                                  |
| [select](#select)               | Allows optgroup, compatible with select2 (In the class parameter you have to add the class select2)                                                                                                                                                                              |
| [tel](#telephone)               |                                                                                                                                                                                                                                                                                  |
| [text](#text)                   |                                                                                                                                                                                                                                                                                  |
| [textarea](#textarea)           |                                                                                                                                                                                                                                                                                  |
| [time](#time)                   |                                                                                                                                                                                                                                                                                  |
| [tinymce](#tinymce)             | This field supports the following specific parameters: textarea_rows (number), teeny (boolean), quicktags (boolean), wpautop (boolean), media_buttons (boolean)                                                                                                                  |
| [toggle](#toggle)               |                                                                                                                                                                                                                                                                                  |
| [url](#url)                     |                                                                                                                                                                                                                                                                                  |

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
