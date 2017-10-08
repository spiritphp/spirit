<?php

namespace Tests\Collection;

use PHPUnit\Framework\TestCase;
use \Spirit\Collection;

/**
 * @covers DB
 */
final class CollectionTest extends TestCase
{
    public function testBase()
    {
        $arr = [
            [
                'id' => 1,
                'title' => 'Title 1',
                'author' => 'Author 1',
            ],
            [
                'id' => 2,
                'title' => 'Title 2',
                'author' => 'Author 2'
            ],
            [
                'id' => 3,
                'title' => 'Title 3',
                'author' => 'Author 3'
            ],
        ];

        $items = Collection::make($arr);

        $this->assertInstanceOf(Collection::class, $items);
        $this->assertEquals($arr, $items->toArray());
        $this->assertEquals($arr, $items->all());
        $this->assertCount(3, $items);
        $this->assertEquals($arr[0], $items[0]);
        $this->assertEquals(3, $items->count());
    }

    public function testKeys()
    {
        $arr = [
            [
                'id' => 1,
                'title' => 'Title 1',
                'author' => 'Author 1',
            ],
            [
                'id' => 2,
                'title' => 'Title 2',
                'author' => 'Author 2'
            ],
            [
                'id' => 3,
                'title' => 'Title 3',
                'author' => 'Author 3'
            ],
        ];
        $items = Collection::make($arr);

        $this->assertEquals([
            1,
            2,
            3
        ], $items->pluck('id'));

        $this->assertCount(3, $items->keys());

        $new_item = $items->byKey('title');

        $this->assertEquals($arr[0], $new_item['Title 1']);
    }

    public function testPluck()
    {
        $arr = [
            [
                'id' => 1,
                'title' => 'Title 1',
                'author' => 'Author 1',
            ],
            [
                'id' => 2,
                'title' => 'Title 2',
                'author' => 'Author 2'
            ],
            [
                'id' => 3,
                'title' => 'Title 3',
                'author' => 'Author 3'
            ],
        ];
        $items = Collection::make($arr);

        $this->assertEquals([
            1,
            2,
            3
        ], $items->pluck('id'));
    }

    public function testJson()
    {
        $arr = [1, 2, 3];
        $items = Collection::make($arr);

        $this->assertEquals('[1,2,3]', json_encode($items));
    }

    public function testChunk()
    {
        $arr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $items = Collection::make($arr);

        $this->assertEquals([
            [0 => 1, 1 => 2, 2 => 3, 3 => 4],
            [4 => 5, 5 => 6, 6 => 7, 7 => 8],
            [8 => 9, 9 => 10]
        ], $items->chunk(4)
            ->toArray());
    }

    public function testCollapse()
    {
        $arr = [[1, 2, 3, 4], [5, 6, 7, 8], [9, 10]];
        $items = Collection::make($arr);

        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $items->collapse()
            ->toArray());
    }

    public function testGroupBy()
    {
        $arr = [
            ['tag' => 'photo', 'product' => 'I'],
            ['tag' => 'photo', 'product' => 'You'],
            ['tag' => 'video', 'product' => 'Y & I'],
        ];
        $items = Collection::make($arr);

        $this->assertEquals([
            'photo' => [
                ['tag' => 'photo', 'product' => 'I'],
                ['tag' => 'photo', 'product' => 'You'],
            ],
            'video' => [
                ['tag' => 'video', 'product' => 'Y & I'],
            ]
        ], $items->groupBy('tag')
            ->toArray());
    }

    public function testFilter()
    {
        $items = new Collection([
            [
                'product' => '1',
                'price' => 100
            ],
            [
                'product' => '2',
                'price' => 120
            ],
            [
                'product' => '3',
                'price' => 80
            ]
        ]);

        $this->assertEquals([
            [
                'product' => '1',
                'price' => 100
            ],
            [
                'product' => '2',
                'price' => 120
            ]
        ], $items->filter(function ($item, $key) {
            return $item['price'] >= 100;
        })
            ->toArray());

        $this->assertEquals([1], Collection::make([1, null, ''])
            ->filter()
            ->toArray());
    }

    public function testReject()
    {
        $items = new Collection([
            [
                'product' => '1',
                'price' => 100
            ],
            [
                'product' => '2',
                'price' => 120
            ],
            [
                'product' => '3',
                'price' => 80
            ]
        ]);

        $this->assertEquals([
            [
                'product' => '3',
                'price' => 80
            ]
        ], $items->reject(function ($item, $key) {
            return $item['price'] >= 100;
        })
            ->values()
            ->toArray());

        $this->assertEquals([1 => null, 2 => ''], Collection::make([1, null, ''])
            ->reject()
            ->toArray());
    }

    public function testWhere()
    {
        $items = new Collection([
            [
                'product' => '1',
                'price' => 100
            ],
            [
                'product' => '2',
                'price' => 120
            ],
            [
                'product' => '3',
                'price' => 80
            ]
        ]);

        $this->assertEquals([
            [
                'product' => '3',
                'price' => 80
            ]
        ], $items->where('price', 80)
            ->values()
            ->toArray());

        $this->assertEquals([
            [
                'product' => '1',
                'price' => 100
            ],
            [
                'product' => '2',
                'price' => 120
            ]
        ], $items->where('price', '>', 80)
            ->values()
            ->toArray());
    }


    public function testWhereIn()
    {
        $items = new Collection([
            [
                'product' => '1',
                'price' => 100
            ],
            [
                'product' => '2',
                'price' => 120
            ],
            [
                'product' => '3',
                'price' => 80
            ]
        ]);

        $this->assertEquals([
            [
                'product' => '1',
                'price' => 100
            ],
            [
                'product' => '2',
                'price' => 120
            ]
        ], $items->whereIn('price', [100, 120])
            ->toArray());
    }

    public function testWhereNotIn()
    {
        $items = new Collection([
            [
                'product' => '1',
                'price' => 100
            ],
            [
                'product' => '2',
                'price' => 120
            ],
            [
                'product' => '3',
                'price' => 80
            ]
        ]);

        $this->assertEquals([
            [
                'product' => '1',
                'price' => 100
            ],
            [
                'product' => '2',
                'price' => 120
            ]
        ], $items->whereNotIn('price', [80])
            ->toArray());
    }

    public function testSort()
    {
        $items = (new Collection([5, 3, 1, 2, 4]))->sort();
        $this->assertEquals([1, 2, 3, 4, 5], $items->values()->all());

        $items = (new Collection(['foo', 'bar-10', 'bar-1']))->sort();
        $this->assertEquals(['bar-1', 'bar-10', 'foo'], $items->values()->all());
    }

    public function testSortBy()
    {
        $items = new Collection(['Bb', 'Cc', 'Aa']);

        $itemsAsc = $items->sortBy(function ($x) {
            return $x;
        });

        $this->assertEquals(['Aa', 'Bb', 'Cc'], array_values($itemsAsc->all()));

        $itemsDesc = $items->sortByDesc(function ($x) {
            return $x;
        });
        $this->assertEquals(['Cc', 'Bb', 'Aa'], array_values($itemsDesc->all()));
    }

    public function testSortByString()
    {
        $items = new Collection([
            [
                'title' => 'Title 2',
            ],
            [
                'title' => 'Title 3',
            ],
            [
                'title' => 'Title 1',
            ],
        ]);
        $this->assertEquals([
                [
                    'title' => 'Title 1',
                ],
                [
                    'title' => 'Title 2',
                ],
                [
                    'title' => 'Title 3',
                ],
            ], $items->sortBy('title')->values()->all());
    }
}
