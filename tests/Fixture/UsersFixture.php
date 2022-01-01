<?php declare(strict_types=1);

namespace Lightning\Test\Fixture;

use Lightning\Fixture\AbstractFixture;

final class UsersFixture extends AbstractFixture
{
    protected string $table = 'users';

    protected array $records = [
        [
            'id' => 1000,
            'name' => 'User #1',
            'created_at' => '2021-10-14 09:01:00',
            'updated_at' => '2021-10-14 09:02:00',
        ],
        [
            'id' => 1001,
            'name' => 'User #2',
            'created_at' => '2021-10-14 09:03:00',
            'updated_at' => '2021-10-14 09:04:00',
        ],
        [
            'id' => 1002,
            'name' => 'User #3',
            'created_at' => '2021-10-14 09:05:00',
            'updated_at' => '2021-10-14 09:06:00',
        ],
    ];
}
