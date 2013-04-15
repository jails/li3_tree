# Tree behavior

## Requirements

- **PHP 5.4**
- This plugin needs [li3_behaviors](https://github.com/jails/li3_behaviors).
- This plugin needs [li3_fixtures](https://github.com/UnionOfRAD/li3_fixtures) (only if you intend to run tests).

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

This behavior store hierarchical datas in a database table using the MPTT logic.

## Constraints

To use the tree behavior, your table needs the following 3 extra fields:

- The `'parent'` config field. By default the field must be named `parent_id` in the table.
- The `'left'` config field. By default the field must be named `lft` in the table.
- The `'right'` config field. By default the field must be named `rght` in the table.

## API

Example of attaching the tree behavior to a model:

```php
<?php
//app/models/Comments.php
namespace app\models;

use li3_behaviors\data\model\Behaviors;

class Comments extends \lithium\data\Model {
	use Behaviors;

	public $belongsTo = ['Posts'];

    protected $_actsAs = ['Tree' => ['scope' => ['post_id']]];
}
?>
```

### Options

In order to store multiple trees in the same table you need to set the `'scope'` option. For example, if a `Post` hasMany `Comment` and a `Comment` belongsTo a `Post`. To allow multiple `Comment` trees in the same table, you must scope `Comment` by the foreign key `post_id`. This way all `Comment` trees will be independant.

`'scope'` can be a full condition:

```php
 protected $_actsAs = ['Tree' => ['scope' => ['region' => 'head']]];
```

Or a simple fieldname like a foreign key:

```php
 protected $_actsAs = ['Tree' => ['scope' => ['post_id']]];
```

In this last case, the full condition will be populated from entity datas. This mean you can't do any CRUD action if the entity datas don't contain all necessary datas for perfoming a well scoped CRUD action.

### Example of use:
```php
<?php

$root1 = Comment::create(['post_id' => 1]);
$root1->save();

$root2 = Comment::create(['post_id' => 2]);
$root2->save();

$neighbor1 = Comment::create(['post_id' => 1]);
$neighbor1->save();

$neighbor1->moveDown();
$root1->moveUp();
$neighbor1->move(0);

$subelement1 = Comment::create(['post_id' => 1, 'parent_id' => $neighbor1->id]);
$subelement1->save();

var_export($root1->childrens());
var_export($subelement1->path());
?>
```

## Greetings

The li3 team, Vogan and all others which make that possible (I mean only because Chuck Norris agreed).

## Build status
[![Build Status](https://secure.travis-ci.org/jails/li3_tree.png?branch=master)](http://travis-ci.org/jails/li3_tree)


