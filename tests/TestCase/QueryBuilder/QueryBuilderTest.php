<?php declare(strict_types=1);

namespace Lightning\Test\QueryBuilder;

use RuntimeException;
use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Lightning\QueryBuilder\QueryBuilder;

final class QueryBuilderTest extends TestCase
{
    public function createBuilder(string $quote = null): QueryBuilder
    {
        return new QueryBuilder($quote);
    }

    public function testInsert()
    {
        $article = [
            'title' => 'Article #' . time(),
            'body' => 'A new article',
            'author_id' => 1234,
            'created_at' => '2021-10-05 19:49:00',
            'updated_at' => '2021-10-05 19:49:00',
        ];

        $builder = $this->createBuilder('`')
            ->insert(array_keys($article))
            ->into('articles')
            ->values(array_values($article));

        $this->assertEquals(
            'INSERT INTO `articles` (`title`, `body`, `author_id`, `created_at`, `updated_at`) VALUES (:v0, :v1, :v2, :v3, :v4)',
            (string) $builder
        );

        $this->assertEquals('insert', $builder->getType());
    }

    public function testDelete()
    {
        $builder = $this->createBuilder('`')
            ->delete()->from('articles');

        $this->assertEquals(
            'DELETE FROM `articles`',
            (string) $builder
        );

        $this->assertEquals('delete', $builder->getType());
    }

    public function testDeleteWithConditions()
    {
        $builder = $this->createBuilder('`')
            ->delete()->from('articles')->where(['id' => 1234,'status' => 'active']);

        $this->assertEquals(
            'DELETE FROM `articles` WHERE `articles`.`id` = :v0 AND `articles`.`status` = :v1',
            (string) $builder
        );
    }

    public function testDeleteWithOrConditions()
    {
        $builder = $this->createBuilder('`')
            ->delete()->from('articles')->where(['id' => 1234])->or(['status' => 'remove']);

        $this->assertEquals(
            'DELETE FROM `articles` WHERE `articles`.`id` = :v0 OR `articles`.`status` = :v1',
            (string) $builder
        );
    }

    public function testUpdate()
    {
        $builder = $this->createBuilder('`')
            ->update('articles')->set(['title' => 'foo']);

        $this->assertEquals(
            'UPDATE `articles` SET `title` = :v0',
            (string) $builder
        );

        $this->assertEquals('update', $builder->getType());
    }

    public function testUpdateWithConditions()
    {
        $builder = $this->createBuilder('`')
            ->update('articles')->set(['title' => 'foo'])->where(['id' => 1234]);

        $this->assertEquals(
            'UPDATE `articles` SET `title` = :v0 WHERE `articles`.`id` = :v1',
            (string) $builder
        );
    }

    public function testSelect()
    {
        $builder = $this->createBuilder();

        $builder->select(['id', 'title', 'description'])->from('articles');

        $this->assertEquals(
            'SELECT articles.id, articles.title, articles.description FROM articles',
            (string) $builder
        );
    }

    public function testSelectWithQuotes()
    {
        $builder = $this->createBuilder('`')
            ->select(['id', 'title AS foo', 'description'])
            ->from('articles');

        // test normal and alias
        $this->assertEquals(
            'SELECT `articles`.`id`, title AS foo, `articles`.`description` FROM `articles`',
            (string) $builder
        );

        // test formula
        $builder = $this->createBuilder('`')
            ->select(['COUNT(id)'])
            ->from('articles');

        $this->assertEquals(
            'SELECT COUNT(id) FROM `articles`',
            (string) $builder
        );
    }

