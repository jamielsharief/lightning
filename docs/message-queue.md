# Message Queue (MQ)

This component provides a messaging queue, using Redis, database or an in memory version (for testing).

## Usage

First create the message queue object 

```php
$queue = new MemoryMessageQueue();
```

You can send any object we have also provided a generic `Message` object.

```php
$message = new Message('hello world!');
```

To send the message to the queue called `default`

```php
$queue->send('default', $message); // This will probably be in service or controller
```

Then to receive messages from the `default` queue

```php
$message = $queue->receive('default'); // This will be in a cron job somewhere
```

## Redis Message Queue


Create the `RedisMessageQueue` object

```php
$redis = new Redis();
$redis->pconnect('redis', 6379);

$queue = new RedisMessageQueue($redis);
```

## Database Message Queue

Create your table

```sql
CREATE TABLE queue (
    id MEDIUMINT NOT NULL AUTO_INCREMENT,
    body TEXT,
    queue VARCHAR(100) NOT NULL,
    scheduled DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

Create the `DatabaseMessageQueue` object

```php
$pdo = new PDO(getenv('DB_URL'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
$queue = new DatabaseMessageQueue($this->pdo, 'queue');
```

For Sqlite.

```sql
CREATE TABLE queue (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT, 
    "body" TEXT NOT NULL,
    "queue" TEXT NOT NULL,
    "scheduled" DATETIME NOT NULL,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

## Generic Message object

The `Message` object has the following methods

```php
$message = new Message('hello world!');
$id = $message->getId(); // unique message identifier not associated with storage
$body = $message->getBody(); // message body
$created = $message->getTimestamp(); // when the message was created
```

