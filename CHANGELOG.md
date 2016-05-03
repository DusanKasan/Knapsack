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
- **Breaking change: getNth removed (to solve ambiguity with takeNth)**
- **Breaking change: difference renamed to diff**
