<?php declare(strict_types=1);

namespace Lightning\Test\Fixture;

use Lightning\Fixture\AbstractFixture;

final class ArticlesFixture extends AbstractFixture
{
    protected string $table = 'articles';

    protected array $records = [
        [
            'id' => 1000,
            'title' => 'Article #1',
            'body' => 'A description for article #1',
            'author_id' => 2000,
            'created_at' => '2021-10-03 09:01:00',
            'updated_at' => '2021-10-03 09:02:00',
        ],
        [
            'id' => 1001,
            'title' => 'Article #2',
            'body' => 'A description for article #2',
            'author_id' => 2001,
            'created_at' => '2021-10-03 09:03:00',
            'updated_at' => '2021-10-03 09:04:00',
        ],
        [
            'id' => 1002,
            'title' => 'Article #3',
            'body' => 'A description for article #3',
            'author_id' => 2002,
            'created_at' => '2021-10-03 09:05:00',
            'updated_at' => '2021-10-03 09:06:00',
        ],
    ];
}
