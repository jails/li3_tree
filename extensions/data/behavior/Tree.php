<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_tree\extensions\data\behavior;

use lithium\core\ConfigException;
use UnexpectedValueException;

class Tree extends \li3_behaviors\data\model\Behavior {

	/**
	 * Default tree configuration
	 *
	 * @var array
	 */
	protected $_defaults = array(
		'parent' => 'parent_id',
		'left' => 'lft',
		'right' => 'rght',
		'recursive' => false,
		'scope' => array()
	);

	/**
	 * Constructor
	 *
	 * @param array $config The configuration array
	 */
	public function __construct($config = array()){
		parent::__construct($config + $this->_defaults);
	}

	/**
	 * Initializer function called by the constructor unless the constructor `'init'` flag is set
	 * to `false`.
	 *
	 * @see lithium\core\Object
	 * @throws ConfigException
	 */
	public function _init() {
		parent::_init();
		if (!$model = $this->_model) {
			throw new ConfigException("`'model'` option needs to be defined.");
		}
		$behavior = $this;
		$model::applyFilter('save', function($self, $params, $chain) use ($behavior) {
			if ($behavior->invokeMethod('_beforeSave', array($params))) {
				return $chain->next($self, $params, $chain);
			}
		});

		$model::applyFilter('delete', function($self, $params, $chain) use ($behavior) {
			if ($behavior->invokeMethod('_beforeDelete', array($params))) {
				return $chain->next($self, $params, $chain);
			}
		});
	}

	/**
	 * Setting a scope to an entity node.
	 *
	 * @param object $entity
	 * @return array The scope values
	 * @throws UnexpectedValueException
	 */
	protected function _scope($entity) {
		$scope = array();
		foreach ($this->_config['scope'] as $key => $value) {
			if (is_numeric($key)) {
				if (isset($entity, $value)) {
					$scope[$value] = $entity->$value;
				} else {
					$message = "The `{$value}` scope in not present in the entity.";
					throw new UnexpectedValueException($message);
				}
			} else {
				$scope[$key] = $value;
			}
		}
		return $scope;
	}

	/**
	 * Returns all childrens of given element (including subchildren if `$recursive` is set
	 * to true or recursive is configured true)
	 *
	 * @param object $entity The entity to fetch the children of
	 * @param Boolean $recursive Overrides configured recursive param for this method
	 */
	public function childrens($entity, $rec = null, $mode = 'all') {
		extract($this->_config);

		$recursive = $rec ? : $recursive;

		if ($recursive) {
			if ($mode === 'count') {
				return ($entity->$right - $entity->$left - 1) / 2;
			} else {
				return $model::find($mode, array(
					'conditions' => array(
						$left => array('>' => $entity->$left),
						$right => array('<' => $entity->$right)
					) + $this->_scope($entity),
					'order' => array($left => 'asc'))
				);
			}
		} else {
			$id = $entity->{$model::key()};
			return $model::find($mode, array(
				'conditions' => array($parent => $id) + $this->_scope($entity),
				'order' => array($left => 'asc'))
			);
		}
	}

	/**
	 * Get path
	 *
	 * returns an array containing all elements from the tree root node to the node with given
	 * an entity node (including this entity node) which have a parent/child relationship
	 *
	 * @param object $entity
	 */
	public function path($entity) {
		extract($this->_config);

		$data = array();
		while ($entity->data($parent) != null) {
			$data[] = $entity;
			$entity = $this->_getById($entity->$parent);
		}
		$data[] = $entity;
		$data = array_reverse($data);
		$model = $entity->model();
		return $model::connection()->item($model, $data, array('exists' => true, 'class' => 'set'));
	}

	/**
	 * Move
	 *
	 * performs move operations of an entity in tree
	 *
	 * @param object $entity the entity node to move
	 * @param integer $newPosition Position new position of node in same level, starting with 0
	 * @param object $newParent The new parent entity node
	 */
	public function move($entity, $newPosition, $newParent = null) {
		extract($this->_config);

		if ($newParent !== null) {
			if($this->_scope($entity) !== ($parentScope = $this->_scope($newParent))) {
				$entity->set($parentScope);
			} elseif ($newParent->$left > $entity->$left && $newParent->$right < $entity->$right) {
				return false;
			}
			$parentId = $newParent->data($model::key());
			$entity->set(array($parent => $parentId));
			$entity->save();
			$parentNode = $newParent;
		} else {
			$newParent = $this->_getById($entity->$parent);
		}

		$childrenCount = $newParent->childrens(false, 'count');
		$position = $this->_getPosition($entity, $childrenCount);
		if ($position !== false) {
			$count = abs($newPosition - $position);

			for ($i = 0; $i < $count; $i++) {
				if ($position < $newPosition) {
					$entity->moveDown();
				} else {
					$entity->moveUp();
				}
			}
		}
		return true;
	}

