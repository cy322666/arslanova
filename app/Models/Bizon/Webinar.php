<?php

namespace App\Models\Bizon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webinar extends Model
{
    use HasFactory;

    protected $table = 'bizon_webinars';

    protected $fillable = [
        'event',
        'roomid',
        'webinarId',
        'stat',
        'len',
        'account_id'
    ];

    public function viewers()
    {
        return $this->hasMany(\App\Models\Bizon\Viewer::class, 'webinar_id', 'id');
    }
}
