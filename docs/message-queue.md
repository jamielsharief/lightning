# Message Queue (MQ)

This component provides a messaging queue, using `Redis`, database (Postgres, MySQL, or Sqlite) or an in memory version (for testing).

## Usage

First create the message queue object 

```php
$queue = new MemoryMessageQueue();
```

Create your message object that you want to send

```php
class Message
{
  public function __construct(protected string $body)
  {
  }

  public function getBody() : string 
  {
    return $this->body
  }
}

```


To send the message to the queue called `default`

```php
$queue->send('default',new Message('jon@bloggs.co.uk')); // This will probably be in service or controller
```

Then to receive messages from the `default` queue

```php
$message = $queue->receive('default'); // This will be in a cron job somewhere
```

## Message Queues

## Redis

Create the `RedisMessageQueue` object

```php
$redis = new Redis();
$redis->pconnect('redis', 6379);

$queue = new RedisMessageQueue($redis);
```

## Database Message Queue (PDO)

Create the database table

### MySQL

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

### Sqlite

```sql
CREATE TABLE queue (
    id INTEGER PRIMARY KEY AUTOINCREMENT, 
    body TEXT NOT NULL,
    queue TEXT NOT NULL,
    scheduled DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### Postgres

> With `Postgres` , serialized PHP objects might have null byte characters such as when using private properties, this becomes a problem for `Postgres` as the data would have to be `BYTEA` and not a string. Therefore when using `Postgres` as the backend, the serialized string will be encoded/decoded with `base64`, so either avoid private properties or create custome serialization which encodes/decodes the object.

```sql
CREATE TABLE queue (
  id SERIAL PRIMARY KEY,
  body TEXT,
  queue VARCHAR(100) NOT NULL,
  scheduled TIMESTAMP(0) NOT NULL,
  created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP
);
```