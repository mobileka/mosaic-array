Mosaic Array
=============

A simple array manipulation class.

## Requirements:
PHP >= 5.4.*

## Some examples:

A very common case when you need to do something like this:

```php
if (isset($arr['key']) {
	$result = $arr['key'];
} else {
	$result = 'default';
}

// another way to write the same thing
$result = isset($arr['key']) ? $arr['key'] : 'default';
```

With `MosaicArray` you can do the same thing more elegantly:

```php
$result = MosaicArray::make($arr)->getItem('key', 'default');
//or
$ma = new MosaicArray($arr);
$result = $ma->getItem('key', 'default');
```
`MosaicArray` implements `ArrayAccess`, `IteratorAggregate`, `Countable` and `Serializable` interfaces, so you can access an instance of this class as an array, iterate over it, count elements, serialize and unserialize it:

```php
$numbers = new MosaicArray([1, 2, 3]);

echo $numbers[0]; //1

foreach ($numbers as $number) {
	// do something
}

echo count($numbers); // 3

serialize($numbers);
unserialize($numbers);
```

## License

MosaicArray is open-source and licensed under the [MIT License](https://github.com/mobileka/mosaiq-array/blob/master/license)