	/**
	 * Before save
	 *
	 * this method is called befor each save
	 *
	 * @param array $params
	 */
	protected function _beforeSave($params) {
		extract($this->_config);
		$entity = $params['entity'];

		if (!$entity->data($model::key())) {
			if ($entity->$parent) {
				$this->_insertParent($entity);
			} else {
				$max = $this->_getMax($entity);
				$entity->set(array(
					$left => $max + 1,
					$right => $max + 2
				));
			}
		} elseif (isset($entity->$parent)) {
			if ($entity->$parent === $entity->data($model::key())) {
				return false;
			}
			$oldNode = $this->_getById($entity->data($model::key()));
			if ($oldNode->$parent === $entity->$parent) {
				return true;
			}
			if (($newScope = $this->_scope($entity)) !== ($oldScope = $this->_scope($oldNode))) {
				$this->_updateScope($entity, $oldScope, $newScope);
				return true;
			}
			$this->_updateNode($entity);
		}
		return true;
	}

	/**
	 * Before delete
	 *
	 * this method is called befor each save
	 *
	 * @param array $params
	 */
	protected function _beforeDelete($params) {
		return $this->_deleteFromTree($params['entity']);
	}

	/**
	 * Insert a parent
	 *
	 * inserts a node at given last position of parent set in $entity
	 *
	 * @param object $entity
	 */
	protected function _insertParent($entity) {
		extract($this->_config);
		$parent = $this->_getById($entity->$parent);
		if ($parent) {
			$r = $parent->$right;
			$this->_update($r, '+', 2, $this->_scope($entity));
			$entity->set(array(
				$left => $r,
				$right => $r + 1
			));
		}
	}

	/**
	 * Update a node (when parent is changed)
	 *
	 * all the "move an element with all its children" magic happens here!
	 * first we calculate movements (shiftX, shiftY), afterwards shifting of ranges is done,
	 * where rangeX is is the range of the element to move and rangeY the area between rangeX
	 * and the new position of rangeX.
	 * to avoid double shifting of already shifted data rangex first is shifted in area < 0
	 * (which is always empty), after correcting rangeY's left and rights we move it to its
	 * designated position.
	 *
	 * @param object $entity updated tree element
	 */
	protected function _updateNode($entity) {
		extract($this->_config);

		$span = $entity->$right - $entity->$left;
		$spanToZero = $entity->$right;

		$rangeX = array('floor' => $entity->$left, 'ceiling' => $entity->$right);
		$shiftY = $span + 1;

		if ($entity->$parent !== null) {
			$newParent = $this->_getById($entity->$parent);
			if ($newParent) {
				$boundary = $newParent->$right;
			} else {
				throw new UnexpectedValueException("The `{$parent}` with id `{$entity->$parent}` doesn't exists.");
			}
		} else {
			$boundary = $this->_getMax($entity) + 1;
		}
		$this->_updateBetween($rangeX, '-', $spanToZero, $this->_scope($entity));

		if ($entity->$right < $boundary) {
			$rangeY = array('floor' => $entity->$right + 1, 'ceiling' => $boundary - 1);
			$this->_updateBetween($rangeY, '-', $shiftY, $this->_scope($entity));
			$shiftX = $boundary - $entity->$right - 1;
		} else {
			$rangeY = array('floor' => $boundary, 'ceiling' => $entity->$left - 1);
			$this->_updateBetween($rangeY, '+', $shiftY, $this->_scope($entity));
			$shiftX = ($boundary - 1) - $entity->$left + 1;
		}
		$this->_updateBetween(array(
			'floor' => (0 - $span), 'ceiling' => 0
		), '+', $spanToZero + $shiftX, $this->_scope($entity));
		$entity->set(array($left => $entity->$left + $shiftX, $right => $entity->$right + $shiftX));
	}

	/**
	 * Update a node (when scope has changed)
	 *
	 * all the "move an element with all its children" magic happens here!
	 *
	 * @param object $entity Updated tree element
	 * @param array $oldScope Old scope data
	 * @param array $newScope New scope data
	 */
	protected function _updateScope($entity, $oldScope, $newScope) {
		extract($this->_config);

		$span = $entity->$right - $entity->$left;
		$spanToZero = $entity->$right;

		$rangeX = array('floor' => $entity->$left, 'ceiling' => $entity->$right);

		$this->_updateBetween($rangeX, '-', $spanToZero, $oldScope, $newScope);
		$this->_update($entity->$right, '-', $span + 1, $oldScope);

		$newParent = $this->_getById($entity->$parent);
		$r = $newParent->$right;
		$this->_update($r, '+', $span + 1, $newScope);
		$this->_updateBetween(array(
			'floor' => (0 - $span), 'ceiling' => 0
		), '+', $span + $r, $newScope);
		$entity->set(array($left => $r, $right => $span + $r));
	}

