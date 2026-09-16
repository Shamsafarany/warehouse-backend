<?php

namespace App\Domains\Identity\Application\Actions;

use Illuminate\Http\Request;

class LogoutUserAction
{
    public function execute(Request $request): void
    {
        $request->user()->currentAccessToken()->delete();
    }
}