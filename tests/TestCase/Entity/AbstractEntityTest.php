<?php declare(strict_types=1);

namespace Lightning\Test\Entity;

use PHPUnit\Framework\TestCase;
use Lightning\Entity\AbstractEntity;

 class Article extends AbstractEntity
 {
     private ?int $id = null;
     private string $title;
     private string $description;

     public static function fromState(array $state): self
     {
         $article = new static();

         if (isset($state['id'])) {
             $article->id = $state['id'];
         }
         $article->setTitle($state['title']);
         $article->setDescription($state['description']);

         return $article;
     }

     public function toArray(): array
     {
         return [
             'id' => $this->id,
             'title' => $this->title,
             'description' => $this->description
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

     public function getDescription(): string
     {
         return $this->description;
     }

     public function setDescription(string $description): self
     {
         $this->description = $description;

         return $this;
     }

     public function getId(): ?int
     {
         return $this->id;
     }
 }

final class AbstractEntityTest extends TestCase
{
    public function testGetState(): void
    {
        $article = new Article();
        $article->setTitle('foo')
            ->setDescription('bar');

        $this->assertEquals(
            ['id' => null,'title' => 'foo','description' => 'bar'],
            $article->toArray()
        );
    }

    public function testFromState(): void
    {
        $article = Article::fromState([
            'id' => 1234,
            'title' => 'foo',
            'description' => 'bar'
        ]);

        $this->assertEquals(
            ['id' => 1234,'title' => 'foo','description' => 'bar'],
            $article->toArray()
        );
    }

    public function testPersisted(): void
    {
        $article = Article::fromState([
            'id' => 1234,
            'title' => 'foo',
            'description' => 'bar'
        ]);

        $this->assertTrue($article->isNew());
        $article->markPersisted(true);
        $this->assertFalse($article->isNew());
    }

    public function testToString(): void
    {
        $article = Article::fromState([
            'id' => 1234,
            'title' => 'foo',
            'description' => 'bar'
        ]);

        $this->assertEquals(
            '{"id":1234,"title":"foo","description":"bar"}',
            $article->toString()
        );
    }
}
