<?php

namespace App\Domains\Order\Domain\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class InvalidOrderStatusTransitionException extends RuntimeException
{
    public function __construct(string|object $fromOrMessage, ?string $toStatus = null, ?string $customMessage = null)
    {
        if ($toStatus === null) {
            $message = $fromOrMessage;
        } else {

            $message = $customMessage ?? "لا يمكن تغيير حالة الطلب من '{$fromOrMessage}' إلى '{$toStatus}'.";
        }
        
        parent::__construct($message);
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}