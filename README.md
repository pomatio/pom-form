# POM Form
An easy way to render form fields.

## Install
```
composer install --save pom-form
```

## Requirements
* PHP 7 or higher
* WordPress 5.0 or higher.

## Dependencies
This package uses the following third party libraries:
* jQuery
* Bootstrap: v3.4.1
* select2: v4.0.13

## How to use
Example of how to render a field
```
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
Parameter key | Description
------------- | -------------
description_position  | It allows two values (below_label / under_field) and it is used to specify if we want the description of the field to appear below the label or below the field.

## Allowed field types
Field | Description
------------- | -------------
button |
checkbox | Allows to render multiple checkboxes at the same time passing them as options parameter. If this option is chosen, the value parameter can be an array to establish as checked more than one checkbox.
email |
file |
hidden |
password |
radio |
range |
select | Allows optgroup, compatible with select2 (In the class parameter you have to add the class select2)
tel |
text |
textarea |
toggle |
url |
