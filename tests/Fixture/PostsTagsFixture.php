<?php declare(strict_types=1);

namespace Lightning\Test\Fixture;

use Lightning\Fixture\AbstractFixture;

final class PostsTagsFixture extends AbstractFixture
{
    protected string $table = 'posts_tags';

    protected array $records = [
        [
            'post_id' => 1000,
            'tag_id' => 2000,
        ],
        [
            'post_id' => 1000,
            'tag_id' => 2002,
        ],
        [
            'post_id' => 1001,
            'tag_id' => 2001,
        ]
    ];
}
