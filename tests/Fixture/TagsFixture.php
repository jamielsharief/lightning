<?php declare(strict_types=1);

namespace Lightning\Test\Fixture;

use Lightning\Fixture\AbstractFixture;

final class TagsFixture extends AbstractFixture
{
    protected string $table = 'tags';

    protected array $records = [
        [
            'id' => 2000,
            'name' => 'Tag #1',
            'created_at' => '2021-10-03 09:01:00',
            'updated_at' => '2021-10-03 09:02:00',
        ],
        [
            'id' => 2001,
            'name' => 'Tag #2',
            'created_at' => '2021-10-03 09:03:00',
            'updated_at' => '2021-10-03 09:04:00',
        ],
        [
            'id' => 2002,
            'name' => 'Tag #3',
            'created_at' => '2021-10-03 09:05:00',
            'updated_at' => '2021-10-03 09:06:00',
        ],
    ];
}
