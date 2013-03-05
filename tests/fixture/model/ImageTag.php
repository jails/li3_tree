<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_tree\tests\fixture\model;

use li3_behaviors\data\model\Behaviors;

class ImageTag extends \lithium\data\Model {
	use Behaviors;

	public $belongsTo = ['Image', 'Tag'];

}