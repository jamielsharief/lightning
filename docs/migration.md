# Migrations

Lightweight database migrations component.

## Usage

Create the migrations table

```sql
CREATE TABLE migrations (
    id MEDIUMINT NOT NULL AUTO_INCREMENT,
	version BIGINT NOT NULL,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

Create the `Migration` object passing a `PDO` connection and the path where your migrations will be.

> The PDO connection should throw an Exception if the query is invalid

```php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=lightning', 'root', 'secret');
$migration = new Migration($pdo, __DIR__ '/database/migrations');
```


Create the following migration file `database/migrations/v1_initial_setup.sql`

```sql
-- Up
CREATE TABLE posts (
  id int unsigned NOT NULL AUTO_INCREMENT,
  title varchar(50) DEFAULT NULL,
  body text,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- Down
DROP TABLE IF EXISTS posts;
```

You can create another one `database/migrations/v2_add_index_posts.sql`

```sql
-- Up
CREATE INDEX idx_title ON posts (title);
-- Down
ALTER TABLE posts DROP INDEX idx_title;
```

To upgrade your database

```php
$migration->up();

// Use a callback to get current migration being run
$migration->up(function($payload){
    echo $payload['name'];
    var_dump($payload['statements']);
});
```

To downgrade your database one level

```php
$migration->down();


// Use a callback to get current migration being run
$migration->down(function($payload){
    echo $payload['name'];
    var_dump($payload['statements']);
});
```