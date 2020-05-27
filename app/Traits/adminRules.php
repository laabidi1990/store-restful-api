<?php

namespace App\Traits;


trait AdminActions
{
    private function before($user, $rule) 
    {
        if ($user->isAdmin()) {
            return true;
        }
    }
}