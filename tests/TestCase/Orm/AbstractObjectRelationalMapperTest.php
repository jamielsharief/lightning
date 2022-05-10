<?php declare(strict_types=1);

namespace Lightning\Test\Orm;

use PDO;
use LogicException;
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
use Lightning\Test\TestCase\DataMapper\Entity\TagEntity;

abstract class MockMapper extends AbstractObjectRelationalMapper
{
    public function checkAssociationDefinition(string $assoc, array $config): void
    {
        $this->validateAssociationDefinition($assoc, $config);
    }

    public function setAssociation(string $name, array $setting): self
    {
        $this->$name = $setting;

        return $this;
    }

    public function setFields(string $association, string $name, array $fields)
    {
        $this->$association[$this->findIndex($association, $name)]['fields'] = $fields;
    }

    public function setConditions(string $association, string $name, array $conditions)
    {
        $this->$association[$this->findIndex($association, $name)]['conditions'] = $conditions;
    }

    public function setOrder(string $association, string $name, $order)
    {
        $this->$association[$this->findIndex($association, $name)]['order'] = $order;
    }

    private function findIndex(string $association, string $name): ?int
    {
        foreach ($this->$association as $key => $config) {
            if ($config['propertyName'] === $name) {
                return $key;
            }
        }

        return null;
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

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

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
        [
            'className' => Author::class,
            'foreignKey' => 'author_id',
            'propertyName' => 'author'
        ]
    ];

    public function mapDataToEntity(array $data): EntityInterface
    {
        return ArticleEntity::fromState($data);
    }
}

class AuthorEntity extends AbstractEntity
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $created_at = null;
    private ?string $updated_at = null;

    private array $articles = [];

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?string $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of articles
     *
     * @return array
     */
    public function getArticles(): array
    {
        return $this->articles;
    }

    /**
     * Set the value of articles
     *
     * @param array $articles
     *
     * @return self
     */
    public function setArticles(array $articles): self
    {
        $this->articles = $articles;

        return $this;
    }
}

class PostEntity extends AbstractEntity
{
    private ?int $id = null;
    private string $title;
    private string $body;
    private string $created_at;
    private string $updated_at;
    private array $tags = [];

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?string $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @param string $title
     *
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
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
     * Get the value of tags
     *
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Set the value of tags
     *
     * @param array $tags
     *
     * @return self
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }
}

class Author extends MockMapper
{
    protected string $table = 'authors';

    protected array $fields = [
        'id', 'name', 'created_at','updated_at'
    ];

    protected array $hasMany = [
        [
            'className' => Article::class,
            'foreignKey' => 'author_id', // in other table,
            'dependent' => true,
            'propertyName' => 'articles'
        ]
    ];

    public function setDependent(bool $dependent): void
    {
        $this->hasMany[0]['dependent'] = $dependent;
    }

    public function mapDataToEntity(array $data): EntityInterface
    {
        return AuthorEntity::fromState($data);
    }
}

class ProfileEntity extends AbstractEntity
{
    private ?int $id = null;
    private string $name;
    private int $user_id;
    private string $created_at;
    private string $updated_at;

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?string $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of user_id
     *
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * Set the value of user_id
     *
     * @param int $user_id
     *
     * @return self
     */
    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }
}

class Profile extends MockMapper
{
    protected string $table = 'profiles';

    protected array $belongsTo = [
        [
            'className' => User::class,
            'foreignKey' => 'user_id',
            'propertyName' => 'user'
        ]
    ];

    public function mapDataToEntity(array $data): EntityInterface
    {
        return ProfileEntity::fromState($data);
    }
}

class UserEntity extends AbstractEntity
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $created_at = null;
    private ?string $updated_at = null;
    private ?ProfileEntity $profile = null;

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?string $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of profile
     *
     * @return ?ProfileEntity
     */
    public function getProfile(): ?ProfileEntity
    {
        return $this->profile;
    }

    /**
     * Set the value of profile
     *
     * @param ?ProfileEntity $profile
     *
     * @return self
     */
    public function setProfile(?ProfileEntity $profile): self
    {
        $this->profile = $profile;

        return $this;
    }
}

class User extends MockMapper
{
    protected string $table = 'users';

    protected array $hasOne = [
        [
            'className' => Profile::class,
            'foreignKey' => 'user_id', // other table
            'dependent' => true,
            'propertyName' => 'profile'
        ]
    ];

