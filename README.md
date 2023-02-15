# Pomatio Framework
An easy way to render form fields in WordPress.

## Install
```
composer require pom/form
```

## Requirements
* PHP 7.4 or higher
* WordPress 5.0 or higher.

## Dependencies
This package uses the following third party libraries:
* [jQuery](https://jquery.com/): Version included in WordPress
* [Bootstrap](https://getbootstrap.com/): v4.6.0
* [select2](https://select2.org/): v4.0.13
* [CodeMirror](https://codemirror.net/): Version included in WordPress

## How to use
To output any field first you need to import the required class:

```php
use PomatioFramework\Pomatio_Framework;
```

Example of how to render a field:

```php
echo (new Pomatio_Framework())::add_field([
    'type' => 'text',
    'label' => 'Test Framework',
    'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit.',
    'placeholder' => 'Lorem Ipsum', // optional
    'name' => 'name',
    'class' => 'regular-text',
    'value' => '',
    'description_position' => 'below_label' // optional
    'default' => '' // optional
]);
```

## Docs
The framework is fully documented [here](docs/index.md).
