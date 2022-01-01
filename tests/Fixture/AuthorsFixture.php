<?php declare(strict_types=1);

namespace Lightning\Test\Fixture;

use Lightning\Fixture\AbstractFixture;

final class AuthorsFixture extends AbstractFixture
{
    protected string $table = 'authors';

    protected array $records = [
        [
            'id' => 2000,
            'name' => 'Jon',
            'created_at' => '2021-10-03 14:01:00',
            'updated_at' => '2021-10-03 14:02:00',
        ],
        [
            'id' => 2001,
            'name' => 'Claire',
            'created_at' => '2021-10-03 14:03:00',
            'updated_at' => '2021-10-03 14:04:00',
        ],
        [
            'id' => 2002,
            'name' => 'Tom',
            'created_at' => '2021-10-03 14:05:00',
            'updated_at' => '2021-10-03 14:06:00',
        ],
    ];
}
