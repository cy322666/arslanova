<?php

namespace App\Http\Controllers;

use App\Models\Getcourse\Lead;
use App\Models\Getcourse\Viewer;
use App\Services\amoCRM\Helpers\Contacts;
use App\Services\amoCRM\Helpers\Leads;
use App\Services\amoCRM\Helpers\Notes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetcourseController extends Controller
{
    //43578895 регистрация на веб
    //45360766 заявка на подарок
    //45360769 заявка на курс
    //46180171 0 - 10
    //46180174 10 - 30
    //46180177 30 - 60
    //46180180 60+

    //hook заявки с формы регистрации или кнопки на вебинаре
    public function forms(Request $request)
    {
        $user = Lead::create([
            'name'  => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'state' => $request->input('state').'-00',
        ]);

        try {
            $client = (new \App\Services\amoCRM\Client())->init([
                'subdomain'     => env('AMOCRM_SUBDOMAIN'),
                'client_id'     => env('AMOCRM_CLIENT_ID'),
                'client_secret' => env('AMOCRM_CLIENT_SECRET'),
                'redirect_uri'  => env('AMOCRM_REDIRECT_URI'),
                'code'          => env('AMOCRM_CODE'),
            ]);

            $status_id = match ($user->state) {
                'Заказ ГК'          => 45360769,
                'Заявка на подарок' => 45360766,
                default             => 43578895,
            };

            $contact = Contacts::search($user->phone, $user->email, $client);

            if($contact == null)
                $contact = Contacts::create($client, [
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'name'  => $user->name,
                ]);

            $leads = Leads::searchActiveLeads($contact, $client);

            if($leads == null) {

                $lead = Leads::create($contact, $client, [
                    'status_id' => $status_id,
                    'name'      => 'Новая регистрация на вебинар',
                ]);
            } else {

                foreach ($leads as $lead) {
                    //45360766 заявка на подарок
                    //45360769 заявка на курс
                    if ($lead['status_id'] == 45360766 ||
                        $lead['status_id'] == 45360769) {

                        $lead = $client->service->leads()->find($lead['id']);

                        break;

                    } else {

                        $lead = $client->service->leads()->find($lead['id']);
                        $lead->status_id = $status_id;
                        $lead->save();

                        break;
                    }
                }
                if(empty($lead)) {

                    $lead = Leads::create($contact, $client, [
                        'status_id' => $status_id,
                        'name'      => 'Новая регистрация на вебинар'
                    ]);
                }
            }

            $lead->attachTags([$user->state]);
            $lead->save();

            $note = $client->service->notes()->create();

            $note->note_type = 4;
            $note->text = Notes::formatGetcourseLead($user);
            $note->element_type = 2;
            $note->element_id = $lead->id;
            $note->save();

            $user->lead_id    = $lead->id;
            $user->status_id  = $lead->status_id;
            $user->contact_id = $contact->id;
            $user->status = 'ok';
            $user->save();

        } catch (\Exception $exception) {

            $user->status = $exception->getMessage();
            $user->save();
        }
    }

    //hook нахождения на вебинаре
    public function hook(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $viewer = Viewer::where('phone', $request->input('phone'))
            ->where('status', 'wait')
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
                'status'=> 'wait',
                'time'  => 5,
            ]);
        }
    }

    //крон 1 раз в 30 мин
    public function send()
    {
        $webinar = Viewer::query()
            ->where('webinar_date', Carbon::now()->format('Y-m-d'))
            ->where('status', '!=', 'ok')
            ->where('created_at', '<', Carbon::now()->timezone('Europe/Moscow')->subMinutes(90)->format('Y-m-d H:i:s'))
            ->first();

        if($webinar) {

            $client = (new \App\Services\amoCRM\Client())->init([
                'subdomain'     => env('AMOCRM_SUBDOMAIN'),
                'client_id'     => env('AMOCRM_CLIENT_ID'),
                'client_secret' => env('AMOCRM_CLIENT_SECRET'),
                'redirect_uri'  => env('AMOCRM_REDIRECT_URI'),
                'code'          => env('AMOCRM_REDIRECT_CODE'),
            ]);

            $viewers = Viewer::query()
                ->where('webinar_date', Carbon::now()->format('Y-m-d'))
                ->where('status', '!=', 'ok')
                ->limit(25)
                ->get();

            foreach ($viewers as $viewer) {

                try {
                    $contact = Contacts::search($viewer->phone, $viewer->email, $client);

                    if($contact == null)
                        $contact = Contacts::create($client, [
                            'phone' => $viewer->phone,
                            'email' => $viewer->email,
                        ]);

                    $status_id = Viewer::getStatusId($viewer->time);

                    $leads = Leads::searchActiveLeads($contact, $client);

                    if($leads == null) {

                        $lead = Leads::create($contact, $client, [
                            'status_id' => $status_id,
                        ]);
                    } else {


                        foreach ($leads as $lead) {
                            //45360766 заявка на подарок
                            //45360769 заявка на курс
                            if ($lead['status_id'] == 45360766 ||
                                $lead['status_id'] == 45360769) {

                                $lead = $client->service->leads()->find($lead['id']);

                                break;
                            }

                            if ($lead['status_id'] != 45360766 ||
                                $lead['status_id'] != 45360769) {

                                $lead = $client->service->leads()->find($lead['id']);
                                $lead->status_id = $status_id;
                                $lead->save();

                                break;
                            }
                        }
                        if(empty($lead)) {

                            $lead = Leads::create($contact, $client, [
                                'status_id' => $status_id,
                                'name'      => 'Новый посетитель автовебинара'
                            ]);
                        }
                    }

                    $lead->attachTags([Viewer::getTag($viewer->time), 'автовеб']);
                    $lead->save();

                    $note = $client->service->notes()->create();
                    $note->note_type = 4;
                    $note->text = Notes::formatGetcourseText($viewer);
                    $note->element_type = 2;
                    $note->element_id = $lead->id;
                    $note->save();

                    $viewer->lead_id = $lead->id;
                    $viewer->contact_id = $contact->id;
                    $viewer->status = 'ok';
                    $viewer->save();

                } catch (\Exception $exception) {

                    $viewer->status = $exception->getMessage();
                    $viewer->save();
                }
            }
        }
    }
}
