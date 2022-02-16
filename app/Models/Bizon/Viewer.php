<?php

namespace App\Models\Bizon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Viewer extends Model
{
    use HasFactory;

    protected $table = 'bizon_viewers';

    protected $fillable = [
        'chatUserId',
        'phone'    ,
        'webinarId',
        'view'     ,
        'time',
        'viewTill' ,
        'email'    ,
        'username' ,
        'roomid'   ,
        'url'      ,
        'ip'       ,
        'useragent',
        'created'  ,
        'playVideo',
        'finished'  ,
        'messages_num',
        'cv'     ,
        'cu1'    ,
        'p1'     ,
        'p2'     ,
        'p3'     ,
        'referer',
        'city'   ,
        'region' ,
        'country',
        'tz'     ,
        'utm_campn',
        'commentaries',
        'lead_id',
        'contact_id',
        'status',
        'webinar_id',
    ];

}
