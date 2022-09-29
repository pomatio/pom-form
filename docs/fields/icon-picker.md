# Icon picker
Select icons in a simple way among all icon libraries.

The icon selector has the ```pom_form_icon_libraries``` filter to be able to add as many libraries as desired.

An example to add libraries would be the following:

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

How to render an Icon picker:

```PHP
echo (new \PomatioFramework\Form())::add_field([
    'type' => 'icon_picker',
    'label' => 'Lorem Ipsum',
    'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit.',
    'name' => 'icon',
]);
```
