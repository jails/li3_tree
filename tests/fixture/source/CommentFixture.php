<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_tree\tests\fixture\source;

class CommentFixture extends \li3_fixtures\test\Fixture {

    protected $_model = 'li3_tree\tests\fixture\model\Comment';

	protected $_fields = array(
		'id' => array('type' => 'id'),
		'image_id' => array('type' => 'integer'),
		'body' => array('type' => 'text'),
		'parent_id' => array('type' => 'integer'),
		'lft' => array('type' => 'integer'),
		'rght' => array('type' => 'integer'),
		'published' => array('type' => 'string', 'length' => 1, 'default' => 'N')
	);

	protected $_records = array(
		array('id' => 1, 'image_id' => 1, 'body' => 'Comment 1', 'parent_id' => null, 'lft' => 1,  'rght' => 8, 'published' => 'Y'),
		array('id' => 2, 'image_id' => 1, 'body' => 'Comment 1.1', 'parent_id' => 1, 'lft' => 2,  'rght' => 7, 'published' => 'Y'),
		array('id' => 3, 'image_id' => 1, 'body' => 'Comment 1.1.1', 'parent_id' => 2, 'lft' => 3,  'rght' => 4, 'published' => 'N'),
		array('id' => 4, 'image_id' => 1, 'body' => 'Comment 1.1.2', 'parent_id' => 2, 'lft' => 5, 'rght' => 6, 'published' => 'Y'),
		array('id' => 5, 'image_id' => 3, 'body' => 'Comment 2', 'parent_id' => null, 'lft' => 1,  'rght' => 6, 'published' => 'Y'),
		array('id' => 6, 'image_id' => 3, 'body' => 'Comment 2.1', 'parent_id' => 5, 'lft' => 2, 'rght' => 3, 'published' => 'Y'),
		array('id' => 7, 'image_id' => 3, 'body' => 'Comment 2.2', 'parent_id' => 5, 'lft' => 4, 'rght' => 5, 'published' => 'N')
	);
}

?>