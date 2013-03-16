<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_tree\tests\fixture\source;;

class ImageFixture extends \li3_fixtures\test\Fixture {

	protected $_model = 'li3_tree\tests\fixture\model\Image';

    protected $_fields = [
		'id' => ['type' => 'id'],
		'gallery_id' => ['type' => 'integer', 'length' => 11],
		'image' => ['type' => 'string', 'length' => 255],
		'title' =>  ['type' => 'string', 'length' => 50]
	];

	protected $_records = [
		['id' => 1, 'gallery_id' => 1, 'image' => 'someimage.png', 'title' => 'Amiga 1200'],
		['id' => 2, 'gallery_id' => 1, 'image' => 'image.jpg', 'title' => 'Srinivasa Ramanujan'],
		['id' => 3, 'gallery_id' => 1, 'image' => 'photo.jpg', 'title' => 'Las Vegas'],
		['id' => 4, 'gallery_id' => 2, 'image' => 'picture.jpg', 'title' => 'Silicon Valley'],
		['id' => 5, 'gallery_id' => 2, 'image' => 'unknown.gif', 'title' => 'Unknown']
	];
}
