<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_tree\tests\fixture\source;

class ImageTagFixture extends \li3_fixtures\test\Fixture {

	protected $_model = 'li3_tree\tests\fixture\model\ImageTag';

    protected $_fields = array(
		'id' => array('type' => 'id'),
		'image_id' => array('type' => 'integer', 'length' => 11),
		'tag_id' =>  array('type' => 'integer', 'length' => 11)
	);

	protected $_records = array(
		array('id' => 1, 'image_id' => 1, 'tag_id' => 1),
		array('id' => 2, 'image_id' => 1, 'tag_id' => 4),
		array('id' => 3, 'image_id' => 2, 'tag_id' => 6),
		array('id' => 4, 'image_id' => 3, 'tag_id' => 7),
		array('id' => 5, 'image_id' => 4, 'tag_id' => 7),
		array('id' => 6, 'image_id' => 4, 'tag_id' => 4),
		array('id' => 7, 'image_id' => 4, 'tag_id' => 1)
	);
}
