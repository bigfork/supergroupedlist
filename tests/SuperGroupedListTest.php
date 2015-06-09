<?php

class SuperGroupedListTest extends SapphireTest {

	protected static $fixture_file = 'SuperGroupedListTest.yml';

	protected $extraDataObjects = array(
		'SuperGroupedListTest_Company',
		'SuperGroupedListTest_Person',
		'SuperGroupedListTest_Category',
		'SuperGroupedListTest_Product'
	);

	/**
	 * Test groupBy with has_one relation
	 */
	public function testGroupByHasOne() {
		$items = SuperGroupedListTest_Person::get();
		$list = new SuperGroupedList($items);
		$grouped = $list->groupBy('Employer.Name');

		$this->assertEquals(2, count($grouped), 'Incorrect number of employers found');
		$this->assertArrayHasKey('Bigfork Ltd', $grouped, 'Expected employer not found');
		$this->assertEquals(2, count($grouped['Bigfork Ltd']), 'Incorrect number of employees for employer');
		$this->assertArrayHasKey('Littlespork Ltd', $grouped, 'Expected employer not found');
		$this->assertEquals(1, count($grouped['Littlespork Ltd']), 'Incorrect number of employees for employer');
	}

	/**
	 * Test GroupedBy with has_one relation
	 */
	public function testGroupedByHasOne() {
		$items = SuperGroupedListTest_Person::get();
		$list = new SuperGroupedList($items);
		$grouped = $list->GroupedBy('Employer.Name');

		$this->assertEquals('Bigfork Ltd', $grouped->First()->Name, 'Expected employer not found');
		$this->assertEquals('Littlespork Ltd', $grouped->Last()->Name, 'Expected employer not found');
		$this->assertEquals(2, $grouped->First()->Children->Count(), 'Incorrect number of employees for employer');
		$this->assertEquals(1, $grouped->Last()->Children->Count(), 'Incorrect number of employees for employer');
	}

	/**
	 * Test groupBy with has_many relations
	 */
	public function testGroupByHasMany() {
		$items = SuperGroupedListTest_Company::get();
		$list = new SuperGroupedList($items);
		$grouped = $list->groupBy('Employees.Age');

		$this->assertEquals(2, count($grouped), 'Incorrect number of employees found');
		$this->assertArrayHasKey('40', $grouped, 'Unable to find expected age group');
		$this->assertEquals(1, count($grouped['40']), 'Incorrect number of companies with employees of given age');
		$this->assertArrayHasKey('50', $grouped, 'Unable to find expected age group');
		$this->assertEquals(1, count($grouped['50']), 'Incorrect number of companies with employees of given age');
	}

	/**
	 * Test GroupedBy with has_many relation
	 */
	public function testGroupedByHasMany() {
		$items = SuperGroupedListTest_Company::get();
		$list = new SuperGroupedList($items);
		$grouped = $list->GroupedBy('Employees.Age');

		$this->assertEquals('40', $grouped->First()->Age, 'Expected age not found');
		$this->assertEquals('50', $grouped->Last()->Age, 'Expected age not found');
		$this->assertEquals(1, $grouped->First()->Children->Count(),
			'Incorrect number of companies with employees of given age');
		$this->assertEquals(1, $grouped->Last()->Children->Count(),
			'Incorrect number of companies with employees of given age');
	}

	/**
	 * Test groupBy with many_many relations
	 */
	public function testGroupByManyMany() {
		$items = SuperGroupedListTest_Product::get();
		$list = new SuperGroupedList($items);
		$grouped = $list->groupBy('Categories.Name');

		$this->assertEquals(3, count($grouped), 'Incorrect number of categories found');
		$this->assertArrayHasKey('Home Entertainment', $grouped, 'Expected product category not found');
		$this->assertEquals(2, count($grouped['Home Entertainment']), 'Incorrect number of products in category');
		$this->assertArrayHasKey('Electricals', $grouped, 'Expected product category not found');
		$this->assertEquals(3, count($grouped['Electricals']), 'Incorrect number of products in category');
		$this->assertArrayHasKey('Kitchen', $grouped, 'Expected product category not found');
		$this->assertEquals(1, count($grouped['Kitchen']), 'Incorrect number of products in category');
	}

