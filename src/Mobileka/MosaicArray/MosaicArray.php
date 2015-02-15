<?php namespace Mobileka\MosaicArray;

use Closure;

/**
 * A simple array manipulation class
 *
 * @author Armen Markossyan <a.a.markossyan@gmail.com>
 * @version 1.0
 */
class MosaicArray implements \ArrayAccess, \IteratorAggregate, \Serializable, \Countable
{
    /**
    * A target array which acts as a source
    *
    * @var array
    */
    protected $target = [];

    /**
     * Static construct
     *
     * @param  array              $array
     * @return Current_Class_Name
     */
    public static function make(array $array)
    {
        return new static($array);
    }

    /**
     * Construct
     *
     * @param array $array
     */
    public function __construct(array $array)
    {
        $this->target = $array;
    }

    /**
     * Replace target array with a new one
     *
     * @param  array              $target new target
     * @return Current_Class_Name
     */
    public function replaceTarget(array $target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Return the target array
     *
     * @param  string $key is used for getting a part of the target array
     * @return mixed
     */
    public function toArray($key = null)
    {
        return $key ? $this->target[$key] : $this->target;
    }

    /**
     * Get a value from an array by key in case when it exsists.
     * Otherwise, return the $defaultResult
     *
     * @param  string $key
     * @param  mixed  $defaultResult
     * @return mixed
     */
    public function getItem($key, $defaultResult = null)
    {
        return isset($this->target[$key]) ? $this->target[$key] : $defaultResult;
    }

    /**
     * Determine if two arrays have at least a single matching element
     *
     * @param  array $array
     * @param  bool  $strict
     * @param  bool  $returnIntersection
     * @return mixed
     */
    public function hasIntersections(array $array, $strict = false, $returnIntersection = false)
    {
        foreach ($this->target as $element) {
            if (in_array($element, $array, $strict)) {
                return $returnIntersection ? $element : true;
            }
        }

        return false;
    }

    /**
     * Return the first truthy element of the target array.
     * If none of them is truthy, return a defaultResult.
     *
     * @param  mixed $defaultResult
     * @return mixed
     */
    public function find($defaultResult = null)
    {
        foreach ($this->target as $value) {
            if ($value) {
                return $value;
            }
        }

        return $defaultResult;
    }

    /**
     * Get an array of target array's keys matching a regular expression
     * If nothing found, return a defaultResult
     *
     * @param  string $pattern
     * @param  mixed  $defaultResult
     * @param  mixed  $flags
     * @return mixed
     */
    public function pregKeys($pattern, $defaultResult = null, $flags = 0)
    {
        $result = preg_grep($pattern, array_keys($this->target), $flags);

        return $result ? array_values($result) : $defaultResult;
    }

    /**
     * Get values from an array by a key matching a regular expression
     * If nothing found, return a defaultResult
     *
     * @param  string $pattern
     * @param  mixed  $defaultResult
     * @param  mixed  $flags
     * @return mixed
     */
    public function pregValues($pattern, $defaultResult = null, $flags = 0)
    {
        $result = preg_grep($pattern, array_values($this->target), $flags);

        return $result ? array_values($result) : $defaultResult;
    }

    /**
     * Exclude specified keys form a given array
     *
     * @param  array $keys
     * @return array
     */
    public function except(array $keys)
    {
        return $keys ? array_diff_key($this->target, array_flip($keys)) : $this->target;
    }

    /**
     * Get a subset of array's keys
     *
     * @param  array $keys
     * @return array
     */
    public function only(array $keys)
    {
        return array_intersect_key($this->target, array_flip($keys));
    }

    /**
     * Sort a target array by another array
     *
     * @param  array $orderBy - values of this array will be considered keys of the target array
     * @return array
     */
    public function sortByArrayKeys(array $orderBy)
    {
        $result = $this->target;

        if ($orderBy) {
            $ordered = [];

            foreach ($orderBy as $key) {
                if (array_key_exists($key, $result)) {
                    $ordered[$key] = $result[$key];
                    unset($result[$key]);
                }
            }

            $result = $ordered + $result;
        }

        return $result;
    }

    /**
     * Sort a target array by another array
     *
     * @param  array $orderBy - values of this array will be considered values of the target array
     * @return array
     */
    public function sortByArrayValues(array $orderBy)
    {
        if (!$orderBy) {
            return $this->target;
        }

        $result = [];

        foreach ($orderBy as $value) {
            if ($keys = array_keys($this->target, $value)) {
                foreach ($keys as $key) {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Exclude an item from an array if its value meets a rule
     *
     * @param  \Closure $rule
     * @return array
     */
    public function excludeByRule(Closure $rule)
    {
        $result = $this->target;

        foreach ($result as $key => $value) {
            if ($rule($key, $value)) {
                unset($result[$key]);
            }
        }

        return $result;
    }

    /**
     * Implementation of the getIterator method of the IteratorAggregate interface
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->toArray());
    }

    /**
     * Implementation of the offsetSet method of the ArrayAccess interface
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->target[] = $value;
        } else {
            $this->target[$offset] = $value;
        }
    }

    /**
     * Implementation of the offsetExists method of the ArrayAccess interface
     *
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->target[$offset]);
    }

    /**
     * Implementation of the offsetUnset method of the ArrayAccess interface
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->target[$offset]);
    }

    /**
     * Implementation of the offsetGet method of the ArrayAccess interface
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->target[$offset]) ? $this->target[$offset] : null;
    }

    /**
     * Implementation of the serialize method of the Serializable interface
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->target);
    }

    /**
     * Implementation of the unserialize method of the Serializable interface
     *
     * @param  string $data
     * @return void
     */
    public function unserialize($data)
    {
        $this->target = unserialize($data);
    }

    /**
     * Implementation of the count method of the Countable interface
     *
     * @return int
     */
    public function count()
    {
        return count($this->target);
    }
}
