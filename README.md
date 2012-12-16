# Tree behavior

## Requirement

This plugin needs [li3_behaviors](https://github.com/jails/li3_behaviors).


## Installation

Checkout the code to either of your library directories:

```
cd libraries
git clone git@github.com:jails/li3_tree
```

Include the library in your `/app/config/bootstrap/libraries.php`

```
Libraries::add('li3_tree');
```

## Presentation

Model behaviors providing a simple way to extend models. This pattern allow common logic to be encapsulated inside behaviors for keeping models lite and composed only by its own business logic.

## API

Model creation:

```php
<?php
//app/models/Comments.php
namespace app\models;

class Comments extends \li3_behaviors\data\model\Behaviorable {

	public $belongsTo = array('Posts');

    protected $_actsAs = array('Tree' => array('scope' => array('post_id')));
}
?>
```

Example of use:
```php
<?php

$root1 = Comment::create(array('image_id' => 1));
$root1->save();

$root2 = Comment::create(array('image_id' => 2));
$root2->save();

$neighbor1 = Comment::create(array('image_id' => 1));
$neighbor1->save();

$neighbor1->moveDown();
$root1->moveUp();
$neighbor1->move(0);

$subelement1 = Comment::create(array('image_id' => 1, 'parent_id' => $neighbor1->id));
$subelement1->save();

var_export($root1->children());
var_export($subelement1->path());


?>
```
