<?php declare(strict_types=1);

namespace Lightning\Test\Repository;

use PDO;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;
use Lightning\Database\PdoFactory;
use Lightning\DataMapper\QueryObject;
use Lightning\Fixture\FixtureManager;
use Lightning\Test\Fixture\TagsFixture;
use Lightning\QueryBuilder\QueryBuilder;
use Lightning\Test\Fixture\PostsFixture;
use Lightning\Test\Fixture\UsersFixture;
use Lightning\Test\Fixture\AuthorsFixture;
use Lightning\Test\Fixture\ArticlesFixture;
use Lightning\Test\Fixture\ProfilesFixture;
use Lightning\Test\Fixture\PostsTagsFixture;
use Lightning\Orm\AbstractObjectRelationalMapper;
use Lightning\DataMapper\DataSource\DatabaseDataSource;

class Article extends AbstractObjectRelationalMapper
{
    protected $primaryKey = 'id';

    protected string $table = 'articles';

    protected array $fields = [
        'id', 'title','body','author_id','created_at','updated_at'
    ];

    protected array $belongsTo = [
        'author' => [
            'className' => Author::class,
            'foreignKey' => 'author_id'
        ]
    ];
}

class Author extends AbstractObjectRelationalMapper
{
    protected string $table = 'authors';

    protected array $fields = [
        'id', 'name', 'created_at','updated_at'
    ];

    protected array $hasMany = [
        'articles' => [
            'className' => Article::class,
            'foreignKey' => 'author_id', // in other table,
            'dependent' => true
        ]
    ];

    public function setDependent(bool $dependent): void
    {
        $this->hasMany['articles']['dependent'] = $dependent;
    }
}

class Profile extends AbstractObjectRelationalMapper
{
    protected string $table = 'profiles';

    protected array $belongsTo = [
        'user' => [
            'className' => User::class,
            'foreignKey' => 'user_id'
        ]
    ];
}

class User extends AbstractObjectRelationalMapper
{
    protected string $table = 'users';

    protected array $hasOne = [
        'profile' => [
            'className' => Profile::class,
            'foreignKey' => 'user_id', // other table
            'dependent' => true
        ]
    ];

    public function setDependent(bool $dependent): void
    {
        $this->hasOne['profile']['dependent'] = $dependent;
    }
}

class Tag extends AbstractObjectRelationalMapper
{
    protected string $table = 'tags';
}

class Post extends AbstractObjectRelationalMapper
{
    protected string $table = 'posts';

    protected array $hasAndBelongsToMany = [
        'tags' => [
            'className' => Tag::class,
            'table' => 'posts_tags',
            'foreignKey' => 'post_id',
            'localKey' => 'tag_id',
        ]
    ];

    public function setDependent(bool $dependent): void
    {
        $this->hasAndBelongsToMany['tags']['dependent'] = $dependent;
    }
}

final class AbstractObjectRelationalMapperTest extends TestCase
{
    protected PDO $pdo;
    protected FixtureManager $fixtureManager;
    protected DatabaseDataSource $storage;