	/**
	 * Test GroupedBy with many_many relation
	 */
	public function testGroupedByManyMany() {
		$items = SuperGroupedListTest_Product::get();
		$list = new SuperGroupedList($items);
		$grouped = $list->GroupedBy('Categories.Name');

		$this->assertEquals('Home Entertainment', $grouped->First()->Name, 'Expected category not found');
		$this->assertEquals('Electricals', $grouped->offsetGet(1)->Name, 'Expected category not found');
		$this->assertEquals('Kitchen', $grouped->Last()->Name, 'Expected category not found');
		$this->assertEquals(2, $grouped->First()->Children->Count(), 'Incorrect number of products in category');
		$this->assertEquals(3, $grouped->offsetGet(1)->Children->Count(), 'Incorrect number of products in category');
		$this->assertEquals(1, $grouped->Last()->Children->Count(), 'Incorrect number of products in category');
	}

	/**
	 * Test groupBy when traversing multiple relations
	 */
	public function testGroupByRelationTraversal() {
		$items = SuperGroupedListTest_Category::get();
		$list = new SuperGroupedList($items);
		$grouped = $list->groupBy('Products.Manufacturer.Name');

		$this->assertEquals(2, count($grouped), 'Incorrect number of manufacturers found');
		$this->assertArrayHasKey('Bigfork Ltd', $grouped, 'Expected manufacturer not found');
		$this->assertEquals(2, count($grouped['Bigfork Ltd']), 'Incorrect number of categories for manufacturer');
		$this->assertArrayHasKey('Littlespork Ltd', $grouped, 'Expected manufacturer not found');
		$this->assertEquals(2, count($grouped['Littlespork Ltd']), 'Incorrect number of categories for manufacturer');
	}

	/**
	 * Test GroupedBy when traversing multiple relations
	 */
	public function testGroupedByRelationTraversal() {
		$items = SuperGroupedListTest_Category::get();
		$list = new SuperGroupedList($items);
		$grouped = $list->GroupedBy('Products.Manufacturer.Name');

		$this->assertEquals('Bigfork Ltd', $grouped->First()->Name, 'Expected manufacturer not found');
		$this->assertEquals('Littlespork Ltd', $grouped->Last()->Name, 'Expected manufacturer not found');
		$this->assertEquals(2, $grouped->First()->Children->Count(), 'Incorrect number of categories for manufacturer');
		$this->assertEquals(2, $grouped->Last()->Children->Count(), 'Incorrect number of categories for manufacturer');
	}

}

class SuperGroupedListTest_Company extends DataObject implements TestOnly {
	private static $db = array(
		'Name' => 'Varchar'
	);

	private static $has_many = array(
		'Employees' => 'SuperGroupedListTest_Person',
		'Products' => 'SuperGroupedListTest_Product'
	);
}

class SuperGroupedListTest_Person extends DataObject implements TestOnly {
	private static $db = array(
		'Name' => 'Varchar',
		'Age' => 'Int'
	);

	private static $has_one = array(
		'Employer' => 'SuperGroupedListTest_Company'
	);
}

class SuperGroupedListTest_Category extends DataObject implements TestOnly {
	private static $db = array(
		'Name' => 'Varchar'
	);

	private static $many_many = array(
		'Products' => 'SuperGroupedListTest_Product'
	);
}

class SuperGroupedListTest_Product extends DataObject implements TestOnly {
	private static $db = array(
		'Name' => 'Varchar',
		'Price' => 'Int'
	);

	private static $has_one = array(
		'Manufacturer' => 'SuperGroupedListTest_Company'
	);

	private static $belongs_many_many = array(
		'Categories' => 'SuperGroupedListTest_Category'
	);
}
