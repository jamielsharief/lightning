<?php

use Psr\Http\Message\ServerRequestInterface;

echo json_encode([
    'error' => [
        'code' => $code,
        'message' => $message,
        'hasRequest' => isset($request) && $request instanceof ServerRequestInterface,
        'hasException' => isset($exception) && $exception instanceof Throwable,
    ]
]);
