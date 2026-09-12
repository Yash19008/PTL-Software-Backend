<?php
// app/QrSession.php
namespace App;

use Illuminate\Database\Eloquent\Model;

class QrSession extends Model
{
    protected $fillable = [
        'qr_token',
        'is_used',
        'user_id',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
