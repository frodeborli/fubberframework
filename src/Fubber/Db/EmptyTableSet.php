<?php
namespace Fubber\Db;

/**
* Useful method for returning an empty result set, for example if filtering will remove all items.
*
* <code>
* <?php
* return new EmptyTableSet('User'); // User class must extend the Table class
* ?>
* </code>
*/
class EmptyTableSet extends TableSet {

	/**
	*	@see TableSet::count()
	*/
	public function count() {
		return 0;
	}

	/**
	*	@see TableSet::one()
	*/
	public function one() {
		return NULL;
	}

	public function getIterator() {
		return new \EmptyIterator();
	}
}
