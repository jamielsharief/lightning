<?php declare(strict_types=1);

namespace Lightning\Test\Fixture;

use Lightning\Fixture\AbstractFixture;

final class MessageQueueFixture extends AbstractFixture
{
    protected string $table = 'queue';
    protected array $records = [];
}
