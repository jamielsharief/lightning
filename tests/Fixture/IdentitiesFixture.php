<?php declare(strict_types=1);

namespace Lightning\Test\Fixture;

use Lightning\Fixture\AbstractFixture;

final class IdentitiesFixture extends AbstractFixture
{
    protected string $table = 'identities';

    protected array $records = [
        [
            'id' => 1000,
            'username' => 'user1@example.com',
            'password' => '$2y$10$R98wqv8DEHIIzYsEKZTzI.My5hCrjNRarLNleIb2Rk6Mw013cJQsG', // 1234
            'created_at' => '2021-10-14 09:01:00',
            'updated_at' => '2021-10-14 09:02:00',
        ],

        [
            'id' => 1001,
            'username' => 'user2@example.com',
            'password' => 'acbd18db4cc2f85cedef654fccc4a4d8', // md5(foo)
            'created_at' => '2021-10-14 09:01:00',
            'updated_at' => '2021-10-14 09:02:00',
        ]
    ];
}
