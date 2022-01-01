<?php declare(strict_types=1);

namespace Lightning\Test\Fixture;

use Lightning\Fixture\AbstractFixture;

final class ProfilesFixture extends AbstractFixture
{
    protected string $table = 'profiles';

    protected array $records = [
        [
            'id' => 2000,
            'name' => 'admin',
            'user_id' => 1000,
            'created_at' => '2021-10-03 14:01:00',
            'updated_at' => '2021-10-03 14:02:00',
        ],
        [
            'id' => 2001,
            'name' => 'standard',
            'user_id' => 1001,
            'created_at' => '2021-10-03 14:03:00',
            'updated_at' => '2021-10-03 14:04:00',
        ],
        [
            'id' => 2002,
            'name' => 'super',
            'user_id' => 1002,
            'created_at' => '2021-10-03 14:05:00',
            'updated_at' => '2021-10-03 14:06:00',
        ],
    ];
}