    public function setDependent(bool $dependent): void
    {
        $this->hasOne[0]['dependent'] = $dependent;
    }

    public function mapDataToEntity(array $data): EntityInterface
    {
        return UserEntity::fromState($data); //
    }
}

class Tag extends MockMapper
{
    protected string $table = 'tags';
    public function mapDataToEntity(array $data): EntityInterface
    {
        return TagEntity::fromState($data); //
    }
}

class Post extends MockMapper
{
    protected string $table = 'posts';

    protected array $belongsToMany = [
        [
            'className' => Tag::class,
            'joinTable' => 'posts_tags',
            'foreignKey' => 'post_id',
            'otherForeignKey' => 'tag_id',
            'propertyName' => 'tags'
        ]
    ];

    public function setDependent(bool $dependent): void
    {
        $this->belongsToMany[0]['dependent'] = $dependent;
    }

    public function mapDataToEntity(array $state): EntityInterface
    {
        return PostEntity::fromState($state);
    }
}

/**
 * TODO: Tests that are modifying data in SQLITE is causing tests to fail
 */
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
                'updated_at' => '2021-10-03 14:02:00',
                'articles' => []
            ]
        ];
        $this->assertEquals($expected, $result->toState());
    }

    public function testBelongsToConditions(): void
    {
        $article = new Article($this->dataSource, new MapperManager($this->dataSource));

        $article->setAssociation('belongsTo', [
            [
                'className' => Author::class,
                'foreignKey' => 'author_id',
                'order' => null,
                'fields' => [],
                'conditions' => [
                    'authors.id <>' => 2000
                ],
                'propertyName' => 'author'
            ]

        ]);
        $result = $article->getBy(['id' => 1000], ['with' => ['author']]);

        # Important check with array not toJson
        $expected = [
            'id' => 1000,
            'title' => 'Article #1',
            'body' => 'A description for article #1',
            'author_id' => 2000,
            'created_at' => '2021-10-03 09:01:00',
            'updated_at' => '2021-10-03 09:02:00',
            'author' => null

        ];
        $this->assertEquals($expected, $result->toState());
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
        $this->assertEquals($expected, $result->toState());
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

        $this->assertEquals($expected, $result->toState());
    }

    /**
     * TOOD: rewrite test so its not modifying db since this causes for random errors in the CI matrix with other SQLITE and PHP versions
     */
    public function testHasOneConditions(): void
    {
        // Create Extra Record
        $profile = new Profile($this->dataSource, new MapperManager($this->dataSource));
        $result = $profile->getDataSource()->update('profiles', new QueryObject(), ['user_id' => 1000]);

        $user = new User($this->dataSource, new MapperManager($this->dataSource));

        $user->setAssociation('hasOne', [
            [
                'className' => Profile::class,
                'foreignKey' => 'user_id', // other table
                'dependent' => true,
                'propertyName' => 'profile',
                'conditions' => [
                    'profiles.id <>' => 2000
                ],
                'order' => null,
                'fields' => [],
                'propertyName' => 'profile'

            ]
        ]);

        $result = $user->getBy(['id' => 1000], ['with' => ['profile']]);

        # Important check with array not toJson
        $expected = [
            'id' => 1000,
            'name' => 'User #1',
            'created_at' => '2021-10-14 09:01:00',
            'updated_at' => '2021-10-14 09:02:00',
            'profile' => [
                'id' => 2001,
                'name' => 'standard',
                'user_id' => 1000,
                'created_at' => '2021-10-03 14:03:00',
                'updated_at' => '2021-10-03 14:04:00'
            ]
        ];
        $this->assertEquals($expected, $result->toState());
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
        $this->assertEquals($expected, $result->toState());
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

        $this->assertEquals($expected, $result->toState());
    }

    /**
     * TOOD: rewrite test so its not modifying db since this causes for random errors in the CI matrix with other SQLITE and PHP versions
     */
    public function testHasManyConditions(): void
    {
        $this->dataSource->update('articles', new QueryObject(['id' => 1002]), ['author_id' => 2000]);

        $author = new Author($this->dataSource, new MapperManager($this->dataSource));

        $author->setAssociation('hasMany', [
            [
                'className' => Article::class,
                'foreignKey' => 'author_id', // in other table,
                'dependent' => true,
                'fields' => [],
                'conditions' => ['id <>' => 1000],
                'order' => null,
                'propertyName' => 'articles'
            ]
        ]);

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
                ]
            ]
        ];

        $this->assertEquals($expected, $result->toState());
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

        $this->assertEquals($expected, $result->toState());
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

        $this->assertEquals($expected, $result->toState());
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

        $this->assertEquals($expected, $result->toState());
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
        $this->assertEquals($expected, $result->toState());
    }

    /**
     * TOOD: rewrite test so its not modifying db since this causes for random errors in the CI matrix with other SQLITE and PHP versions
     */
    public function testBelongsToManyConditions(): void
    {
        // Create extra
        $this->dataSource->update('posts_tags', new QueryObject(['post_id' => 1002]), ['post_id' => 1000]);

        $post = new Post($this->dataSource, new MapperManager($this->dataSource));

        $post->setAssociation('belongsToMany', [
            [
                'className' => Tag::class,
                'joinTable' => 'posts_tags',
                'foreignKey' => 'post_id',
                'otherForeignKey' => 'tag_id',
                'conditions' => [
                    'id !=' => 2000,
                ],
                'order' => null,
                'fields' => [],
                'propertyName' => 'tags'
            ]
        ]);

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
                ]
            ]
        ];
        $this->assertEquals($expected, $result->toState());
    }

    /**
     * TODO: This not passing on github actions
     */
    public function testBelongsToManyOrder(): void
    {
        // Create extra
        $this->dataSource->update('posts_tags', new QueryObject(['post_id' => 1002]), ['post_id' => 1000]);

        $post = new Post($this->dataSource, new MapperManager($this->dataSource));

        $post->setAssociation('belongsToMany', [
            [
                'className' => Tag::class,
                'joinTable' => 'posts_tags',
                'foreignKey' => 'post_id',
                'otherForeignKey' => 'tag_id',
                'order' => 'id DESC',
                'conditions' => [],
                'fields' => [],
                'propertyName' => 'tags'
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
        $this->assertEquals($expected, $result->toState());
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
        $this->assertEquals($expected, $result->toState());
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

    public function testInvalidAssociationDefinitionPropertyName(): void
    {
        $post = new Post($this->dataSource, new MapperManager($this->dataSource));

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('belongsTo is missing propertyName');

        $post->checkAssociationDefinition('belongsTo', [
            'className' => Profile::class,
            'foreignKey' => 'user_id', // other table
            'dependent' => true,
            'conditions' => [],
            'order' => null,
            'fields' => []
        ]);
    }

    public function testInvalidAssociationDefinitionForeignKey(): void
    {
        $post = new Post($this->dataSource, new MapperManager($this->dataSource));

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('belongsTo `foo` is missing foreignKey');

        $post->checkAssociationDefinition('belongsTo', [
            'className' => Profile::class,
            'dependent' => true,
            'propertyName' => 'foo',
            'conditions' => [],
            'order' => null,
            'fields' => []
        ]);
    }

    public function testInvalidAssociationDefinitionClassName(): void
    {
        $post = new Post($this->dataSource, new MapperManager($this->dataSource));

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('belongsTo `foo` is missing className');

        $post->checkAssociationDefinition('belongsTo', [
            // 'className' => Profile::class,
            'foreignKey' => 'user_id', // other table
            'dependent' => true,
            'propertyName' => 'foo',
            'conditions' => [],
            'order' => null,
            'fields' => []
        ]);
    }

    public function testInvalidAssociationDefinitionJoinTable(): void
    {
        $post = new Post($this->dataSource, new MapperManager($this->dataSource));

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('belongsToMany `tags` is missing joinTable');

        $post->checkAssociationDefinition('belongsToMany', [
            'className' => Tag::class,
            'foreignKey' => 'post_id',
            'otherForeignKey' => 'tag_id',
            'conditions' => [],
            'order' => null,
            'fields' => [],
            'propertyName' => 'tags'
        ]);
    }

    public function testInvalidAssociationDefinitionOtherForeignKey(): void
    {
        $post = new Post($this->dataSource, new MapperManager($this->dataSource));

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('belongsToMany `tags` is missing otherForeignKey');

        $post->checkAssociationDefinition('belongsToMany', [
            'className' => Tag::class,
            'joinTable' => 'posts_tags',
            'foreignKey' => 'post_id',
            'conditions' => [],
            'order' => null,
            'fields' => [],
            'propertyName' => 'tags'
        ]);
    }
}
