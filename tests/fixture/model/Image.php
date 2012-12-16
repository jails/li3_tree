<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_tree\tests\fixture\model;

class Image extends \li3_behaviors\data\model\Behaviorable {

	public $belongsTo = array('Gallery');

	public $hasMany = array('ImageTag', 'Comment');

	public $hasAndBelongsToMany = array('Tag' => array('via' => 'ImageTag'));

}
