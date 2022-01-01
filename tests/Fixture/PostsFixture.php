<?php declare(strict_types=1);

namespace Lightning\Test\Fixture;

use Lightning\Fixture\AbstractFixture;

final class PostsFixture extends AbstractFixture
{
    protected string $table = 'posts';

    protected array $records = [
        [
            'id' => 1000,
            'title' => 'Post #1',
            'body' => 'A description for post #1',
            'created_at' => '2021-10-03 09:01:00',
            'updated_at' => '2021-10-03 09:02:00',
        ],
        [
            'id' => 1001,
            'title' => 'Post #2',
            'body' => 'A description for post #2',
            'created_at' => '2021-10-03 09:03:00',
            'updated_at' => '2021-10-03 09:04:00',
        ],
        [
            'id' => 1002,
            'title' => 'Post #3',
            'body' => 'A description for post #3',
            'created_at' => '2021-10-03 09:05:00',
            'updated_at' => '2021-10-03 09:06:00',
        ],
    ];
}
