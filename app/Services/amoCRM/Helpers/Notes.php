<?php

namespace App\Services\amoCRM\Helpers;

use App\Models\Getcourse\Lead;
use App\Models\Getcourse\Viewer;

class Notes
{
    public static function formatGetcourseText(Viewer $viewer)
    {
        $array = [
            'Информация о зрителе',
            '----------------------',
            ' - Имя : ' . $viewer->name,
            ' - Телефон : ' . $viewer->phone,
            ' - Почта : ' . $viewer->email,
            ' - Присутствовал : ' . $viewer->time.' мин',
        ];

        return implode("\n", $array);
    }

    public static function formatGetcourseLead(Lead $lead): string
    {
        $array = [
            'Информация о клиенте',
            '----------------------',
            ' - Имя : ' . $lead->name,
            ' - Телефон : ' . $lead->phone,
            ' - Почта : ' . $lead->email,
        ];

        return implode("\n", $array);
    }

    public static function formatBizonText($webinar, $viewer): string
    {
        $array = [
            'Информация о зрителе',
            '----------------------',
            ' - Вебинар : '.$webinar->room_title,
            ' - Комната : '.$webinar->roomid,
            ' - Вебинар запустился : ' .$viewer->playVideo,
            ' - Ник : ' . $viewer->username,
            ' - Телефон : ' . $viewer->phone,
            ' - Почта : ' . $viewer->email,
            ' - Откуда : ' . $viewer->region,
            ' - Страна : ' . $viewer->country,
            ' - Присутствовал : ' . $viewer->time.' мин',
            ' - Когда зашел : ' . date('Y-m-d H:i:s', $viewer->view),
            ' - Когда вышел : ' . date('Y-m-d H:i:s',$viewer->viewTill),
            ' - Откуда перешел : ' . $viewer->referer,
            ' - Устройство : ' . $viewer->useragent,
            ' - IP : ' . $viewer->ip,
            ' - Присутствовал до конца : ' . $viewer->userFinished,
            ' - Кликал по банеру : ' . $viewer->clickBanner,
            ' - Кликал по кнопке : ' . $viewer->clickFile,
        ];

        return implode("\n", $array);
    }
}
