# Message Queue (MQ)

This component provides a messaging queue, using `Redis`, database (Postgres, MySQL, or Sqlite) or an in memory version (for testing).

## Usage

First create the message queue object 

```php
$queue = new MemoryMessageQueue();
```

You can send any object, but we have also provided a generic `Message` object.

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

## Generic Message object

The `Message` object has the following methods

```php
$message = new Message('hello world!');
$id = $message->getId(); // unique message identifier not associated with storage
$body = $message->getBody(); // message body
$created = $message->getTimestamp(); // when the message was created
```

## Redis Message Queue


Create the `RedisMessageQueue` object

```php
$redis = new Redis();
$redis->pconnect('redis', 6379);

$queue = new RedisMessageQueue($redis);
```

## Database Message Queue

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

With `Postgres` , serialized PHP objects might have null byte characters such as when using private properties, this becomes a problem for `Postgres` as
the data would have to be `BYTEA` and not a string. Therefore when using `Postgres` as the backend, the serialized string will be encoded/decoded with `base64`.

```sql
CREATE TABLE queue (
  id SERIAL PRIMARY KEY,
  body TEXT,
  queue VARCHAR(100) NOT NULL,
  scheduled TIMESTAMP(0) NOT NULL,
  created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP
);
```


## Message Producer

Using the `MessageProducer`, you can swap out the queues being used an hook into the lifecycle `beforeSend` and `afterSend`.

```php
$messageQueue = new MemoryMessageQueue();
$producer = new MessageProducer($messageQueue, 'default');
$producer->send(new Message('foo'),5);
$producer->sentTo('bar',new Message('foo'), 0);
```

## Message Consumer

Using the `MessageConsumer`, you can swap out the queues being used an hook into the lifecycle `afterReceive`.

```php
$messageQueue = new MemoryMessageQueue();
$producer = new MessageConsumer($messageQueue, 'default');
$producer->receive();
$producer->receiveFrom('mailers');
```


## QueueWorker

The `QueueWorkerCommand` command  uses `WorkerInterface` to consume workers from a queue.

Create a file `bin/queue-worker` and run `chmod +x` on it.

```php
#!/usr/bin/env php
<?php

use Lightning\Console\ConsoleIo;
use Lightning\Console\ConsoleApplication;
use Lightning\MessageQueue\MessageConsumer;
use Lightning\Console\ConsoleArgumentParser;
use Lightning\MessageQueue\MemoryMessageQueue;
use Lightning\MessageQueue\Command\QueueWorkerCommand;

include dirname(__DIR__) . '/config/bootstrap_cli.php';

$io = new ConsoleIo();
$parser = new ConsoleArgumentParser();
$application = new ConsoleApplication($io);

$consumer = new MessageConsumer(new MemoryMessageQueue(), 'default');

$command = new QueueWorkerCommand($parser, $io, $consumer);
exit($command->run($argv));
```