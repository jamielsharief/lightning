<?php declare(strict_types=1);

namespace Lightning\Test\Fixture;

use Lightning\Fixture\AbstractFixture;

final class MigrationsFixture extends AbstractFixture
{
    protected string $table = 'migrations';

    protected array $records = [
        [
            'id' => 1000,
            'version' => 1,
            'created_at' => '2021-09-28 16:10:00'
        ]
    ];
}
