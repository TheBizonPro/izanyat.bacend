<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignMeUserState extends Model
{
    protected $table = 'signme_user_state';

    public const STATUS_REQUEST_IN_PROGRESS = 'request_in_progress';
    public const STATUS_AWAIT_APPROVE = 'await_approve';
    public const STATUS_APPROVED = 'approved';

    protected $fillable = [
        'user_id',
        'signme_id',
        'status',
        'signme_code',
    ];

    public function user()
    {
        $this->hasOne(User::class, 'id', 'user_id');
    }
}
