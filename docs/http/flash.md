# Flash

The Flash component makes it easy to send messages between requests.

```php
$session = new PhpSession(); // This will have to be started
$session->start();

$flash = new Flash($session);

$flash->set('success','Your contact has been saved.');

$bool = $flash->has('success');
$message = $flash->get('success');

$messages = $flash->getMessages();
foreach($flash as $key => $message) {
     echo $message;
}
```