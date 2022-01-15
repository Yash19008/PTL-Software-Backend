<?php

namespace App\Traits;

use JWTAuth;

trait TimestampTrait
{

    public static function bootTimestampTrait()
    {
        // static::creating(function ($item) {
        //     $item->created_at = $user->id;
        //     $item->updated_at = $user->id;
        // });
     
        // static::updating(function ($item) {
        //     $item->updated_at = $user->id;
        // });

        // static::deleting(function ($item) {
        //     $item->deleted_at = $user->id;
        // });
    }

}