    public function testWhereString()
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id = :id']);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles WHERE id = :id',
            (string) $builder
        );
    }

    public function testInvalidExpression(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id <-o->' => 1234]);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Invalid expression `<-o->`');

        (string) $builder;
    }

    public function testWhereSingle()
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id' => 1234]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id = :v0',
            (string) $builder
        );
    }

    public function testWhereMultiple()
    {
        $builder = $this->createBuilder()
            ->select(['id','name','email'])
            ->from('articles')
            ->where(['id' => 1234,'status' => 'published']);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id = :v0 AND articles.status = :v1',
            (string) $builder
        );
    }

    public function sqlOperatorProvider(): array
    {
        return [
            ['='],
            ['!='],
            ['<>'],
            ['<'],
            ['<='],
            ['>'],
            ['>='],
            ['LIKE'],
            ['NOT LIKE'],
        ];
    }

    /**
     * @dataProvider sqlOperatorProvider
     */
    public function testOperatorsWithSingleValue($operator)
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(["id {$operator}" => 1234]);

        // Adjust for ISO standard
        if ($operator === '!=') {
            $operator = '<>';
        }

        $this->assertEquals(
            "SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id {$operator} :v0",
            (string) $builder
        );
    }

    public function testParameterizedValues()
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id' => 1234]);

        $builder->toString();

        $this->assertEquals(
            [':v0' => 1234],
            $builder->getParameters()
        );

        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id' => 1234,'foo' => 'bar']);
        $builder->toString();

        $this->assertEquals(
            [':v0' => 1234,':v1' => 'bar'],
            $builder->getParameters()
        );
    }

    public function testBetween()
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id BETWEEN' => [1,2]]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id BETWEEN :v0 AND :v1',
            (string) $builder
        );
    }

    public function testNotBetween()
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id NOT BETWEEN' => [1,2]]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id NOT BETWEEN :v0 AND :v1',
            (string) $builder
        );
    }

    public function testIn()
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id IN' => [1,2]]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id IN ( :v0, :v1 )',
            (string) $builder
        );
    }

    public function testInAuto()
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id' => [1,2]]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id IN ( :v0, :v1 )',
            (string) $builder
        );
    }

    public function testNotIn()
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id !=' => [1,2]]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id NOT IN ( :v0, :v1 )',
            (string) $builder
        );

        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id NOT IN' => [1,2]]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id NOT IN ( :v0, :v1 )',
            (string) $builder
        );
    }

    public function testLeftJoin(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->leftJoin('users', null, 'user.id = articles.user_id');

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles LEFT JOIN users ON user.id = articles.user_id',
            (string) $builder
        );
    }

    public function testLeftJoinAlias(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->leftJoin('users', 'u', 'user.id = articles.user_id');

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles LEFT JOIN users AS u ON user.id = articles.user_id',
            (string) $builder
         );
    }

    public function testLeftJoinArray(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->leftJoin('users', null, [
                'articles.created_date >=' => '2021-01-01 09:45:00',
                'user.id = articles.user_id'
            ]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles LEFT JOIN users ON articles.created_date >= :v0 AND user.id = articles.user_id',
            (string) $builder
        );
    }

    public function testRightJoin(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->rightJoin('users', null, 'user.id = articles.user_id');

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles RIGHT JOIN users ON user.id = articles.user_id',
            (string) $builder
        );
    }

    public function testRightJoinAlias(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->rightJoin('users', 'u', 'user.id = articles.user_id');

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles RIGHT JOIN users AS u ON user.id = articles.user_id',
            (string) $builder
         );
    }

    public function testRightJoinArray(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->rightJoin('users', null, [
                'articles.created_date >=' => '2021-01-01 09:45:00',
                'user.id = articles.user_id'
            ]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles RIGHT JOIN users ON articles.created_date >= :v0 AND user.id = articles.user_id',
            (string) $builder
        );
    }

    public function testFullJoin(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->fullJoin('users', null, 'user.id = articles.user_id');

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles FULL JOIN users ON user.id = articles.user_id',
            (string) $builder
        );
    }

    public function testFullJoinAlias(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->fullJoin('users', 'u', 'user.id = articles.user_id');

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles FULL JOIN users AS u ON user.id = articles.user_id',
            (string) $builder
         );
    }

    public function testFullJoinArray(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->fullJoin('users', null, [
                'articles.created_date >=' => '2021-01-01 09:45:00',
                'user.id = articles.user_id'
            ]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles FULL JOIN users ON articles.created_date >= :v0 AND user.id = articles.user_id',
            (string) $builder
        );
    }

    public function testInnerJoin(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->innerJoin('users', null, 'user.id = articles.user_id');

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles INNER JOIN users ON user.id = articles.user_id',
            (string) $builder
        );
    }

    public function testInnerJoinArray(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->innerJoin('users', null, [
                'articles.created_date >=' => '2021-01-01 09:45:00',
                'user.id = articles.user_id'
            ]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles INNER JOIN users ON articles.created_date >= :v0 AND user.id = articles.user_id',
            (string) $builder
        );
    }

    public function testGroupBy(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->groupBy('country');

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles GROUP BY articles.country',
            (string) $builder
        );
    }

    public function testGroupByArray(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->groupBy(['surname', 'country']);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles GROUP BY articles.surname, articles.country',
            (string) $builder
        );
    }

    public function testHaving(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->having('COUNT(id) > 5');

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles HAVING COUNT(id) > 5',
            (string) $builder
        );
    }

    public function testOrderByString(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->orderBy('id DESC');

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles ORDER BY articles.id DESC',
            (string) $builder
        );
    }

    public function testOrderByPair(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->orderBy(['id' => 'DESC']);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles ORDER BY articles.id DESC',
            (string) $builder
        );
    }

    public function testOrderByArray(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->orderBy(['surname', 'country DESC']);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles ORDER BY articles.surname, articles.country DESC',
            (string) $builder
        );
    }

    public function testLimit(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->limit(10);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles LIMIT 10',
            (string) $builder
        );
    }

    public function testLimitOffset(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->limit(10, 20);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles LIMIT 10 OFFSET 20',
            (string) $builder
        );
    }

    public function testSelectWithOr()
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id' => 1234])
            ->or(['status' => 'active']);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id = :v0 OR articles.status = :v1',
            (string) $builder
        );
    }

    public function testSelectWithOrGroup()
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id' => 1234])
            ->or(['status' => 'active','deleted' => null]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id = :v0 OR (articles.status = :v1 AND articles.deleted IS NULL)',
            (string) $builder
        );
    }

    /**
     * @internal bingo.
     */
    public function testSelectWithNestedOr()
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id' => 1234,'OR' => ['status' => 'active']]);

        $this->assertEquals(
                'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id = :v0 OR articles.status = :v1',
                (string) $builder
            );

        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id' => 1234,'OR' => ['status' => 'active','deleted' => null]]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id = :v0 OR (articles.status = :v1 AND articles.deleted IS NULL)',
            (string) $builder
        );
    }

    public function testSelectWithNestedNot()
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['NOT' => ['id' => [123,456]]]);

        $this->assertEquals(
                'SELECT articles.id, articles.name, articles.email FROM articles WHERE NOT articles.id IN ( :v0, :v1 )',
                (string) $builder
            );

        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id' => 1234,'NOT' => ['status' => 'active']]);

        $this->assertEquals(
                'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id = :v0 AND NOT articles.status = :v1',
                (string) $builder
            );

        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id' => 1234,'NOT' => ['status' => 'active','deleted' => null]]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id = :v0 AND NOT (articles.status = :v1 AND articles.deleted IS NULL)',
            (string) $builder
        );
    }

    public function testIsNull(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id' => null]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id IS NULL',
            (string) $builder
        );
    }

    public function testIsNotNull(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id !=' => null]);

        $this->assertEquals(
            'SELECT articles.id, articles.name, articles.email FROM articles WHERE articles.id IS NOT NULL',
            (string) $builder
        );
    }

    public function testErrorParsingException(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id >' => ['foo']]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error parsing expression');

        (string) $builder;
    }

    public function testErrorParsingExceptionLike(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id LIKE' => ['foo']]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error parsing expression');

        (string) $builder;
    }

    public function testErrorParsingExceptionBetween(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id BETWEEN' => 1234]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error parsing expression');

        (string) $builder;
    }

    public function testErrorParsingExceptionIn(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id IN' => 1234]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error parsing expression');

        (string) $builder;
    }

    public function testSetParameter(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id = :id']);

        $this->assertInstanceOf(QueryBuilder::class, $builder->setParameter('id', 123456));
        $this->assertInstanceOf(QueryBuilder::class, $builder->setParameter('status', 'foo'));
    }

    public function testGetParameter(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id = :id']);

        $builder->setParameter('id', 123456);
        $builder->setParameter('status', 'foo');

        $this->assertEquals([
            ':id' => 123456,
            ':status' => 'foo'
        ], $builder->getParameters());
    }

    public function testSetParameters(): void
    {
        $builder = $this->createBuilder()
            ->select(['id', 'name','email'])
            ->from('articles')
            ->where(['id = :id']);

        $this->assertInstanceOf(QueryBuilder::class, $builder->setParameters(['id' => 123456,'status' => 'foo']));
        $this->assertEquals([
            ':id' => 123456,
            ':status' => 'foo'
        ], $builder->getParameters());
    }

    public function testSelectTableNotSet(): void
    {
        $builder = $this->createBuilder()->select(['*']);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Table for the query was not set');

        $builder->toString();
    }

    public function testInsertTableNotSet(): void
    {
        $builder = $this->createBuilder()->insert(['id']);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Table for the query was not set');

        $builder->toString();
    }

    public function testDeleteTableNotSet(): void
    {
        $builder = $this->createBuilder()->delete();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Table for the query was not set');

        $builder->toString();
    }
}
