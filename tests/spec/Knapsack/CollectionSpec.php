<?php

namespace spec\Knapsack;

use ArrayIterator;
use DOMXPath;
use Iterator;
use IteratorAggregate;
use Knapsack\Callback\Argument;
use Knapsack\Collection;
use Knapsack\Exceptions\InvalidArgument;
use PhpSpec\ObjectBehavior;

/**
 * @mixin Collection
 */
class CollectionSpec extends ObjectBehavior
{
    function let()
    {
        $array = [1, 3, 3, 2,];
        $this->beConstructedWith(new ArrayIterator($array));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Collection::class);
        $this->shouldHaveType(Iterator::class);
    }

    function it_can_be_instantiated_from_iterator()
    {
        $iterator = new ArrayIterator([1, 2]);
        $this->beConstructedWith($iterator);
        $this->toArray()->shouldReturn([1, 2]);
    }

    function it_can_be_instantiated_from_iterator_aggregate(IteratorAggregate $iteratorAggregate)
    {
        $iterator = new ArrayIterator([1, 2]);
        $iteratorAggregate->getIterator()->willReturn($iterator);
        $this->beConstructedWith($iteratorAggregate);
        $this->toArray()->shouldReturn([1, 2]);
    }

    function it_will_throw_when_passed_something_other_than_array_or_traversable()
    {
        $this->shouldThrow(InvalidArgument::class)->during('__construct', [1]);
    }

    function it_can_iterate_correctly_when_passed_array()
    {
        $this->beConstructedWith([1, 2, 3]);
        $this->toArray()->shouldReturn([1, 2, 3]);
    }

    function it_can_filter()
    {
        $this->filter(
            function ($item) {
                return $item > 2;
            })
            ->toArray()
            ->shouldReturn([1 => 3, 2 => 3]);

        $this->filter(
            function ($key, $item) {
                return $key > 2;
            })
            ->toArray()
            ->shouldReturn([3 => 2]);
    }

    function it_can_distinct()
    {
        $this->distinct()->toArray()->shouldReturn([1, 3, 3 => 2]);
    }

    function it_can_concat()
    {
        $secondIterator = new ArrayIterator([4, 5]);
        $c1 = $this->concat($secondIterator);
        $c1->toArray()->shouldReturn([4, 5, 3, 2]);
        $c1->size()->shouldReturn(6);
    }

    function it_can_map()
    {
        $this->map(function ($item) {
            return $item + 1;
        })
            ->toArray()
            ->shouldReturn([2, 4, 4, 3]);

        $this->map(function ($key, $item) {
            yield $key + 1;
            yield $item;
        })
            ->toArray()
            ->shouldReturn([1 => 1, 2 => 3, 3 => 3, 4 => 2]);

        $this->map(function ($item) {
            yield $item + 1;
        })
            ->toArray()
            ->shouldReturn([2, 4, 4, 3]);
    }

    function it_can_reduce()
    {
        $this->reduce(
            0,
            function ($temp, $item) {
                return $temp + $item;
            }
        )
            ->shouldReturn(9);

        $this->reduce(
            0,
            function ($temp, $key, $item) {
                return $temp + $key + $item;
            }
        )
            ->shouldReturn(15);
    }

    function it_can_flatten()
    {
        $this->beConstructedWith([1, [2, [3]]]);
        $this->flatten()->resetKeys()->toArray()->shouldReturn([1, 2, 3]);
        $this->flatten(1)->resetKeys()->toArray()->shouldReturn([1, 2, [3]]);
    }

    function it_can_sort()
    {
        $this->beConstructedWith([3, 1, 2]);
        $this->sort(
            function ($a, $b) {
                return $a > $b;
            })
            ->toArray()
            ->shouldReturn([1 => 1, 2 => 2, 0 => 3]);

        $this->sort(
            function ($k1, $v1, $k2, $v2) {
                return $k1 < $k2;
            })
            ->toArray()
            ->shouldReturn([2 => 2, 1 => 1, 0 => 3]);
    }

    function it_can_slice()
    {
        $this->beConstructedWith([1, 2, 3, 4, 5]);
        $this->slice(2, 4)->toArray()->shouldReturn([1 => 2, 2 => 3, 3 => 4]);

        $this->slice(4)->toArray()->shouldReturn([3 => 4, 4 => 5]);
    }

    function it_can_group_by()
    {
        $this->beConstructedWith([1, 2, 3, 4, 5]);
        $this->groupBy(function ($i) {
            return $i % 2;
        })
            ->toArray()
            ->shouldReturn([1 => [1, 3, 5], 0 => [2, 4]]);
    }

    function it_can_execute_callback_for_each_item(DOMXPath $a)
    {
        $a->query('asd')->shouldBeCalled();

        $this->beConstructedWith([$a]);
        $this->each(function (DOMXPath $i) {
            $i->query('asd');
        })
            ->toArray()
            ->shouldReturn([$a]);
    }

    function it_can_get_size()
    {
        $this->size()->shouldReturn(4);
    }

    function it_can_get_item_by_key()
    {
        $this->get(0)->shouldReturn(1);
        $this->get(5)->shouldReturn(null);
        $this->get(5, 'not found')->shouldReturn('not found');
    }

    function it_can_find_key()
    {
        $this->find(function ($v) {
            return $v < 3;
        })->shouldReturn(1);
        $this->find(function ($k, $v) {
            return $v < 3 && $k > 1;
        })->shouldReturn(2);
        $this->find(function ($v) {
            return $v < 0;
        })->shouldReturn(null);
        $this->find(function ($v) {
            return $v < 0;
        },
            'not found')->shouldReturn('not found');
    }

    function it_can_count_by()
    {
        $this->countBy(function ($v) {
            return $v % 2 == 0 ? 'even' : 'odd';
        })
            ->toArray()
            ->shouldReturn(['odd' => 3, 'even' => 1]);
    }

    function it_can_index_by()
    {
        $this->indexBy(
            function ($v) {
                return $v;
            })
            ->toArray()
            ->shouldReturn([1 => 1, 3 => 3, 2 => 2]);

        $this->indexBy(
            function ($k, $v) {
                return $k . 'a';
            })
            ->toArray()
            ->shouldReturn(['0a' => 1, '1a' => 3, '2a' => 3, '3a' => 2]);
    }

    function it_can_check_if_every_item_passes_predicament_test()
    {
        $this->every(function ($v) {
            return $v > 0;
        })->shouldReturn(true);
        $this->every(function ($v) {
            return $v > 1;
        })->shouldReturn(false);
        $this->every(function ($k, $v) {
            return $v > 0 && $k >= 0;
        })->shouldReturn(true);
        $this->every(function ($k, $v) {
            return $v > 0 && $k > 0;
        })->shouldReturn(false);
    }

    function it_can_check_if_some_items_pass_predicament_test()
    {
        $this->some(function ($v) {
            return $v < -1;
        })->shouldReturn(false);
        $this->some(function ($k, $v) {
            return $v > 0 && $k < -1;
        })->shouldReturn(false);
        $this->some(function ($v) {
            return $v < 2;
        })->shouldReturn(true);
        $this->some(function ($k, $v) {
            return $v > 0 && $k > 0;
        })->shouldReturn(true);
    }

    function it_can_check_if_it_contains_a_value()
    {
        $this->contains(3)->shouldReturn(true);
        $this->contains(true)->shouldReturn(false);
    }

    function it_can_reverse()
    {
        $it = $this->reverse();

        $it->rewind();
        $it->valid()->shouldReturn(true);
        $it->key()->shouldReturn(3);
        $it->current()->shouldReturn(2);
        $it->next();
        $it->valid()->shouldReturn(true);
        $it->key()->shouldReturn(2);
        $it->current()->shouldReturn(3);
        $it->next();
        $it->valid()->shouldReturn(true);
        $it->key()->shouldReturn(1);
        $it->current()->shouldReturn(3);
        $it->next();
        $it->valid()->shouldReturn(true);
        $it->key()->shouldReturn(0);
        $it->current()->shouldReturn(1);
        $it->next();
        $it->valid()->shouldReturn(false);
    }

    function it_can_reduce_from_right()
    {
        $this->reduceRight(
            '',
            function ($temp, $e) {
                return $temp . $e;
            }
        )
            ->shouldReturn('2331');

        $this->reduceRight(
            0,
            function ($temp, $key, $item) {
                return $temp + $key + $item;
            }
        )
            ->shouldReturn(15);
    }

    function it_can_return_only_first_x_elements()
    {
        $this->take(2)
            ->toArray()
            ->shouldReturn([1, 3]);
    }

    function it_can_skip_first_x_elements()
    {
        $this->drop(2)
            ->toArray()
            ->shouldReturn([2 => 3, 3 => 2]);
    }

    function it_can_reset_keys()
    {
        $this->beConstructedWith(['a' => 1, 'b' => 2]);
        $this->resetKeys()->toArray()->shouldReturn([1, 2]);
    }

    function it_can_follow_a_callback_to_continue_iteration()
    {
        $this->beConstructedWith([[1, 1]]);

        $it = $this->iterate(function ($v) {
            return [$v[1], $v[0] + $v[1]];
        });
        $it->rewind();
        $it->valid()->shouldReturn(true);
        $it->current()->shouldReturn([1, 1]);
        $it->next();
        $it->valid()->shouldReturn(true);
        $it->current()->shouldReturn([1, 2]);
        $it->next();
        $it->valid()->shouldReturn(true);
        $it->current()->shouldReturn([2, 3]);

        $it = $this->iterate(function ($k, $v) {
            yield $k + $v[1];
            yield [$v[1], $v[0] + $v[1]];
        });

        $it->rewind();
        $it->valid()->shouldReturn(true);
        $it->key()->shouldReturn(0);
        $it->current()->shouldReturn([1, 1]);
        $it->next();
        $it->valid()->shouldReturn(true);
        $it->key()->shouldReturn(1);
        $it->current()->shouldReturn([1, 2]);
        $it->next();
        $it->valid()->shouldReturn(true);
        $it->key()->shouldReturn(3);
        $it->current()->shouldReturn([2, 3]);

        $it = $this->iterate(function ($v) {
            yield [$v[1], $v[0] + $v[1]];
        });

        $it->rewind();
        $it->valid()->shouldReturn(true);
        $it->current()->shouldReturn([1, 1]);
        $it->next();
        $it->valid()->shouldReturn(true);
        $it->current()->shouldReturn([1, 2]);
        $it->next();
        $it->valid()->shouldReturn(true);
        $it->current()->shouldReturn([2, 3]);
    }

    function it_can_find_item_and_return_it_as_a_collection()
    {
        $this->beConstructedWith([[1, 2], [2, 3]]);

        $this->findCollection(function ($v) {
            return $v[0] + $v[1] > 4;
        })
            ->toArray()
            ->shouldReturn([2, 3]);

        $this->findCollection(function ($k, $v) {
            return $k > 0;
        })
            ->toArray()
            ->shouldReturn([2, 3]);
    }

    function it_can_get_item_by_its_key_and_return_it_as_a_collection()
    {
        $this->beConstructedWith(['a' => [1, 2], 'b' => [2, 3]]);

        $this->getCollection('b')
            ->toArray()
            ->shouldReturn([2, 3]);
    }

    function it_can_remove_elements_from_collection()
    {
        $this->reject(function ($v) {
            return $v == 3;
        })
            ->toArray()
            ->shouldReturn([1, 3 => 2]);

        $this->reject(function ($k, $v) {
            return $k == 2 && $v == 3;
        })
            ->toArray()
            ->shouldReturn([1, 3, 3 => 2]);
    }

    function it_can_get_keys()
    {
        $this->keys()->toArray()->shouldReturn([0, 1, 2, 3]);
    }

    function it_can_interpose()
    {
        $this->interpose('a')
            ->resetKeys()
            ->toArray()
            ->shouldReturn([1, 'a', 3, 'a', 3, 'a', 2]);
    }

    function it_can_drop_elements_from_end_of_the_collection()
    {
        $this->dropLast()->toArray()->shouldReturn([1, 3, 3]);
        $this->dropLast(2)->toArray()->shouldReturn([1, 3]);
    }

    function it_can_interleave_elements()
    {
        $this->interleave(['a', 'b', 'c', 'd', 'e'])
            ->resetKeys()
            ->toArray()
            ->shouldReturn([1, 'a', 3, 'b', 3, 'c', 2, 'd', 'e']);
    }

    function it_can_repeat_items_of_collection_infinitely()
    {
        $this->cycle()
            ->take(8)
            ->resetKeys()
            ->toArray()
            ->shouldReturn([1, 3, 3, 2, 1, 3, 3, 2]);
    }

    function it_can_prepend_item()
    {
        $this->prepend(1)
            ->resetKeys()
            ->toArray()
            ->shouldReturn([1, 1, 3, 3, 2]);
    }

    function it_can_prepend_item_with_key()
    {
        $this->prependWithKey('a', 1)
            ->toArray()
            ->shouldReturn(['a' => 1, 0 => 1, 1 => 3, 2 => 3, 3 => 2]);
    }

    function it_can_append_item()
    {
        $this->append(1)
            ->resetKeys()
            ->toArray()
            ->shouldReturn([1, 3, 3, 2, 1]);
    }

    function it_can_append_item_with_key()
    {
        $this->appendWithKey('a', 1)
            ->toArray()
            ->shouldReturn([1, 3, 3, 2, 'a' => 1,]);
    }

    function it_can_drop_items_while_predicament_is_true()
    {
        $this->dropWhile(function ($v) {
            return $v < 3;
        })
            ->toArray()
            ->shouldReturn([1 => 3, 2 => 3, 3 => 2]);

        $this->dropWhile(function ($k, $v) {
            return $k < 2 && $v < 3;
        })
            ->toArray()
            ->shouldReturn([1 => 3, 2 => 3, 3 => 2]);
    }

    function it_can_map_and_then_concatenate_a_collection()
    {
        $this->mapcat(function ($v) {
            return [[$v]];
        })
            ->resetKeys()
            ->toArray()
            ->shouldReturn([[1], [3], [3], [2]]);

        $this->mapcat(function ($k, $v) {
            return [[$k]];
        })
            ->resetKeys()
            ->toArray()
            ->shouldReturn([[0], [1], [2], [3]]);
    }

    function it_can_take_items_while_predicament_is_true()
    {
        $this->takeWhile(function ($v) {
            return $v < 3;
        })
            ->toArray()
            ->shouldReturn([1]);

        $this->takeWhile(function ($k, $v) {
            return $k < 2 && $v < 3;
        })
            ->toArray()
            ->shouldReturn([1]);
    }

    function it_can_split_the_collection_at_nth_item()
    {
        $this->splitAt(2)->toArray()->shouldReturn([[1, 3], [2 => 3, 3 => 2]]);
    }

    function it_can_split_the_collection_with_predicament()
    {
        $this->splitWith(function ($v) {
            return $v < 3;
        })
            ->toArray()
            ->shouldReturn([[1], [1 => 3, 2 => 3, 3 => 2]]);

        $this->splitWith(function ($k, $v) {
            return $k < 2 && $v < 3;
        })
            ->toArray()
            ->shouldReturn([[1], [1 => 3, 2 => 3, 3 => 2]]);
    }

    function it_can_replace_items_by_items_from_another_collection()
    {
        $this->replace([3 => 'a'])->toArray()->shouldReturn([1, 'a', 'a', 2]);
    }

    function it_can_get_reduction_steps()
    {
        $this->reductions(0,
            function ($tmp, $i) {
                return $tmp + $i;
            })
            ->toArray()
            ->shouldReturn([1, 4, 7, 9]);
    }

    function it_can_return_every_nth_item()
    {
        $this->takeNth(2)
            ->toArray()
            ->shouldReturn([1, 2 => 3]);
    }

    function it_can_shuffle_itself()
    {
        $this->shuffle()
            ->reduce(0,
                function ($tmp, $i) {
                    return $tmp + $i;
                })
            ->shouldReturn(9);
    }

    function it_can_partition()
    {
        $this->partition(3, 2, [0, 1])->toArray()->shouldReturn([[1, 3, 3], [2 => 3, 3 => 2, 0 => 0]]);
        $this->partition(3, 2)->toArray()->shouldReturn([[1, 3, 3], [2 => 3, 3 => 2]]);
        $this->partition(3)->toArray()->shouldReturn([[1, 3, 3], [3 => 2]]);
    }

    function it_can_partition_by()
    {
        $this->partitionBy(function ($v) {
            return $v % 3 == 0;
        })
            ->toArray()
            ->shouldReturn([[1], [1 => 3, 2 => 3], [3 => 2]]);

        $this->partitionBy(function ($k, $v) {
            return $k - $v;
        })
            ->toArray()->shouldReturn([[1], [1 => 3], [2 => 3], [3 => 2]]);
    }

    function it_can_check_if_it_is_not_empty()
    {
        $this->isEmpty()->shouldReturn(false);
        $this->isNotEmpty()->shouldReturn(true);
    }

    function it_can_check_if_it_is_empty()
    {
        $this->beConstructedWith([]);

        $this->isEmpty()->shouldReturn(true);
        $this->isNotEmpty()->shouldReturn(false);
    }

    function it_can_check_frequency_of_distinct_items()
    {
        $this->frequencies()->toArray()->shouldReturn([1 => 1, 3 => 2, 2 => 1]);
    }
}
