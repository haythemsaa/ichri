<?php

namespace App\Exceptions;

use Exception;

class RewardNotAvailableException extends Exception
{
    protected $message = 'Cette récompense n\'est pas disponible';
    protected $code = 404;

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
            'error_code' => 'REWARD_NOT_AVAILABLE',
        ], $this->code);
    }
}
