<?php

class SuperGroupedList extends GroupedList {

	/**
	 * The current item we're trying to extract data from. Easier to store it here
	 * than pass it down through recursive method calls.
	 * @var mixed
	 */
	protected $currentItem = null;

	/**
	 * The list of grouped items we've managed to extract. Easier to store it here
	 * than pass it down and back up through recursive method calls.
	 * @var array
	 */
	protected $extractedParts = array();

	/**
	 * @param string $index
	 * @param string $children
	 * @return ArrayList
	 */
	public function GroupedBy($index, $children = 'Children') {
		if(strpos($index, '.') === false) {
			return parent::GroupedBy($index, $children);
		}

		$grouped = $this->groupBy($index);
		$result = new ArrayList();

		// Extract the key name from the relations
		$relations = explode('.', $index);
		$key = array_pop($relations);

		foreach($grouped as $indVal => $list) {
			$list = self::create($list);
			$result->push(new ArrayData(array(
				$key => $indVal,
				$children => $list
			)));
		}

		return $result;
	}

	/**
	 * @param string $index
	 * @return array
	 */
	public function groupBy($index) {
		if(strpos($index, '.') === false) {
			return parent::groupBy($index);
		}

		$list = $this->getList();
		if( ! $list instanceof DataList) {
			throw new Exception("I don't know how to traverse relations on instances of " . get_class($list) . " :(");
		}

		$relations = explode('.', $index);
		foreach($list as $item) {
			$this->currentItem = $item;
			$this->extract($item, $relations);
		}

		return $this->extractedParts;
	}

	/**
	 * Recursively try to find the index for the current item, and store the current
	 * item against that index.
	 * @param mixed $item
	 * @param array $relationParts
	 */
	protected function extract($item, $relationParts) {
		$part = array_shift($relationParts);

		if($item->has_one($part)) {
			$this->extract($item->getComponent($part), $relationParts);
		} elseif($item->has_many($part)) {
			$components = $item->getComponents($part);
			foreach($components as $component) {
				$this->extract($component, $relationParts);
			}
		} elseif($item->many_many($part)) {
			$components = $item->getManyManyComponents($part);
			foreach($components as $component) {
				$this->extract($component, $relationParts);
			}
		} else {
			$key = $item->hasMethod($part) ? $item->$part() : $item->$part;

			if(array_key_exists($key, $this->extractedParts)) {
				$this->extractedParts[$key]->push($this->currentItem);
			} else {
				$this->extractedParts[$key] = new ArrayList(array($this->currentItem));
			}
		}
	}

}