	/**
	 * Delete from tree
	 *
	 * deletes a node (and its children) from the tree
	 *
	 * @param object $entity updated tree element
	 */
	protected function _deleteFromTree($entity) {
		extract($this->_config);

		$span = 1;
		if ($entity->$right - $entity->$left !== 1) {
			$span = $entity->$right - $entity->$left;
			$model::remove(array($parent => $entity->data($model::key())));
		}
		$this->_update($entity->$right, '-', $span + 1, $this->_scope($entity));
		return true;
	}

	/**
	 * Get by id
	 *
	 * returns the element with given id
	 *
	 * @param integer $id the id to fetch from db
	 */
	protected function _getById($id) {
		$model = $this->_config['model'];
		return $model::find('first', array('conditions' => array($model::key() => $id)));
	}

	/**
	 * Update node indices
	 *
	 * Updates the Indices in greater than $rght with given value.
	 *
	 * @param integer $rght the right index border to start indexing
	 * @param string $dir Direction +/- (defaults to +)
	 * @param integer $span value to be added/subtracted (defaults to 2)
	 * @param array $scp The scope to apply updates on
	 */
	protected function _update($rght, $dir = '+', $span = 2, $scp = array()) {
		extract($this->_config);

		$model::update(array($right => (object) ($right . $dir . $span)), array(
			$right => array('>=' => $rght)
		) + $scp);

		$model::update(array($left => (object) ($left . $dir . $span)), array(
			$left => array('>' => $rght)
		) + $scp);
	}

	/**
	 * Update node indices between
	 *
	 * Updates the Indices in given range with given value.
	 *
	 * @param array $range the range to be updated
	 * @param string $dir Direction +/- (defaults to +)
	 * @param integer $span Value to be added/subtracted (defaults to 2)
	 * @param array $scp The scope to apply updates on
	 * @param array $data Additionnal scope datas (optionnal)
	 */
	protected function _updateBetween($range, $dir = '+', $span = 2, $scp = array(), $data = array()) {
		extract($this->_config);

		$model::update(array($right => (object) ($right . $dir . $span)), array(
			$right => array(
				'>=' => $range['floor'],
				'<=' => $range['ceiling']
		)) + $scp);

		$model::update(array($left => (object) ($left . $dir . $span)) + $data, array(
			$left => array(
				'>=' => $range['floor'],
				'<=' => $range['ceiling']
		)) + $scp);
	}

	/**
	 * Moves an element down in order
	 *
	 * @param object $entity The Entity to move down
	 */
	public function moveDown($entity) {
		extract($this->_config);
		$next = $model::find('first', array(
					'conditions' => array(
						$parent => $entity->$parent,
						$left => $entity->$right + 1
				)));

		if ($next !== null) {
			$spanToZero = $entity->$right;
			$rangeX = array('floor' => $entity->$left, 'ceiling' => $entity->$right);
			$shiftX = ($next->$right - $next->$left) + 1;
			$rangeY = array('floor' => $next->$left, 'ceiling' => $next->$right);
			$shiftY = ($entity->$right - $entity->$left) + 1;

			$this->_updateBetween($rangeX, '-', $spanToZero, $this->_scope($entity));
			$this->_updateBetween($rangeY, '-', $shiftY, $this->_scope($entity));
			$this->_updateBetween(array(
				'floor' => (0 - $shiftY), 'ceiling' => 0
			), '+', $spanToZero + $shiftX, $this->_scope($entity));

			$entity->set(array(
				$left => $entity->$left + $shiftX, $right => $entity->$right + $shiftX
			));
		}
		return true;
	}

	/**
	 * Moves an element up in order
	 *
	 * @param object $entity The Entity to move up
	 */
	public function moveUp($entity) {
		extract($this->_config);
		$prev = $model::find('first', array(
			'conditions' => array(
				$parent => $entity->$parent,
				$right => $entity->$left - 1
			)
		));
		if (!$prev) {
			return true;
		}
		$spanToZero = $entity->$right;
		$rangeX = array('floor' => $entity->$left, 'ceiling' => $entity->$right);
		$shiftX = ($prev->$right - $prev->$left) + 1;
		$rangeY = array('floor' => $prev->$left, 'ceiling' => $prev->$right);
		$shiftY = ($entity->$right - $entity->$left) + 1;

		$this->_updateBetween($rangeX, '-', $spanToZero, $this->_scope($entity));
		$this->_updateBetween($rangeY, '+', $shiftY, $this->_scope($entity));
		$this->_updateBetween(array(
			'floor' => (0 - $shiftY), 'ceiling' => 0
		), '+', $spanToZero - $shiftX, $this->_scope($entity));

		$entity->set(array(
			$left => $entity->$left - $shiftX, $right => $entity->$right - $shiftX
		));
		return true;
	}

