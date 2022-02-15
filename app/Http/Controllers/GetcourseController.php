<?php

namespace App\Http\Controllers;

use App\Models\Getcourse\Lead;
use App\Models\Getcourse\Viewer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetcourseController extends Controller
{
    //43578895 регистрация на веб
    //45360766 заявка на подарок
    //45360769 заявка на курс
    //45673804 холодные
    //46180171 0 - 10
    //46180174 10 - 30
    //46180177 30 - 60
    //46180180 60+

    //hook заявки с формы регистрации или кнопки на вебинаре
    public function forms(Request $request)
    {
        $lead = Lead::create([
            'name'  => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'state' => $request->input('state'),
        ]);

        switch ($lead->state) {

            case 'Заказ ГК' :
                $status_id = 45360769;
                break;
            case 'Заявка на подарок' :
                $status_id = 45360766;
                break;
            case 'Регистрация' :
                $status_id = 43578895;
                break;
            default :
                $status_id = 0;
        }
        /*
         * в зависимости от state проделываем манипуляции
         * заявки на курсы/подарки выше, чем обычные и присутствие на вебе
         */

    }

    //https://hub.blackclever.ru/arslanova/public/api/getcourse/webinars/hook?name={object.first_name}&email={object.email}&phone={object.phone}
    //hook нахождения на вебинаре
    public function hook(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $viewer = Viewer::where('phone', $request->input('phone'))
            ->where('webinar_date', Carbon::now()->format('Y-m-d'))
            ->first();

        if ($viewer) {
            $viewer->time = $viewer->time + 5;
            $viewer->save();
        } else {
            Viewer::create([
                'webinar_date' => Carbon::now()->format('Y-m-d'),
                'name'  => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
            ]);
        }
    }

    public function send()
    {
        //TODO тег автовеб
        $webinar = Viewer::where('webinar_date', Carbon::now()->format('Y-m-d'))
            ->where('status', '!=', 'ok')
            ->where('created_at', '<', Carbon::now()->subHour()->format('Y-m-d H:i:s'))
            ->latest();//TODO ток последний

        if($webinar) {

            $viewers = Viewer::query()
                ->where('webinar_date', Carbon::now()->format('Y-m-d'))
                ->where('status', '!=', 'ok')
                ->groupBy('phone')
                ->get();

            dd($viewers);
        }
    }
}
