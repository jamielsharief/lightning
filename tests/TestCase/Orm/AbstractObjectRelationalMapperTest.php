<?php declare(strict_types=1);

namespace Lightning\Test\Repository;

use PDO;
use PHPUnit\Framework\TestCase;
use Lightning\Orm\MapperManager;
use function Lightning\Dotenv\env;
use Lightning\Database\PdoFactory;
use Lightning\Entity\AbstractEntity;
use Lightning\DataMapper\QueryObject;
use Lightning\Entity\EntityInterface;
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

class MockMapper extends AbstractObjectRelationalMapper
{
    public function setAssociation(string $name, array $setting): self
    {
        $this->$name = $setting;

        return $this;
    }

    public function setFields(string $association, string $name, array $fields)
    {
        $this->$association[$name]['fields'] = $fields;
    }

    public function setConditions(string $association, string $name, array $conditions)
    {
        $this->$association[$name]['conditions'] = $conditions;
    }

    public function setOrder(string $association, string $name, $order)
    {
        $this->$association[$name]['order'] = $order;
    }
}

class ArticleEntity extends AbstractEntity
{
    private ?int $id = null;
    private string $title;
    private string $body;
    private ?int $author_id = null;
    private ?string $created_at = null;
    private ?string $updated_at = null;
    private ?EntityInterface $author = null;

    public static function fromState(array $state): self
    {
        $article = new static();

        if (isset($state['id'])) {
            $article->id = $state['id'];
        }

        foreach ($state as $key => $value) {
            $article->$key = $value;
        }

        return $article;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'author_id' => $this->author_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'author' => $this->author ? $this->author->toArray() : null
        ];
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of body
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Set the value of body
     *
     * @param string $body
     *
     * @return self
     */
    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get the value of author_id
     *
     * @return int
     */
    public function getAuthorId(): int
    {
        return $this->author_id;
    }

    /**
     * Set the value of author_id
     *
     * @param int $author_id
     *
     * @return self
     */
    public function setAuthorId(int $author_id): self
    {
        $this->author_id = $author_id;

        return $this;
    }

