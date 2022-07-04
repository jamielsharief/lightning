# Worker

The `Worker` component uses the `MessageQueue` component to defer execution of tasks.

## Queue Worker Command

The `QueueWorkerCommand` consumes messages from the `MessageQueue` and processes jobs that implement `RunableInterface`. The `QueueWorkerCommand` to works a specific queue.

Create a file `bin/queue-worker` and run `chmod +x` on it.

```php
#!/usr/bin/env php
<?php

namespace App\Command;

use Lightning\Logger\Logger;
use Lightning\Console\ConsoleIo;
use Lightning\Worker\MessageListener;
use Lightning\MessageQueue\MessageConsumer;
use Lightning\MessageQueue\MessageProducer;
use Lightning\Console\ConsoleArgumentParser;
use Lightning\Logger\Handler\ConsoleHandler;
use Lightning\MessageQueue\RedisMessageQueue;
use Lightning\Worker\Command\QueueWorkerCommand;

include dirname(__DIR__) . '/config/bootstrap_cli.php';

// Create the Message Queue
$redis = new \Redis();
$redis->pconnect('redis', 6379);
$messageQueue = new RedisMessageQueue($redis);

// Create and configure the Message Consumer
$consumer = new MessageConsumer($messageQueue, 'default');
$messageListener = new MessageListener(new MessageProducer($messageQueue), $consumer);
$consumer->setMessageListener($messageListener);

// Create the PSR-3: Logger
$logger = new Logger('queue');
$logger->addHandler(new ConsoleHandler());
$messageListener->setLogger($logger);

$command = new QueueWorkerCommand(new ConsoleArgumentParser(), new ConsoleIo(), $consumer);
exit($command->run($argv));
```

Now when you execute a worker for a particular queue

```php
root@32ec5221b471:/var/www# bin/queue-worker mailers -d
[2022-07-04 8:40:00] queue DEBUG: App\Command\SendEmailNotification received
[2022-07-04 8:40:00] queue ERROR: App\Command\SendEmailNotification could not connect to SMTP server
[2022-07-04 8:50:00] queue DEBUG: App\Command\SendEmailNotification received
[2022-07-04 8:50:00] queue INFO: App\Command\SendEmailNotification executed
```

## Job

The `AbstractJob` class gives you everything you need to run a background job

```php
class SendEmailNotification extends AbstractJob
{
    // number of times to retry if exception thrown
    protected int $maxRetries = 3;

    // delay in seconds between retries
    protected int $delay = 5;

    /**
     * Use DI here
     */ 
    public function __construct(protected Mailer $mailer) 
    {
    }

    protected function initialize() : void 
    {
    }

    protected function execute(Params $params): void
    {
        $this->mailer->send('welcome-email', $params->get('email'));
    }
}
```

Then create the `Job` and send this using the `MessageProducer`

```php
$job = new SendEmailNotification($mailer);
$job = $job->withParameters(['email'=>'jon@example.com']);

(new MessageProducer(new MemoryQueue()))->send('mailers', $job);
```

When you execute the `QueueWorkerCommand` the job will be executed.