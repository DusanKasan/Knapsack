# Knapsack
**Collection pipeline library for PHP**

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5fcb3dc2-2061-4da3-853b-a5e2a35a35fb/mini.png)](https://insight.sensiolabs.com/projects/5fcb3dc2-2061-4da3-853b-a5e2a35a35fb) [![Code Coverage](https://scrutinizer-ci.com/g/DusanKasan/Knapsack/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/DusanKasan/Knapsack/?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/DusanKasan/Knapsack/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/DusanKasan/Knapsack/?branch=master)

Knapsack is a [collection pipeline](http://martinfowler.com/articles/collection-pipeline/) library implementing most of the sequence operations proposed by [Clojures sequences](http://clojure.org/sequences)

The heart of Knapsack is its [Collection class](https://github.com/DusanKasan/Knapsack/blob/master/src/Knapsack/Collection.php). It is an iterator implementor that accepts Traversable object or array as constructor argument. It provides most of Clojures sequence function plus some extra ones. It is also immutable - operations preformed on the collection will return new collection (or value) instead of modifying the original collection.
 
Most of the methods of Collection return lazy collections (such as filter/map/etc.). However, some return non-lazy collections (reverse) or simple values (count). For these operations all of the items in the collection must be iterated over (and realized). There are also operations (drop) that iterate over some items of the collection but do not affect/return them in the result. This behaviour as well as laziness is noted for each of the operations.  

If you want more example usage beyond what is provided here, check the [specs](https://github.com/DusanKasan/Knapsack/tree/master/tests/spec/Knapsack) and/or [scenarios](https://github.com/DusanKasan/Knapsack/tree/master/tests/scenarios) 

Feel free to report any [issues](https://github.com/DusanKasan/Knapsack/issues) you find. I will do my best to fix them as soon as possible, but community [pull requests](https://github.com/DusanKasan/Knapsack/pulls) to fix them are more than welcome.

## Documentation
Check out the documentation (which is prettified version of this readme) at http://dusankasan.github.io/Knapsack

## Usage
### Work with arrays or Traversable objects
```php
$collection1 = new Collection([1,2,3]);
$collection2 = new Collection(new ArrayIterator([1,2,3]);
```

### Basic map/reduce
```php
$collection = new Collection([1,2]);
$result = $collection
    ->map(function($v) {return $v*2;})
    ->reduce(0, function($tmp, $v) {return $tmp+$v;});
    
echo $result; //6
```

### Get first 5 items of Fibonacci's sequence
```php
$collection = new Collection([[1,1]]);
$result = $collection
    ->iterate(function($v) {
        return [$v[1], $v[0] + $v[1]]; //[1, 2], [2, 3] ...
    })
    ->map(function($v) {
        return $v[0];
    })
    ->take(5);
    
foreach ($result as $item) {
    echo $item . PHP_EOL;
}

//1
//1
//2
//3
//5
```

### You can pass any callable as argument to most methods
Prettified basic map reduce from before.

```php
function multiplyBy2($v)
{
    return $v*2;
}

function add($a, $b)
{
    return $a + $b;
}

$collection = new Collection([1,2]);
$result = $collection
    ->map('multiplyBy2')
    ->reduce(0, 'add');
    
echo $result; //6
```

### Callback arguments typehinted as Collection are converted automatically
```php
$collection = new Collection([[1, 2], [3, 4, 5]]);
$result = $collection
    ->map(function (Collection $i) {
        return $i->size();
    })
    ->reduce(0, function($tmp, $v) {
        return $tmp+$v;
    });
        
echo $result; //5
```
This behaviour works for all callables passed to Collection. No need to convert your arrays to Collection inside your callbacks.

### Can execute callback with argument templates
```php
$collection = new Collection([[1, 2], [3, 4, 5]]);
$result = $collection
    ->map('implode', ['', Argument::item()]) //implode with empty string
    ->toArray(); //[12, 345]        
```
This is available for all Collection methods that accept callable as argument. The argument template always goes after the callable argument.

#### There are 5 named constructor for the Argument class: #####

 - Argument::key()
 - Argument::item() 
 - Argument::secondKey() used in comparisons
 - Argument::secondItem() used in comparisons
 - Argument::intermediateValue() used in reductions
 
Use these in template and Collection will know how to replace these on each iteration. This is useful for calling native functions which do not have the footprint that Collection can guess - (item) or (key, item). 

### Collections are immutable
```php
function multiplyBy2($v)
{
    return $v * 2;
}

function multiplyBy3($v)
{
    return $v * 3;
}

function add($a, $b)
{
    return $a + $b;
}

$collection = new Collection([1,2]);
$result = $collection
    ->map('multiplyBy2')
    ->reduce(0, 'add');
    
echo $result; //6

//On the same collection
$differentResult = $collection
    ->map('multiplyBy3')
    ->reduce(0, 'add');
    
echo $differentResult; //9
```

### Keys are not unique by design
It would harm performance. This is only a problem if you need to call toArray(), then you should call resetKeys() before.
```php
$collection = new Collection([1,2]);
$result = $collection->concat([3,4]);
    
//arrays have unique keys
$result->toArray(); //[3,4]
$result->resetKeys()->toArray(); //[1,2,3,4]

//When iterating, you can have multiple keys.
foreach ($result as $key => $item) {
    echo $key . ':' . $item . PHP_EOL;
}

//0:1
//1:2
//0:3
//1:4
```

## Operations
These are the operations (methods) provided by Collection class.

### Standard Iterator methods
It implements http://php.net/manual/en/class.iterator.php

#### append(mixed $item) : Collection
Returns a lazy collection of items of this collection with $item added as last element. Its key will be 0.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->append(1)
    ->resetKeys() //both 1 have 0 key
    ->toArray(); //[1, 3, 3, 2, 1]
```

#### appendWithKey(mixed $key, mixed $item) : Collection
Returns a lazy collection of items of this collection with $item added as last element. Its key will be $key.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->appendWithKey('a', 1)
    ->toArray(); //[1, 3, 3, 2, 'a' => 1]
```

#### concat(Traversable|array) : Collection
Returns a lazy collection with items from this collection followed by items from $collection.
```php
$collection = new Collection([1,3,3,2]);
$collection
    ->concat([4,5])
    ->resetKeys() //If we would convert to array here, we would loose 2 items because of same keys
    ->toArray() //[1, 3, 3 => 2] - each item has key of the first occurrence
```

#### contains(mixed $needle) : bool
Returns true if $needle is present in the collection.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection->contains(2); //true
```

#### countBy(callable $differentiator) : Collection
Returns a collection of items whose keys are the return values of $differentiator and values are the number of items in this collection for which the $differentiator returned this value. $differentiator could take 1 argument (the item) or 2 arguments (key, item).
```php
$collection = new Collection([1, 2, 3, 4, 5]);
$collection
    ->countBy(function ($i) {
        return $v % 2 == 0 ? 'even' : 'odd';
    })
    ->toArray(); //['odd' => [1, 3, 5], 'even' => [2, 4]]
```

#### cycle() : Collection
Returns an infinite lazy collection of items in this collection repeated infinitely.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->cycle()
    ->take(8) //we take just 8 items, since this collection is infinite
    ->resetKeys()
    ->toArray(); //[1, 3, 3, 2, 1, 3, 3, 2]
```

#### distinct() : Collection
Returns a lazy collection of distinct items. The comparison whether the item is in the collection or not is the same as in in_array.
```php
$collection = new Collection([1,3,3,2]);
$collection
    ->distinct()
    ->toArray() //[1, 3, 3 => 2] - each item has key of the first occurrence
```

#### drop(int $numberOfItems) : Collection
A form of slice that returns all but first $numberOfItems items.
```php
$collection = new Collection([1, 2, 3, 4, 5]);
$collection
    ->drop(4)
    ->toArray(); //[4 => 5]
```

#### dropLast($numberOfItems = 1) : Collection
Returns a lazy collection with last $numberOfItems items skipped. These are still realized, just skipped.
```php
$collection = new Collection([1, 2, 3]);
$collection
    ->dropLast()
    ->toArray(); //[1, 2]
```
```php
$collection = new Collection([1, 2, 3]);
$collection
    ->dropLast(2)
    ->toArray(); //[1]
```

#### dropWhile(callable $predicament) : Collection
Returns a lazy collection by removing items from this collection until first item for which $predicament returns false. $predicament could take 1 argument (the item) or 2 arguments (key, item).
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->dropWhile(function ($v) {
        return $v < 3;
    })
    ->toArray(); //[1 => 3, 2 => 3, 3 => 2])
```
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->dropWhile(function ($k, $v) {
        return $k < 2 && $v < 3;
    })
    ->toArray(); //[1 => 3, 2 => 3, 3 => 2])
```

#### each(callable $callback) : Collection
Returns a lazy collection in which $callback is executed for each item. $callback could take 1 argument (the item) or 2 arguments (key, item).
```php
$collection = new Collection([1, 2, 3, 4, 5]);
$collection
    ->each(function ($i) {
        echo $i . PHP_EOL;
    })
    ->toArray(); //[1, 2, 3, 4, 5]

//1
//2
//3
//4
//5
```

#### every(callable $predicament) : bool
Returns true if $predicament returns true for every item in this collection, false otherwise. $predicament could take 1 argument (the item) or 2 arguments (key, item).
```php
$collection = new Collection([1, 3, 3, 2]);
$collection->every(function ($v) {
   return $v < 3;
}); //false
```
```php
$collection = new Collection([1, 3, 3, 2]);
$collection->find(function ($k, $v) {
   return $v < 4 && $k < 2;
}, 10); //false
```

#### filter(callable $filter) : Collection
Returns a lazy collection of items for which $filter returned true. $filter could take 1 argument (the item) or 2 arguments (key, item).
```php
$collection = new Collection([1,3,3,2]);
$collection->filter(function ($item) {
        return $item > 2;
    })
    ->toArray() //[1 => 3, 2 => 3]
```
```php
$collection = new Collection([1,3,3,2]);
$collection->filter(function ($key, $item) {
        return $item > 2 && $key > 1;
    })
    ->toArray() //[2 => 3]
```

#### find(callable $filter, mixed $ifNotFound = null) : mixed
Returns first value matched by callable. If no value matches, return $ifNotFound. $filter could take 1 argument (the item) or 2 arguments (key, item).
```php
$collection = new Collection([1, 3, 3, 2]);
$collection->find(function ($v) {
   return $v < 3;
}); //1
```
```php
$collection = new Collection([1, 3, 3, 2]);
$collection->find(function ($v) {
   return $v > 3;
}, 10); //10
```
```php
$collection = new Collection([1, 3, 3, 2]);
$collection->find(function ($k, $v) {
  return $v < 3 && $k > 1;
}); //2
```
     
#### findCollection(callable $filter, $ifNotFound = null) : Collection
Like find, but converts the return value to Collection if possible (i.e. if it's an array). $filter could take 1 argument (the item) or 2 arguments (key, item).
```php
$collection = new Collection([[1, 2], [2, 3]]);
$collection
    ->findCollection(function ($v) {
        return $v[0] + $v[1] > 4;
    })
    ->toArray(); //[2, 3]
```
```php
$collection = new Collection([[1, 2], [2, 3]]);
$collection
    ->findCollection(function ($k, $v) {
        return $k > 0;
    })
    ->toArray(); //[2, 3]
```

#### flatten(int $depth = -1) : Collection
Returns a lazy collection with one or multiple levels of nesting flattened. Removes all nesting when no $depth value is passed.
```php
$collection = new Collection([1,[2, [3]]]);
$collection
    ->flatten()
    ->resetKeys() //1, 2 and 3 have all key 0
    ->toArray() //[1, 2, 3]
```
```php
$collection = new Collection([1,[2, [3]]]);
$collection
    ->flatten(1)
    ->resetKeys() //1, 2 and 3 have all key 0
    ->toArray() //[1, 2, [3]]
```

#### frequencies() : Collection
Returns a collection where keys are distinct items from this collection and their values are number of occurrences of each value.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->frequencies()
    ->toArray(); //[1 => 1, 3 => 2, 2 => 1]
```

#### get(mixed $key, mixed $ifNotFound = null) : mixed
Returns value at the key $key. If multiple values have this key, return first. If no value has this key, return $ifNotFound.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection->get(2); //3
```

#### getCollection(mixed $key, $ifNotFound = null) : Collection
Like get, but converts the return value to Collection if possible (i.e. if it's an array).
```php
$collection = new Collection(['a' => [1, 2], 'b' => [2, 3]]);
$collection
    ->getCollection('a')
    ->toArray(); //[1, 2]
```

#### groupBy(callable $differentiator) : Collection
Returns collection which items are separated into groups indexed by the return value of $differentiator. $differentiator could take 1 argument (the item) or 2 arguments (key, item).
```php
$collection = new Collection([1, 2, 3, 4, 5]);
$collection
    ->groupBy(function ($i) {
        return $i % 2;
    })
    ->toArray(); //[1 => [1, 3, 5], 0 => [2, 4]]
```

#### indexBy(callable $indexer) : Collection
Returns a lazy collection by changing keys of this collection for each item to the result of $indexer for that key/value. $indexer could take 1 argument (the item) or 2 arguments (key, item).
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->indexBy(function ($v) {
        return $v;
    })
    ->toArray(); //[1 => 1, 3 => 3, 2 => 2]
```

#### interleave(Traversable|array $collection) : Collection
Returns a lazy collection of first item from first collection, first item from second, second from first and so on.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->interleave(['a', 'b', 'c', 'd', 'e'])
    ->resetKeys()
    ->toArray(); //[1, 'a', 3, 'b', 3, 'c', 2, 'd', 'e']
```

#### interpose(mixed $separator) : Collection
Returns a lazy collection of items of this collection separated by $separator item.
```php
$collection = new Collection([1, 2, 3]);
$collection
    ->interpose('a')
    ->resetKeys() // we must reset the keys, because each 'a' has undecided key
    ->toArray(); //[1, 'a', 2, 'a', 3]
```

#### isEmpty() : bool
Returns true if is collection is empty. False otherwise.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection->isEmpty(); //false
```

#### isNotEmpty() : bool
Opposite of isEmpty
```php
$collection = new Collection([1, 3, 3, 2]);
$collection->isNotEmpty(); //true
```

#### iterate(callable $iterator) : Collection
Returns lazy collection which is infinite passing last item of this collection to the $iterator and using its return value as next item (and key). If you wish to pass the key, you must yield 2 values from $iterator, first is key, second is item. $iterator could take 1 argument (the item) or 2 arguments (key, item). If you throw a NoMoreItems exception, you will mark the end of the collection.
```php
$collection = new Collection([1]);
$collection
    ->iterate(function ($v) {
        return $v++;
    });
    
$it->rewind();
$it->valid() == true; //always true, we iterate to infinity
$it->key();// == ?; Keys are undecided
$it->current() == 1;
$it->next();
$it->valid() == true;
$it->current() == 2;
$it->next();
$it->valid() == true;
$it->current() == 3;
```
```php
$collection = new Collection([1]);
$collection
    ->iterate(function ($v) {
        yield $v--; //key
        yield $v++; //value
    });
    
$it->rewind();
$it->valid() == true; //always true, we iterate to infinity
$it->key() == 0;
$it->current() == 1;
$it->next();
$it->valid() == true;
$it->key() == 1;
$it->current() == 2;
$it->next();
$it->valid() == true;
$it->key() == 2;
$it->current() == 3;
```

#### keys() : Collection
Returns a lazy collection of the keys of this collection.
```php
$collection = new Collection(['a' => [1, 2], 'b' => [2, 3]]);
$collection
    ->keys()
    ->toArray(); //['a', 'b']
```

#### map(callable $mapper) : Collection
Returns collection where each key/item is changed to the output of executing $mapper on each key/item. If you wish to modify keys, yield 2 values in the callable. First is key, second is item. $mapper could take 1 argument (the item) or 2 arguments (key, item).
```php
$collection = new Collection([1,3,3,2]);
$collection
    ->map(function ($item) {
        return $item + 1;
    })
    ->toArray() //[2, 4, 4, 3]
```
```php
$collection = new Collection([1,3,3,2]);
$collection
    ->map(function ($key, $item) {
        yield $key + 1;
        yield $item;
    })
    ->toArray() //[1 => 1, 2 => 3, 3 => 3, 4 => 2]
```
```php
$collection = new Collection([1,3,3,2]);
$collection
    ->map(function ($key, $item) {
        yield $item + 1;
    })
    ->toArray() //[2, 4, 4, 3]
```

#### mapcat(callable $mapper) : Collection
Returns a lazy collection which is a result of calling map($mapper) and then flatten(1). $mapper could take 1 argument (the item) or 2 arguments (key, item).
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->mapcat(function ($v) {
        return [[$v]];
    })
    ->toArray(); //[[1], [3], [3], [2]]
```
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->mapcat(function ($k, $v) {
        return [[$k]];
    })
    ->toArray(); //[[0], [1], [2], [3]]
```

#### partition(int $numberOfItems, int $step = 0, Traversable|array $padding = []) : Collection
Returns a lazy collection of collections of $numberOfItems items each, at $step step apart. If $step is not supplied, defaults to $numberOfItems, i.e. the partitionsdo not overlap. If a $padding collection is supplied, use its elements asnecessary to complete last partition up to $numberOfItems items. In case there are not enough padding elements, return a partition with less than $numberOfItems items.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->partition(3, 2, [0, 1])
    ->toArray(); //[[1, 3, 3], [2 => 3, 3 => 2, 0 => 0]]
```
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->partition(3, 2)
    ->toArray(); //[[1, 3, 3], [2 => 3, 3 => 2]]
```
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->partition(3)
    ->toArray(); //[[1, 3, 3], [3 => 2]]
```

#### partitionBy(callable $partitioning) : Collection
Creates a lazy collection of collections created by partitioning this collection every time $partitioning will return different result.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->partitionBy(function ($v) {
        return $v % 3 == 0;
    })
    ->toArray(); //[[1], [1 => 3, 2 => 3], [3 => 2]]
```

#### prepend(mixed $item) : Collection
Returns a lazy collection of items of this collection with $item added as first element. Its key will be 0.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->prepend(1)
    ->resetKeys() //both 1 have 0 key
    ->toArray(); //[1, 1, 3, 3, 2]
```

#### prependWithKey(mixed $key, mixed $item) : Collection
Returns a lazy collection of items of this collection with $item added as first element. Its key will be $key.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->prependWithKey('a', 1)
    ->toArray(); //['a' => 1, 0 => 1, 1 => 3, 2 => 3, 3 => 2]
```

#### reduce(mixed $start, callable) : mixed
Reduces the collection to single value by iterating over the collection and calling callable while passing $start and current key/item as parameters. The output of callable is used as $start in next iteration. The output of callable on last element is the return value of this function.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection->reduce(0, function ($tmp, $i) {
   return $tmp + $i;
}); //9
```

#### reduceRight(mixed $start,, callable $reduction) : mixed
Like reduce, but walks from last item to the first one.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection->reduceRight(0, function ($tmp, $i) {
   return $tmp + $i;
}); //9
```

#### reductions($start, callable $reduction) : Collection
Returns a lazy collection of reduction steps.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->reductions(0, function ($tmp, $i) {
        return $tmp + $i;
    })
    ->toArray(); //[1, 4, 7, 9]
```

#### reject(callable $filter) : Collection
Returns a lazy collection of items for which $filter returned false. $filter could take 1 argument (the item) or 2 arguments (key, item).
```php
$collection = new Collection([1,3,3,2]);
$collection->reject(function ($item) {
        return $item > 2;
    })
    ->toArray() //[1, 3 => 2]
```
```php
$collection = new Collection([1,3,3,2]);
$collection->reject(function ($key, $item) {
        return $item > 2 && $key > 1;
    })
    ->toArray() ////[1, 1 => 3, 3 => 2]
```

#### replace(Traversable|array $replacementMap) : Collection
Returns a lazy collection with items from this collection equal to any key in $replacementMap replaced for their value.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->replace([3 => 'a'])
    ->toArray(); //[1, 'a', 'a', 2]
```

#### resetKeys() : Collection
Returns collection of items from this collection but with keys being numerical from 0 upwards.
```php
$collection = new Collection(['asd' => 1]);
$collection
    ->resetKeys()
    ->toArray(); //[1]
```

#### reverse() : Collection
Returns collection of items in this collection in reverse order.
```php
$collection = new Collection([1, 2, 3]);
$collection
    ->reverse()
    ->toArray(); //[2 => 3, 1 => 2, 0 => 1]
```

#### shuffle() : Collection
Returns a collection of shuffled items from this collection
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->shuffle()
    ->toArray(); //something like [2 => 3, 0 => 1, 3 => 2, 1 => 3]
```

#### size() : int
Returns the number of items in this collection.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection->size(); //4
```

#### slice(int $from, int $to) : Collection
Returns a lazy collection of items which are part of the original collection from item number $from to item number $to inclusive. The items before $from are also realized, just not returned.
```php
$collection = new Collection([1, 2, 3, 4, 5]);
$collection
    ->slice(2, 4)
    ->toArray(); //[1 => 2, 2 => 3, 3 => 4]
```
```php
$collection = new Collection([1, 2, 3, 4, 5]);
$collection
    ->slice(4)
    ->toArray(); //[3 => 4, 4 => 5]
```

#### some(callable $predicament) : bool
Returns true if $predicament returns true for at least one item in this collection, false otherwise. $predicament could take 1 argument (the item) or 2 arguments (key, item).
```php
$collection = new Collection([1, 3, 3, 2]);
$collection->every(function ($v) {
   return $v < 3;
}); //true
```
```php
$collection = new Collection([1, 3, 3, 2]);
$collection->find(function ($k, $v) {
   return $v < 4 && $k < 2;
}, 10); //true
```

#### sort(callable $sort) : Collection
Returns collection sorted using $sort($item1, $item2). $sort should return true if first item is larger than the second and false otherwise.
```php
$collection = new Collection([3, 1, 2]);
$collection
    ->sort(function ($a, $b) {
        return $a > $b;
    })
    ->toArray(); //[1 => 1, 2 => 2, 0 => 3]
```
```php
$collection = new Collection([3, 1, 2]);
$collection
    ->sort(function ($k1, $v1, $k2, $v2) {
        return $v1 < $v2;
    })
    ->toArray(); //[2 => 2, 1 => 1, 0 => 3]
```

#### splitAt(int $position) : Collection
Returns a collection of lazy collections: [take($position), drop($position)].
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->splitAt(2)
    ->toArray(); //[[1, 3], [2 => 3, 3 => 2]]
```

#### splitWith(callable $predicament) : Collection
Returns a collection of lazy collections: [takeWhile($predicament), dropWhile($predicament)]. $predicament could take 1 argument (the item) or 2 arguments (key, item).
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->splitWith(function ($v) {
        return $v < 3;
    })
    ->toArray(); //[[1], [1 => 3, 2 => 3, 3 => 2]]
```
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->splitWith(function ($k, $v) {
        return $k < 2 && $v < 3;
    })
    ->toArray(); //[[1], [1 => 3, 2 => 3, 3 => 2]]
```

#### take(int $numberOfItems) : Collection
A form of slice that returns first $numberOfItems items.
```php
$collection = new Collection([1, 2, 3, 4, 5]);
$collection
    ->take(2)
    ->toArray(); //[1, 2]
```

#### takeNth(int $step) : Collection
Returns a lazy collection of every nth item in this collection
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->takeNth(2)
    ->toArray(); //[1, 2 => 3]
```

#### takeWhile(callable $predicament) : Collection
Returns a lazy collection of items from the start of the collection until the first item for which $predicament returns false. $predicament could take 1 argument (the item) or 2 arguments (key, item).
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->takeWhile(function ($v) {
        return $v < 3;
    })
    ->toArray(); //[1]
```
```php
$collection = new Collection([1, 3, 3, 2]);
$collection
    ->takeWhile(function ($k, $v) {
        return $k < 2 && $v < 3;
    })
    ->toArray(); //[1]
```

#### toArray() : array
Converts the collection to array recursively. Obviously this is not lazy since all the items must be realized. Calls iterator_to_array internaly.
```php
$collection = new Collection([1, 3, 3, 2]);
$collection->toArray(); //[1, 3, 3, 2]
```

## Todo    
- multiple collections can be passed to lets say concat
- rewrite from inheritance to using traits (iterable => collection operations), so it's easier to reason about the code
- performance tests/comparison
- more scenarios
