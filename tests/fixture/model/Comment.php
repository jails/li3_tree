<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_tree\tests\fixture\model;

class Comment extends \li3_behaviors\data\model\Behaviorable {

	public $belongsTo = array('Image');

	protected $_actsAs = array('Tree');
}