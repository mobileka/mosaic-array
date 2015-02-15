<?php

use Mobileka\MosaicArray\MosaicArray;

/**
 * @covers Mobileka\MosaicArray\MosaicArray
 */
class MosaicArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $target;

    public function setUp()
    {
        $this->target = [
            'numbers' => range(1, 5),
            'students' => ['Armen Markossyan', 'Vadim Vorotilov']
        ];
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::__construct
     */
    public function test_is_instantiable()
    {
        $ma = new MosaicArray([]);
        assertTrue(method_exists($ma, 'getItem'));
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::make
     */
    public function test_is_makeable()
    {
        $ma = MosaicArray::make([]);
        assertTrue(method_exists($ma, 'getItem'));
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::toArray
     */
    public function test_returns_target_array()
    {
        $ma = new MosaicArray($this->target);

        // full target array
        $expect = $this->target;
        $result = $ma->toArray();
        assertEquals($expect, $result);

        // only numbers
        $expect = range(1, 5);
        $result = $ma->toArray('numbers');
        assertEquals($expect, $result);

        // negative result
        $expect = ['blah'];
        $result = $ma->toArray();
        assertNotEquals($expect, $result);
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::getItem
     */
    public function test_gets_item_if_exists_or_returns_default_result()
    {
        $ma = new MosaicArray($this->target);

        // numbers
        $expect = range(1, 5);
        $result = $ma->getItem('numbers');
        assertEquals($expect, $result);

        // no key
        $expect = null;
        $result = $ma->getItem('no such key');
        assertEquals($expect, $result);

        // custom result
        $expect = 'custom result';
        $result = $ma->getItem('no such key', 'custom result');
        assertEquals($expect, $result);

        // negative condition
        $expect = range(1, 5);
        $result = $ma->getItem('no such key', false);
        assertNotEquals($expect, $result);
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::hasIntersections
     */
    public function test_detects_intersections()
    {
        $ma = new MosaicArray($this->target);

        // strict = false
        $numbers = ['numbers' => [1, 2, '3', 4, 5]];
        assertTrue($ma->hasIntersections($numbers));

        $numbers = ['numbers' => ['numbers' => range(1, 4)]];
        assertFalse($ma->hasIntersections($numbers));

        assertTrue($ma->hasIntersections($this->target));

        $expect = range(1, 5);
        $result = $ma->hasIntersections(['numbers' => range(1, 5)], false, true);
        assertEquals($expect, $result);

        // strict = true
        $numbers = ['numbers' => [1, 2, '3', 4, 5]];
        assertFalse($ma->hasIntersections($numbers, true));
        assertFalse($ma->hasIntersections($numbers, true, true));

        $numbers = ['numbers' => range(1, 5)];
        assertTrue($ma->hasIntersections($numbers, true));

        $expect = range(1, 5);
        $result = $ma->hasIntersections(['numbers' => range(1, 5)], true, true);
        assertEquals($expect, $result);
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::find
     */
    public function test_finds_first_truthy_element()
    {
        $badArray = [0, false, '', null];
        $result = MosaicArray::make($badArray)->find();
        assertNull($result);

        $result = MosaicArray::make($badArray)->find(false);
        assertFalse($result);

        $expect = 1;
        $result = MosaicArray::make([0, 1])->find();
        assertEquals($expect, $result);

        $expect = 'Mosaic Soft';
        $result = MosaicArray::make([null, false, 'Mosaic Soft'])->find();
        assertEquals($expect, $result);

        $badNestedArray = [null, false, 'Mosaic Soft' => []];
        $result = MosaicArray::make($badNestedArray)->find();
        assertNull($result);

        $goodNestedArray = [null, false, 'Mosaic Soft' => ['test' => ['hoo!']]];
        $expect = ['test' => ['hoo!']];
        $result = MosaicArray::make($goodNestedArray)->find();
        assertEquals($expect, $result);
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::pregKeys
     */
    public function test_gets_an_array_of_target_arrays_keys_matching_a_regex()
    {
        $ma = new MosaicArray($this->target);

        // should not be found
        $result = $ma->pregKeys('/^\d{5}$/');
        assertNull($result);

        $expect = 'Mosaic Soft';
        $result = $ma->pregKeys('/^\d{5}$/', 'Mosaic Soft');
        assertEquals($expect, $result);

        // keys ending with "s"
        $expect = ['numbers', 'students'];
        $result = $ma->pregKeys('/^.*s$/');
        assertEquals($expect, $result);

        // containing two numbers in a row
        $target = ['5wrong' => '', 'cor55rect' => '', 'corre76ct' => '', 'hello' => 'world'];
        $expect = ['cor55rect', 'corre76ct'];
        $result = MosaicArray::make($target)->pregKeys('/^.*\d{2}.*$/');
        assertEquals($expect, $result);
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::pregValues
     */
    public function test_gets_an_array_of_target_arrays_values_matching_a_regex()
    {
        $target = ['key' => 'VALUE', 'hello' => 'World', 'Mosaic' => 'Soft'];
        $ma = new MosaicArray($target);

        // should not be found
        $result = $ma->pregValues('/^\d{5}$/');
        assertNull($result);

        $expect = 'Mosaic Soft';
        $result = $ma->pregValues('/^\d{5}$/', 'Mosaic Soft');
        assertEquals($expect, $result);

        $expect = ['World', 'Soft'];
        $result = $ma->pregValues('/^.*o.*$/');
        assertEquals($expect, $result);

        $expect = ['VALUE'];
        $result = $ma->pregValues('/^.*ALU.*$/');
        assertEquals($expect, $result);
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::except
     */
    public function test_excludes_keys_from_target()
    {
        $ma = new MosaicArray($this->target);

        $expect = ['numbers' => range(1, 5)];
        $result = $ma->except(['students']);
        assertEquals($expect, $result);

        $expect = $this->target;
        $result = $ma->except(['Mosaic']);
        assertEquals($expect, $result);

        $expect = $this->target;
        $result = $ma->except([]);
        assertEquals($expect, $result);
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::only
     */
    public function test_gets_only_specified_keys_from_target()
    {
        $ma = new MosaicArray($this->target);

        $expect = ['numbers' => range(1, 5)];
        $result = $ma->only(['numbers']);
        assertEquals($expect, $result);

        $expect = [];
        $result = $ma->only(['Mosaic']);
        assertEquals($expect, $result);

        $expect = [];
        $result = $ma->only([]);
        assertEquals($expect, $result);
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::sortByArrayKeys
     */
    public function test_sorts_target_by_array_keys()
    {
        $fixture = ['second' => '2', 'first' => '1', 'last'];
        $ma = new MosaicArray($fixture);
        $sortBy = [0, 'second', 'first'];

        $expect = ['last', 'second' => '2', 'first' => '1'];
        $result = $ma->sortByArrayKeys($sortBy);
        assertSame($expect, $result);

        $expect = $fixture;
        $result = $ma->sortByArrayKeys([]);
        assertSame($expect, $result);
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::sortByArrayValues
     */
    public function test_sorts_target_by_array_values()
    {
        $fixture = ['second' => '2', 'first' => '1', 'last'];
        $ma = new MosaicArray($fixture);
        $sortBy = ['last', '2', '1'];

        $expect = ['last', 'second' => '2', 'first' => '1'];
        $result = $ma->sortByArrayValues($sortBy);
        assertSame($expect, $result);

        $expect = $fixture;
        $result = $ma->sortByArrayValues([]);
        assertSame($expect, $result);
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::excludeByRule
     */
    public function test_excludes_target_elements_by_rule()
    {
        $fixture = ['key' => 'value', 1, 2, 3, 'numbers' => [1, 2, 3]];
        $ma = new MosaicArray($fixture);

        $expect = [1, 2, 3, 'numbers' => [1, 2, 3]];
        $result = $ma->excludeByRule(function ($key, $value) { return $key === 'key'; });
        assertSame($expect, $result);

        $expect = $fixture;
        $result = $ma->excludeByRule(function ($key, $value) { return $value == 'nothing will be excluded'; });
        assertSame($expect, $result);

        // exclude all numeric values
        $expect = ['key' => 'value', 'numbers' => [1, 2, 3]];
        $result = $ma->excludeByRule(function ($key, $value) { return is_numeric($value); });
        assertSame($expect, $result);

        // exclude all arrays
        $expect = ['key' => 'value', 1, 2, 3];
        $result = $ma->excludeByRule(function ($key, $value) { return is_array($value); });
        assertSame($expect, $result);

        // exclude all non-numeric keys
        $expect = [1, 2, 3];
        $result = $ma->excludeByRule(function ($key, $value) { return !is_numeric($key); });
        assertSame($expect, $result);
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::replaceTarget()
     */
    public function test_replaces_target_array()
    {
        $ma = new MosaicArray($this->target);
        $expect = ['some value'];

        $result = $ma->replaceTarget($expect)->toArray();

        assertEquals($expect, $result);
        assertNotEquals($this->target, $result);
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::getIterator
     * @covers Mobileka\MosaicArray\MosaicArray::serialize
     * @covers Mobileka\MosaicArray\MosaicArray::unserialize
     */
    public function test_implements_serializable_interface()
    {
        $ma = new MosaicArray([1, 2, 3]);
        $clone = clone $ma;

        $ma = serialize($ma);
        $ma = unserialize($ma);

        assertEquals($clone, $ma);

    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::count
     */
    public function test_implements_countable_interface()
    {
        $ma = new MosaicArray([1, 2, 3]);

        $count = count($ma);

        assertEquals(3, $count);
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::offsetExists
     * @covers Mobileka\MosaicArray\MosaicArray::offsetSet
     * @covers Mobileka\MosaicArray\MosaicArray::offsetUnset
     * @covers Mobileka\MosaicArray\MosaicArray::offsetGet
     */
    public function test_implements_array_access_interface()
    {
        $ma = new MosaicArray([1, 2, 3]);

        assertEquals(2, $ma[1]);

        $ma[] = 4;
        assertEquals(4, $ma[3]);

        $ma[4] = 5;
        assertTrue(isset($ma[4]));
    }

    /**
     * @covers Mobileka\MosaicArray\MosaicArray::offsetSet
     * @covers Mobileka\MosaicArray\MosaicArray::offsetUnset
     * @covers Mobileka\MosaicArray\MosaicArray::offsetGet
     */
    public function test_implements_iterator_aggregate_interface()
    {
        $ma = new MosaicArray([1, 2, 3]);
        $i = 0;

        foreach ($ma as $value) {
            assertEquals($ma[$i], $value);
            $i++;
        }

        unset($ma[0]);
        assertEquals(2, count($ma));
    }
}