	/**
	 * Get max
	 *
	 * @param object $entity An `Entity` object
	 * @return The highest 'right'
	 */
	protected function _getMax($entity) {
		extract($this->_config);

		$node = $model::find('first', array(
			'conditions' => $this->_scope($entity),
			'order' => array($right => 'desc')
		));
		if ($node) {
			return $node->$right;
		}
		return 0;
	}

	/**
	 * Returns the current position number of an element at the same level,
	 * where 0 is first position
	 *
	 * @param object $entity the entity node to get the position from.
	 * @param integer $childrenCount number of children of entity's parent,
	 *        performance parameter to avoid double select.
	 */
	protected function _getPosition($entity, $childrenCount = false) {
		extract($this->_config);

		$parent = $this->_getById($entity->$parent);

		if ($entity->$left === ($parent->$left + 1)) {
			return 0;
		}

		if (($entity->$right + 1) === $parent->$right) {
			if ($childrenCount === false) {
				$childrenCount = $parent->childrens(false, 'count');
			}
			return $childrenCount - 1;
		}

		$count = 0;
		$children = $parent->childrens(false);

		$id = $entity->data($model::key());
		foreach ($children as $child) {
			if ($child->data($model::key()) === $id) {
				return $count;
			}
			$count++;
		}

		return false;
	}

	/**
	 * Check if the current tree is valid.
	 *
	 * Returns true if the tree is valid otherwise an array of (type, incorrect left/right index,
	 * message)
	 *
	 * @param object $entity the entity node to get the position from.
	 * @return mixed true if the tree is valid or empty, otherwise an array of (error type [node,
	 *         boundary], [incorrect left/right boundary,node id], message)
	 */
	public function verify($entity) {
		extract($this->_config);

		$count = $model::find('count', array(
			'conditions' => array(
				$left => array('>' => $entity->$left),
				$right => array('<' => $entity->$right)
			) + $this->_scope($entity)
		));
		if (!$count) {
			return true;
		}
		$min = $entity->$left;
		$edge = $entity->$right;

		if ($entity->$left >= $entity->$right) {
			$id = $entity->data($model::key());
			$errors[] = array('root node', "`{$id}`", 'has left greater than right.');
		}

		$errors = array();

		for ($i = $min; $i <= $edge; $i++) {
			$count = $model::find('count', array(
					'conditions' => array(
						'or' => array($left => $i, $right => $i)
					) + $this->_scope($entity)
				));

			if ($count !== 1) {
				if ($count === 0) {
					$errors[] = array('node boundary', "`{$i}`", 'missing');
				} else {
					$errors[] = array('node boundary', "`{$i}`", 'duplicate');
				}
			}
		}

		$node = $model::find('first', array(
			'conditions' => array(
				$right => array('<' => $left)
			) + $this->_scope($entity)
		));

		if ($node) {
			$id = $node->data($model::key());
			$errors[] = array('node id', "`{$id}`", 'has left greater or equal to right.');
		}

		$model::bind('belongsTo', 'Verify', array(
			'to' => $model,
			'key' => $parent
		));

		$results = $model::find('all', array(
			'conditions' => $this->_scope($entity),
			'with' => array('Verify')
		));

		$id = $model::key();
		foreach ($results as $key => $instance) {
			if (is_null($instance->$left) || is_null($instance->$right)) {
				$errors[] = array('node', $instance->$id,
					'has invalid left or right values');
			} elseif ($instance->$left === $instance->$right) {
				$errors[] = array('node', $instance->$id,
					'left and right values identical');
			} elseif ($instance->$parent) {
				if (!isset($instance->verify->$id) || !$instance->verify->$id) {
					$errors[] = array('node', $instance->$id,
						'The parent node ' . $instance->$parent . ' doesn\'t exist');
				} elseif ($instance->$left < $instance->verify->$left) {
					$errors[] = array('node', $instance->$id,
						'left less than parent (node ' . $instance->verify->$id . ').');
				} elseif ($instance->$right > $instance->verify->$right) {
					$errors[] = array('node', $instance->$id,
						'right greater than parent (node ' . $instance->verify->$id . ').');
				}
			} elseif ($model::find('count', array(
					'conditions' => array(
					$left => array('<' => $instance->$left),
					$right => array('>' => $instance->$right)
					) + $this->_scope($entity)
				))) {
				$errors[] = array('node', $instance->$id, 'the parent field is blank, but has a parent');
			}
		}

		if ($errors) {
			return $errors;
		}
		return true;
	}

}

?>