    public function setUp(): void
    {
        $pdoFactory = new PdoFactory();
        $this->pdo = $pdoFactory->create(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'));

        $this->storage = new DatabaseDataSource($this->pdo, new QueryBuilder());

        $this->fixtureManager = new FixtureManager($this->pdo);
        $this->fixtureManager->load([
            ArticlesFixture::class,
            AuthorsFixture::class,
            UsersFixture::class,
            TagsFixture::class,
            ProfilesFixture::class,
            PostsFixture::class,
            PostsTagsFixture::class,
        ]);
    }

    public function testBelongsTo(): void
    {
        $article = new Article($this->storage);

        $result = $article->getBy(['id' => 1000], ['with' => ['author']]);

        # Important check with array not toJson
        $expected = [
            'id' => 1000,
            'title' => 'Article #1',
            'body' => 'A description for article #1',
            'author_id' => 2000,
            'created_at' => '2021-10-03 09:01:00',
            'updated_at' => '2021-10-03 09:02:00',
            'author' => [
                'id' => 2000,
                'name' => 'Jon',
                'created_at' => '2021-10-03 14:01:00',
                'updated_at' => '2021-10-03 14:02:00'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public function testBelongsToNotFound(): void
    {
        $this->storage->delete('authors', new QueryObject([]));

        $article = new Article($this->storage);

        $result = $article->getBy(['id' => 1000], ['with' => ['author']]);
        $expected = [
            'id' => 1000,
            'title' => 'Article #1',
            'body' => 'A description for article #1',
            'author_id' => 2000,
            'created_at' => '2021-10-03 09:01:00',
            'updated_at' => '2021-10-03 09:02:00',
            'author' => null
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHasOne(): void
    {
        $user = new User($this->storage);

        $result = $user->getBy(['id' => 1000], ['with' => ['profile']]);

        # Important check with array not toJson
        $expected = [
            'id' => 1000,
            'name' => 'User #1',
            'created_at' => '2021-10-14 09:01:00',
            'updated_at' => '2021-10-14 09:02:00',
            'profile' => [
                'id' => 2000,
                'name' => 'admin',
                'user_id' => 1000,
                'created_at' => '2021-10-03 14:01:00',
                'updated_at' => '2021-10-03 14:02:00'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHasOneNotFound(): void
    {
        $this->storage->delete('profiles', new QueryObject([]));
        $user = new User($this->storage);

        $result = $user->getBy(['id' => 1000], ['with' => ['profile']]);

        # Important check with array not toJson
        $expected = [
            'id' => 1000,
            'name' => 'User #1',
            'created_at' => '2021-10-14 09:01:00',
            'updated_at' => '2021-10-14 09:02:00',
            'profile' => null
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHasOneDepenent(): void
    {
        $user = new User($this->storage);
        $user->setDependent(true);

        $query = new QueryObject(['user_id' => 1000]);

        $this->assertEquals(1, $this->storage->count('profiles', $query));
        $this->assertEquals(1, $user->delete($user->get(new QueryObject(['id' => 1000]))));
        $this->assertEquals(0, $this->storage->count('profiles', $query));
    }

    public function testHasManyDependent(): void
    {
        $author = new Author($this->storage);
        $author->setDependent(true);

        $query = new QueryObject(['author_id' => 2000]);

        $this->assertEquals(1, $this->storage->count('articles', $query));
        $this->assertEquals(1, $author ->delete($author->get(new QueryObject(['id' => 2000]))));
        $this->assertEquals(0, $this->storage->count('articles', $query));
    }

    public function testHasManyFoo(): void
    {
        $this->storage->update('articles', new QueryObject(['id' => 1002]), ['author_id' => 2000]);

        $author = new Author($this->storage);

        $result = $author->getBy(['id' => 2000], ['with' => ['articles']]);

        $expected = [
            'id' => 2000,
            'name' => 'Jon',
            'created_at' => '2021-10-03 14:01:00',
            'updated_at' => '2021-10-03 14:02:00',
            'articles' => [
                0 => [
                    'id' => 1000,
                    'title' => 'Article #1',
                    'body' => 'A description for article #1',
                    'author_id' => 2000,
                    'created_at' => '2021-10-03 09:01:00',
                    'updated_at' => '2021-10-03 09:02:00'
                ],
                1 => [
                    'id' => 1002,
                    'title' => 'Article #3',
                    'body' => 'A description for article #3',
                    'author_id' => 2000,
                    'created_at' => '2021-10-03 09:05:00',
                    'updated_at' => '2021-10-03 09:06:00'
                ]
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHasManyNotFound(): void
    {
        $this->storage->delete('articles', new QueryObject([]));

        $author = new Author($this->storage);

        $result = $author->getBy(['id' => 2000], ['with' => ['articles']]);

        $expected = [
            'id' => 2000,
            'name' => 'Jon',
            'created_at' => '2021-10-03 14:01:00',
            'updated_at' => '2021-10-03 14:02:00',
            'articles' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHasAndBelongsToMany(): void
    {
        // Create extra
        $this->storage->update('posts_tags', new QueryObject(['post_id' => 1002]), ['post_id' => 1000]);

        $post = new Post($this->storage);
        $result = $post->getBy(['id' => 1000], ['with' => ['tags']]);

        $expected = [
            'id' => 1000,
            'title' => 'Post #1',
            'body' => 'A description for post #1',
            'created_at' => '2021-10-03 09:01:00',
            'updated_at' => '2021-10-03 09:02:00',
            'tags' => [
                0 => [
                    'id' => 2000,
                    'name' => 'Tag #1',
                    'created_at' => '2021-10-03 09:01:00',
                    'updated_at' => '2021-10-03 09:02:00',
                ],
                1 => [
                    'id' => 2002,
                    'name' => 'Tag #3',
                    'created_at' => '2021-10-03 09:05:00',
                    'updated_at' => '2021-10-03 09:06:00'
                ]
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHasAndBelongsToManyNotFound(): void
    {
        $this->storage->delete('tags', new QueryObject([]));

        // Create extra
        $this->storage->update('posts_tags', new QueryObject(['post_id' => 1002]), ['post_id' => 1000]);

        $post = new Post($this->storage);
        $result = $post->getBy(['id' => 1000], ['with' => ['tags']]);

        $expected = [
            'id' => 1000,
            'title' => 'Post #1',
            'body' => 'A description for post #1',
            'created_at' => '2021-10-03 09:01:00',
            'updated_at' => '2021-10-03 09:02:00',
            'tags' => []
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHasAndBelongsToDependent(): void
    {
        $post = new Post($this->storage);
        $post->setDependent(true);

        $query = new QueryObject(['post_id' => 1000]);

        $this->assertEquals(2, $this->storage->count('posts_tags', $query));
        $this->assertEquals(1, $post ->delete($post->get(new QueryObject(['id' => 1000]))));
        $this->assertEquals(0, $this->storage->count('posts_tags', $query));
    }
}
