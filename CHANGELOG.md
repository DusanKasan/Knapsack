# Changelog

##0.2.0
- First release
- Every operation is represented by different Collection class

##0.3.0
- **Total overhaul and move to more functional design**
- Collection class now uses Generator functions under the hood. 
- The functions are also accessible allowing for functional programming. 
- Performance improved 2x.

##0.4.0
- More utility functions added

##0.4.1
- Missing utility functions added to readme

##0.4.2
- Ditching PHP 5.5. support
- Variadics introduced to some functions

##0.4.3
- No longer rewinds wrapped collection in constructor

##1.0.0
- The project is ready for production use. No known bugs exist.

##2.0.0
- Project moved to new global namespace DusanKasan (whole namespace is DusanKasan\Knapsack) to avoid conflicts. 
- Collection::realize was introduced to force materialization of the collection (turning lazy collection into non-lazy). 
- Collection::concat and Collection::interleave are now variadic.
- **Breaking change: toArray and Collection::toArray now behave more logicaly and do not convert items recursively.**

##3.0.0
- Automatic conversion of return values to Collections is no longer happening if you do not explicitly require it. Details in documentation.

##3.1.0
- CollectionTrait has been introduced and its usage documented in readme.
- New functions added:
    - second() // seems useless but really usefull :)
    - combine($values) // uses current collection as keys, $values as values
    - except($keys) // rejects every item with key found in $keys
    - only($keys) // filters only items with key found in $keys
    - difference(...$collections) // filters items that are in the original collection but not in any of $collections
    - flip() // flips keys with values
    - has($key) // checks for the existence of item with $key key
- A handful of bugfixes also:
    - Collection constructor might have experienced conflicts between callable (in array form) and array arguments
    - Pluck might have failed on heterogenous collections. Now ignores non-collection items.

##4.0.0
- GroupByKey function introduced
- Serialization support added
- Changelog added
- **Breaking change: combine now throws NonEqualCollectionLength**

##5.0.0
- Zip function added
- Extract function added
- Transform function added
- **Breaking change: combine now stops when it runs out of keys or values**
- **Breaking change: pluck removed (replaced by extract)**

##6.0.0
- Intersect function added
- Average utility function added
- Concatenate utility function added
- Reduce/reduceRight/second now have the returnAsCollection flag
- **Breaking change: getNth removed (to solve ambiguity with takeNth)**
- **Breaking change: difference renamed to diff**

##6.1.0
- Filter can be called without arguments and it will remove falsy values

##6.2.0
- sizeIsExactly function added
- sizeIsGreaterThan function added
- sizeIsLessThan function added
- sizeIsBetween function added

##7.0.0
- The functionality of sum, average, min, max and concatenate moved into collection. 
- Sum collection function added
- Average collection function added
- Min collection function added
- Max collection function added
- ToString collection function added
- **Breaking change: sum utility function removed**
- **Breaking change: average utility function removed**
- **Breaking change: min utility function removed**
- **Breaking change: max utility function removed**
- **Breaking change: concatenate utility function removed**

##8.0.0
- **Breaking change: sum function will return integer by default, float if there are float type elements**
- **Breaking change: average function will not force return float and will return integer if the sum/count result is integer**

##8.1.0
- ReplaceByKeys function added

##8.1.1
- Fixed bug: the only function always included the item with key equal to zero in the result. Caused by comparing string == 0. Also affected extract.

##8.2.0
- Dump function added, to make debugging easier.

##8.3.0
- PrintDump function added, to make debugging easier. Prints debug output, but returns the original collection.  

##8.4.0
- Transpose functionality added.

##8.4.1
- Issue where group by could blow the stack fixed by internally using array to group the items.

##9.0.0
- **Breaking change: Collection no longer implements Iterator but instead implements Traversable via IteratorAggregate**
- Moving from Iterator to Traversable allows for huge performance gains (some 4x improvement at the very least)
