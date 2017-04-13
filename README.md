# SuperGroupedList #
[![Build Status](https://travis-ci.org/bigfork/supergroupedlist.png?branch=master)](https://travis-ci.org/bigfork/supergroupedlist) [![Latest Stable Version](https://poser.pugx.org/bigfork/supergroupedlist/v/stable.png)](https://packagist.org/packages/bigfork/supergroupedlist) [![Total Downloads](https://poser.pugx.org/bigfork/supergroupedlist/downloads.png)](https://packagist.org/packages/bigfork/supergroupedlist) [![License](https://poser.pugx.org/bigfork/supergroupedlist/license.png)](https://packagist.org/packages/bigfork/supergroupedlist)

An extension of SilverStripe’s GroupedList that supports traversing relations.

**Note:** depending on your data, the same items may be output multiple times. For example if a product belongs to multiple categories, and you group by category title, then the product will show under each of the categories that it belongs to.

### Installation ###

```
composer require bigfork/supergroupedlist ^1.0
```

Or download and extract to a folder named `supergroupedlist` in your document root.

### Usage ###

Use exactly as you would use `GroupedList`, but with dot-notation to traverse relations:

```php
public function GroupedProducts() {
	$products = Product::get();
	return SuperGroupedList::create($products);
}
```

```html
<% loop $GroupedProducts.GroupedBy('Categories.Title') %>
	<h1>{$Title}</h1><!-- Category title -->
	<ul>
		<% loop $Children %>
			<li>{$Title}</li><!-- Product title -->
		<% end_loop %>
	</ul>
<% end_loop %>
```

You can traverse `has_one`, `has_many` and `many_many` relations using dot notation. The last part of the notation you provide (`Title` in the example above) will be both the field that’s extracted from the final component, and the `$Variable` used to access that field inside the loop.

You can even traverse multiple relations at once. For example, `$GroupedProducts.GroupedBy('Manufacturer.Employees.FavouriteTeam.Name')` would return a list of products grouped by the names of the favourite teams of the employees of the product’s manufacturer.