    /**
     * Get the value of created_at
     *
     * @return ?string
     */
    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     *
     * @param ?string $created_at
     *
     * @return self
     */
    public function setCreatedAt(?string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Get the value of updated_at
     *
     * @return ?string
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    /**
     * Set the value of updated_at
     *
     * @param ?string $updated_at
     *
     * @return self
     */
    public function setUpdatedAt(?string $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * Get the value of author
     *
     * @return ?EntityInterface
     */
    public function getAuthor(): ?EntityInterface
    {
        return $this->author;
    }

    /**
     * Set the value of author
     *
     * @param ?EntityInterface $author
     *
     * @return self
     */
    public function setAuthor(?EntityInterface $author): self
    {
        $this->author = $author;

        return $this;
    }
}

class Article extends MockMapper
{
    protected $primaryKey = 'id';

    protected string $table = 'articles';

    protected array $fields = [
        'id', 'title','body','author_id','created_at','updated_at'
    ];

    protected array $belongsTo = [
        'author' => [
            'class' => Author::class,
            'foreignKey' => 'author_id'
        ]
    ];

    public function mapDataToEntity(array $data): EntityInterface
    {
        return ArticleEntity::fromState($data);
    }
}

class Author extends MockMapper
{
    protected string $table = 'authors';

    protected array $fields = [
        'id', 'name', 'created_at','updated_at'
    ];

    protected array $hasMany = [
        'articles' => [
            'class' => Article::class,
            'foreignKey' => 'author_id', // in other table,
            'dependent' => true
        ]
    ];

    public function setDependent(bool $dependent): void
    {
        $this->hasMany['articles']['dependent'] = $dependent;
    }
}

class Profile extends MockMapper
{
    protected string $table = 'profiles';

    protected array $belongsTo = [
        'user' => [
            'class' => User::class,
            'foreignKey' => 'user_id'
        ]
    ];
}

class User extends MockMapper
{
    protected string $table = 'users';

    protected array $hasOne = [
        'profile' => [
            'class' => Profile::class,
            'foreignKey' => 'user_id', // other table
            'dependent' => true
        ]
    ];

    public function setDependent(bool $dependent): void
    {
        $this->hasOne['profile']['dependent'] = $dependent;
    }
}

class Tag extends MockMapper
{
    protected string $table = 'tags';
}

class Post extends MockMapper
{
    protected string $table = 'posts';

    protected array $belongsToMany = [
        'tags' => [
            'class' => Tag::class,
            'joinTable' => 'posts_tags',
            'foreignKey' => 'post_id',
            'otherForeignKey' => 'tag_id',
        ]
    ];

    public function setDependent(bool $dependent): void
    {
        $this->belongsToMany['tags']['dependent'] = $dependent;
    }
}

final class AbstractObjectRelationalMapperTest extends TestCase
{
    protected PDO $pdo;
    protected FixtureManager $fixtureManager;
    protected DatabaseDataSource $dataSource;

    public function setUp(): void
    {
        $pdoFactory = new PdoFactory();
        $this->pdo = $pdoFactory->create(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'));

        $this->dataSource = new DatabaseDataSource($this->pdo, new QueryBuilder());

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
        $article = new Article($this->dataSource, new MapperManager($this->dataSource));

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

    public function testBelongsToFields(): void
    {
        $article = new Article($this->dataSource, new MapperManager($this->dataSource));

        $article->setFields('belongsTo', 'author', ['id','name']);

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
                'name' => 'Jon'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public function testBelongsToNotFound(): void
    {
        $this->dataSource->delete('authors', new QueryObject([]));

        $article = new Article($this->dataSource, new MapperManager($this->dataSource));

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
        $user = new User($this->dataSource, new MapperManager($this->dataSource));

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

    public function testHasOneFields(): void
    {
        $user = new User($this->dataSource, new MapperManager($this->dataSource));
        $user->setFields('hasOne', 'profile', ['id','name']);

        $result = $user->getBy(['id' => 1000], ['with' => ['profile']]);

        # Important check with array not toJson
        $expected = [
            'id' => 1000,
            'name' => 'User #1',
            'created_at' => '2021-10-14 09:01:00',
            'updated_at' => '2021-10-14 09:02:00',
            'profile' => [
                'id' => 2000,
                'name' => 'admin'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHasOneNotFound(): void
    {
        $this->dataSource->delete('profiles', new QueryObject([]));
        $user = new User($this->dataSource, new MapperManager($this->dataSource));

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
        $user = new User($this->dataSource, new MapperManager($this->dataSource));
        $user->setDependent(true);

        $query = new QueryObject(['user_id' => 1000]);

        $this->assertEquals(1, $this->dataSource->count('profiles', $query));
        $this->assertEquals(1, $user->delete($user->get(new QueryObject(['id' => 1000]))));
        $this->assertEquals(0, $this->dataSource->count('profiles', $query));
    }

    public function testHasMany(): void
    {
        $this->dataSource->update('articles', new QueryObject(['id' => 1002]), ['author_id' => 2000]);

        $author = new Author($this->dataSource, new MapperManager($this->dataSource));

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
                    'updated_at' => '2021-10-03 09:02:00',
                    'author' => null
                ],
                1 => [
                    'id' => 1002,
                    'title' => 'Article #3',
                    'body' => 'A description for article #3',
                    'author_id' => 2000,
                    'created_at' => '2021-10-03 09:05:00',
                    'updated_at' => '2021-10-03 09:06:00',
                    'author' => null
                ]
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHasManyOrder(): void
    {
        $this->dataSource->update('articles', new QueryObject(['id' => 1002]), ['author_id' => 2000]);

        $author = new Author($this->dataSource, new MapperManager($this->dataSource));
        $author->setOrder('hasMany', 'articles', 'id DESC');

        $result = $author->getBy(['id' => 2000], ['with' => ['articles']]);

        $expected = [
            'id' => 2000,
            'name' => 'Jon',
            'created_at' => '2021-10-03 14:01:00',
            'updated_at' => '2021-10-03 14:02:00',
            'articles' => [
                0 => [
                    'id' => 1002,
                    'title' => 'Article #3',
                    'body' => 'A description for article #3',
                    'author_id' => 2000,
                    'created_at' => '2021-10-03 09:05:00',
                    'updated_at' => '2021-10-03 09:06:00',
                    'author' => null
                ],
                1 => [
                    'id' => 1000,
                    'title' => 'Article #1',
                    'body' => 'A description for article #1',
                    'author_id' => 2000,
                    'created_at' => '2021-10-03 09:01:00',
                    'updated_at' => '2021-10-03 09:02:00',
                    'author' => null
                ]
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHasManyFields(): void
    {
        $this->dataSource->update('articles', new QueryObject(['id' => 1002]), ['author_id' => 2000]);

        $author = new Author($this->dataSource, new MapperManager($this->dataSource));
        $author->setFields('hasMany', 'articles', ['id','title','body']);

        $result = $author->getBy(['id' => 2000], ['with' => ['articles']]);

        // This looks incorrect because of fields, but it means that the fields were not selected and hence why it is empty for this type of entity

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
                    'author_id' => null,
                    'created_at' => null,
                    'updated_at' => null,
                    'author' => null
                ],
                1 => [
                    'id' => 1002,
                    'title' => 'Article #3',
                    'body' => 'A description for article #3',
                    'author_id' => null,
                    'created_at' => null,
                    'updated_at' => null,
                    'author' => null
                ]
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHasManyDependent(): void
    {
        $author = new Author($this->dataSource, new MapperManager($this->dataSource));
        $author->setDependent(true);

        $query = new QueryObject(['author_id' => 2000]);

        $this->assertEquals(1, $this->dataSource->count('articles', $query));
        $this->assertEquals(1, $author ->delete($author->get(new QueryObject(['id' => 2000]))));
        $this->assertEquals(0, $this->dataSource->count('articles', $query));
    }

    public function testHasManyNotFound(): void
    {
        $this->dataSource->delete('articles', new QueryObject([]));

        $author = new Author($this->dataSource, new MapperManager($this->dataSource));

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

    public function testBelongsToMany(): void
    {
        // Create extra
        $this->dataSource->update('posts_tags', new QueryObject(['post_id' => 1002]), ['post_id' => 1000]);

        $post = new Post($this->dataSource, new MapperManager($this->dataSource));
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

    public function testBelongsToManyFields(): void
    {
        // Create extra
        $this->dataSource->update('posts_tags', new QueryObject(['post_id' => 1002]), ['post_id' => 1000]);

        $post = new Post($this->dataSource, new MapperManager($this->dataSource));
        $post->setFields('belongsToMany', 'tags', ['id','name']);

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
                    'name' => 'Tag #1'
                ],
                1 => [
                    'id' => 2002,
                    'name' => 'Tag #3'
                ]
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public function testBelongsToManyOrder(): void
    {
        // Create extra
        $this->dataSource->update('posts_tags', new QueryObject(['post_id' => 1002]), ['post_id' => 1000]);

        $post = new Post($this->dataSource, new MapperManager($this->dataSource));

        $post->setAssociation('belongsToMany', ['tags' => [
            'class' => Tag::class,
            'joinTable' => 'posts_tags',
            'foreignKey' => 'post_id',
            'otherForeignKey' => 'tag_id',
            'order' => 'id DESC',
            'conditions' => [],
            'fields' => [],
        ]]);

        $result = $post->getBy(['id' => 1000], ['with' => ['tags']]);

        $expected = [
            'id' => 1000,
            'title' => 'Post #1',
            'body' => 'A description for post #1',
            'created_at' => '2021-10-03 09:01:00',
            'updated_at' => '2021-10-03 09:02:00',
            'tags' => [
                0 => [
                    'id' => 2002,
                    'name' => 'Tag #3',
                    'created_at' => '2021-10-03 09:05:00',
                    'updated_at' => '2021-10-03 09:06:00'
                ],
                1 => [
                    'id' => 2000,
                    'name' => 'Tag #1',
                    'created_at' => '2021-10-03 09:01:00',
                    'updated_at' => '2021-10-03 09:02:00',
                ]
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public function testBelongsToManyNotFound(): void
    {
        $this->dataSource->delete('tags', new QueryObject([]));

        // Create extra
        $this->dataSource->update('posts_tags', new QueryObject(['post_id' => 1002]), ['post_id' => 1000]);

        $post = new Post($this->dataSource, new MapperManager($this->dataSource));
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
        $post = new Post($this->dataSource, new MapperManager($this->dataSource));
        $post->setDependent(true);

        $query = new QueryObject(['post_id' => 1000]);

        $this->assertEquals(2, $this->dataSource->count('posts_tags', $query));
        $this->assertEquals(1, $post ->delete($post->get(new QueryObject(['id' => 1000]))));
        $this->assertEquals(0, $this->dataSource->count('posts_tags', $query));
    }
}
