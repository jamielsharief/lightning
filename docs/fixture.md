# Fixture

Easily add Fixtures to PHPUnit tests. 

## Usage

Load your schema into the test database, then each time you run a test, the tables will be truncated and reinserted for the loaded fixtures.

First create a Fixture, e.g. `tests/Fixture/ContactsFixture`, you will need to set a `table` property.

```php
<?php declare(strict_types=1);

namespace Lightning\Test\Fixture;

use Lightning\Fixture\AbstractFixture as Fixture;

final class MigrationsFixture extends Fixture
{
    protected string $table = 'migrations';

    protected array $records = [
        [
            'id' => 1000,
            'version' => 20210928140841,
            'created_at' => '2021-09-28 16:10:00'
        ]
    ];
}
```

Then in your `phpunit` test case

```php
final class ContactsControllerTest extends TestCase
{
    protected function setUp() : void 
    {
        $pdo = new PDO(getenv('DB_URL'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        $this->fixtureManager = new FixtureManager($pdo);
        $this->fixtureManager->load([ContactsFixture::class]); // this loads the fixtures
    }

    protected function tearDown() : void 
    {
        $this->fixtureManager->unload(); // This will truncate the fixture tables
    }
}
```