<?php

namespace App\Exceptions;

use Exception;

class InsufficientLoyaltyPointsException extends Exception
{
    protected $message = 'Points de fidélité insuffisants';
    protected $code = 400;

    public function __construct(int $required, int $available)
    {
        $this->message = "Points insuffisants. Requis: {$required}, Disponibles: {$available}";
        parent::__construct($this->message, $this->code);
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
            'error_code' => 'INSUFFICIENT_POINTS',
        ], $this->code);
    }
}
