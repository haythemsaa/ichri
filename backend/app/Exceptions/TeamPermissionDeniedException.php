<?php

namespace App\Exceptions;

use Exception;

class TeamPermissionDeniedException extends Exception
{
    protected $message = 'Permission d\'équipe refusée';
    protected $code = 403;

    public function __construct(string $permission = '')
    {
        $this->message = $permission
            ? "Permission refusée: {$permission}"
            : 'Permission d\'équipe refusée';

        parent::__construct($this->message, $this->code);
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
            'error_code' => 'TEAM_PERMISSION_DENIED',
        ], $this->code);
    }
}
