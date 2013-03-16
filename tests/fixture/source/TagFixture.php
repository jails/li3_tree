<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_tree\tests\fixture\source;

class TagFixture extends \li3_fixtures\test\Fixture {

	protected $_model = 'li3_tree\tests\fixture\model\Tag';

	protected $_fields = [
		'id' => ['type' => 'id'],
		'name' => ['type' => 'string', 'length' => 50],
		'author_id' => ['type' => 'integer', 'length' => 11]
	];

	protected $_records = [
		['id' => 1, 'name' => 'High Tech', 'author_id' => 6],
		['id' => 3, 'name' => 'Sport', 'author_id' => 9],
		['id' => 4, 'name' => 'Computer', 'author_id' => 6],
		['id' => 5, 'name' => 'Art', 'author_id' => 2],
		['id' => 6, 'name' => 'Science', 'author_id' => 1],
		['id' => 7, 'name' => 'City', 'author_id' => 2]
	];
}
