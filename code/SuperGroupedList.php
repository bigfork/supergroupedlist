<?php

class SuperGroupedList extends GroupedList
{

    /**
     * @param string $index
     * @param string $childrenKey
     * @return ArrayList
     */
    public function GroupedBy($index, $childrenKey = 'Children')
    {
        if (strpos($index, '.') === false) {
            return parent::GroupedBy($index, $childrenKey);
        }

        $grouped = $this->groupBy($index);
        $result = new ArrayList();

        // Extract the key name from the relations
        $relations = explode('.', $index);
        $key = array_pop($relations);

        foreach ($grouped as $indexValue => $list) {
            $list = self::create($list);
            $data = new ArrayData(array($key => $indexValue, $childrenKey => $list));
            $result->push($data);
        }

        return $result;
    }

    /**
     * @param string $index
     * @return array
     */
    public function groupBy($index)
    {
        if (strpos($index, '.') === false) {
            return parent::groupBy($index);
        }

        $list = $this->getList();
        if (! $list instanceof DataList) {
            throw new Exception("I don't know how to traverse relations on instances of " . get_class($list) . " :(");
        }

        $result = array();
        $relations = explode('.', $index);
        foreach ($list as $item) {
            $this->extractInto($result, $item, $item, $relations);
        }

        return $result;
    }

    /**
     * Recursively try to find the index for the current item, and store the current
     * item against that index.
     * @param array &$result The array of results
     * @param mixed $originalItem The original item we're trying to group
     * @param mixed $currentItem The current item we're traversing
     * @param array $relationParts Array containing relation/field names to be traversed
     */
    protected function extractInto(array &$result, $originalItem, $currentItem, array $relationParts)
    {
        $part = array_shift($relationParts);

        if ($currentItem->has_one($part)) {
            // If this is a has_one relation, we can just jump straight to it
            $this->extractInto($result, $originalItem, $currentItem->getComponent($part), $relationParts);
        } elseif ($currentItem->has_many($part)) {
            // For a has_many we need to iterate over each component and extract relations from it
            $components = $currentItem->getComponents($part);
            foreach ($components as $component) {
                $this->extractInto($result, $originalItem, $component, $relationParts);
            }
        } elseif ($currentItem->many_many($part)) {
            // For a many_many we need to iterate over each component and extract relations from it
            $components = $currentItem->getManyManyComponents($part);
            foreach ($components as $component) {
                $this->extractInto($result, $originalItem, $component, $relationParts);
            }
        } else {
            // If this isn't a relation, it must be a field, so extract the data from it
            $key = $currentItem->hasMethod($part) ? $currentItem->$part() : $currentItem->$part;

            if (array_key_exists($key, $result)) {
                if (! $result[$key]->find('ID', $originalItem->ID)) {
                    $result[$key]->push($originalItem);
                }
            } else {
                $result[$key] = new ArrayList(array($originalItem));
            }
        }
    }
}
