<?php

namespace App\Models\Getcourse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Viewer extends Model
{
    use HasFactory;

    protected $fillable = [
        'webinar_date',
        'name',
        'email',
        'phone',
        'status',
        'time',
    ];

    protected $table = 'getcourse_viewers';

    public static function getTag(int $time_view): string
    {
        if($time_view <= 10) {

            return '0-10';
        }
        if($time_view <= 30) {

            return '10-30';
        }
        if($time_view <= 60) {

            return '30-60';
        } else
            return '60+';
    }

    public static function getStatusId(int $time_view): int
    {
        if($time_view < 10) {

            return 46180171;
        }
        if($time_view <= 30) {

            return 46180174;
        }
        if($time_view <= 60) {

            return 46180177;
        } else
            return 46180180;
    }
}
