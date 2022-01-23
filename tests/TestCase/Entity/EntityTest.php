<?php declare(strict_types=1);

namespace Lightning\Test\Entity;

use Lightning\Entity\Entity;
use PHPUnit\Framework\TestCase;

class User extends Entity
{
    protected array $virtualFields = [
        'full_name'
    ];

    protected array $hiddenFields = [
        'id'
    ];

    protected function setFirstName($name)
    {
        return strtoupper($name);
    }

    protected function getFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    protected function getLastName($name)
    {
        return ucwords($name);
    }

    public function validate(): bool
    {
        return true;
    }
}

final class EntityTest extends TestCase
{
    public function testIsset()
    {
        $article = Entity::fromState(['title' => 'Article']);
        $this->assertTrue($article->has('title'));
        $this->assertTrue(isset($article->title));
        $this->assertTrue(isset($article['title']));

        $this->assertFalse($article->has('name'));
        $this->assertFalse(isset($article->name));
        $this->assertFalse(isset($article['name']));
    }

    public function testUnset()
    {
        $article = Entity::fromState(['title' => 'Article']);
        $article->unset('title');
        $this->assertFalse($article->has('title'));

        $article = Entity::fromState(['title' => 'Article']);
        unset($article->title);
        $this->assertFalse(isset($article->title));

        $article = Entity::fromState(['title' => 'Article']);
        unset($article['title']);
        $this->assertFalse(isset($article['title']));

        unset($article->foo); // Ensure no errors
    }

    public function testGet(): void
    {
        $article = Entity::fromState(['title' => 'Article']);
        $this->assertEquals('Article', $article->get('title'));

        $this->assertEquals('Article', $article->title);

        $this->assertEquals('Article', $article['title']);

        $this->assertNull($article->foo);
    }

    public function testSet(): void
    {
        $article = new Entity();
        $this->assertInstanceOf(Entity::class, $article->set('title', 'Foo'));

        $this->assertEquals('Foo', $article->get('title'));

        $article = new Entity();
        $article->id = 1234;
        $this->assertEquals(1234, $article->id);

        $article = new Entity();
        $article['foo'] = 'bar';
        $this->assertEquals('bar', $article['foo']);
    }

    public function testSetArray(): void
    {
        $article = Entity::fromState([
            'title' => 'a',
            'status' => 'b'
        ]);

        $this->assertInstanceOf(Entity::class, $article->set(['title' => 'foo','status' => 'bar']));

        $this->assertEquals(['title' => 'foo','status' => 'bar'], $article->toArray());
    }

    public function testSetError()
    {
        $article = new Entity();
        $this->assertInstanceOf(Entity::class, $article->setError('foo', 'bar'));

        $this->assertEquals(['bar'], $article->getError('foo'));
        $this->assertEquals(['foo' => ['bar']], $article->getErrors());
    }

    /**
     * @depends testSetError
     */
    public function testGetError()
    {
        $article = new Entity();

        $this->assertEquals([], $article->getError('foo'));

        $article->setError('foo', 'bar');
        $this->assertEquals(['bar'], $article->getError('foo'));
    }

    /**
     * @depends testSetError
     */
    public function testGetErrors()
    {
        $article = new Entity();

        $this->assertEquals([], $article->getErrors());

        $article->setError('foo', 'bar');
        $this->assertEquals(['foo' => ['bar']], $article->getErrors());
    }

    /**
     * @depends testSetError
     */
    public function testHasError()
    {
        $article = new Entity();
        $this->assertFalse($article->hasError('foo'));

        $article->setError('foo', 'bar');
        $this->assertTrue($article->hasError('foo'));
    }

    public function testHasErrors()
    {
        $article = new Entity();
        $this->assertFalse($article->hasErrors());

        $article->setError('foo', 'bar');
        $this->assertTrue($article->hasErrors());
    }

    public function testPersisted()
    {
        $article = new Entity();
        $this->assertTrue($article->isNew());

        $article->markPersisted(true);
        $this->assertFalse($article->isNew());
    }

    public function testState()
    {
        $article = new Entity();
        $this->assertFalse($article->isDirty());
        $this->assertTrue($article->isNew());

        $article = Entity::fromState([
            'title' => 'foo',
            'status' => 'active'
        ]);
        $this->assertFalse($article->isDirty());
        $this->assertTrue($article->isNew());
    }

    /**
     * @depends testState
     */
    public function testIsDirty()
    {
        $article = Entity::fromState([
            'title' => 'foo',
            'status' => 'active'
        ]);

        $this->assertFalse($article->isDirty());
        $this->assertFalse($article->isDirty('status'));
        $this->assertFalse($article->isDirty('foo'));

        $article->status = 'foo';
        $this->assertTrue($article->isDirty());
        $this->assertTrue($article->isDirty('status'));
        $this->assertFalse($article->isDirty('foo'));
    }

    /**
     * @depends testIsDirty
     */
    public function testClean()
    {
        $article = Entity::fromState([
            'title' => 'foo',
            'status' => 'active'
        ]);
        $article->status = 'foo';
        $this->assertTrue($article->isDirty());
        $article->clean();
        $this->assertFalse($article->isDirty());
    }

    public function testAccessorsAndMutation()
    {
        $user = User::fromState(['first_name' => 'mutate','last_name' => 'access']);
        $this->assertEquals('MUTATE Access', $user->full_name); // This tests all 3
    }

    public function testVirtualFields()
    {
        $user = User::fromState(['first_name' => 'mutate','last_name' => 'access']);

        $this->assertContains('full_name', array_keys($user->toArray())) ; // This tests all 3
    }

    public function testToString()
    {
        $this->assertEquals(
            '{"name":"Article"}',
            (string)Entity::fromState(['name' => 'Article'])
        );
    }

    public function testJsonSerializeable()
    {
        $this->assertEquals(
            ['name' => 'Article'],
            Entity::fromState(['name' => 'Article'])->jsonSerialize()
        );
    }

    public function testToArray()
    {
        $article = Entity::fromState(['name' => 'Article']);
        $article->author = Entity::fromState(['name' => 'Jon']);
        $article->tags = [
            Entity::fromState(['title' => 'new'])
        ];

        $this->assertEquals(
           json_decode('{"name":"Article","author":{"name":"Jon"},"tags":[{"title":"new"}]}', true),
            $article->toArray()
        );
    }
}
