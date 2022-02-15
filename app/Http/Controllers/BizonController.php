<?php

namespace App\Http\Controllers;

use App\Models\Bizon\Viewer;
use App\Models\Bizon\Webinar;
use App\Services\amoCRM\Helpers\Contacts;
use App\Services\amoCRM\Helpers\Leads;
use App\Services\amoCRM\Helpers\Notes;
use App\Services\Bizon365\Client;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BizonController extends Controller
{
    //46180171 0 - 10
    //46180174 10 - 30
    //46180177 30 - 60
    //46180180 60+
    /**
     * @throws GuzzleException
     */
    public function hook(Request $request)
    {
        $webinar = Webinar::create([
            'event'     => $request->input('event'),
            'roomid'    => $request->input('roomid'),
            'webinarId' => $request->input('webinarId'),
            'stat'      => $request->input('stat'), //число зрителей
            'len'       => $request->input('len'),  //длительность вебинара
        ]);

        $bizon = (new Client())
            ->setLogin(env('BIZON_LOGIN'))
            ->setPassword(env('BIZON_PASSWORD'))
            ->auth();

        $info = $bizon->webinar($webinar->webinarId);

        $webinar_title   = $info->room_title;
        $webinar_created = $info->report->created;
        $webinar_group   = $info->report->group;

        $report = json_decode($info->report->report, true);

        $commentariesTS = json_decode($info->report->messages, true);

        foreach ($report['usersMeta'] as $user_key => $user_array) {

            $webinar->viewers()->create([
                'chatUserId' => $user_array['chatUserId'],
                'phone'      => $user_array['phone'],
                'webinarId'  => $user_array['webinarId'],
                'view'       => Carbon::parse()->microsecond($user_array['view'])->format('Y-m-d H:i:s'),
                'viewTill'   => Carbon::parse()->microsecond($user_array['viewTill'])->format('Y-m-d H:i:s'),
                'time'       => (int)round((($user_array['viewTill'] - $user_array['view']) / 1000) / 60),
                'email'      => $user_array['email'],
                'username'   => $user_array['username'],
                'roomid'     => $user_array['roomid'],
                'url'        => $user_array['url'],
                'ip'         => $user_array['ip'],
                'useragent'  => $user_array['useragent'],
                'created'    => $user_array['created'],
                'playVideo'  => $user_array['playVideo'],
                'finished'    => $user_array['finished'] ?? null,
                'messages_num' => $user_array['messages_num'],
                'cv'         => $user_array['cv'],
                'cu1'        => $user_array['cu1'],
                'p1'         => $user_array['p1'],
                'p2'         => $user_array['p2'],
                'p3'         => $user_array['p3'],
                'referer'    => $user_array['referer'],
                'city'       => $user_array['city'] ?? null,
                'region'     => $user_array['region'] ?? null,
                'country'    => $user_array['country'] ?? null,
                'tz'         => $user_array['tz'] ?? null,
                'utm_source' => $user_array['utm_source'] ?? null,
                'utm_medium' => $user_array['utm_medium'] ?? null,
                'utm_campaign' => $user_array['utm_campaign'] ?? null,

                'clickFile'   => $user_array['clickFile'] ?? null,
                'clickBanner' => $user_array['clickBanner'] ?? null,
                'commentaries' => count($commentariesTS[$user_key]) > 0 ? json_encode($commentariesTS[$user_key]) : null,
            ]);
        }

        $webinar->room_title = $webinar_title;
        $webinar->created    = $webinar_created;
        $webinar->group      = $webinar_group;
        $webinar->status     = 'wait';
        $webinar->save();
    }

    /**
     * @throws Exception
     */
    public function send()
    {
        $client = (new \App\Services\amoCRM\Client())->init([
            'domain'        => env('AMOCRM_SUBDOMAIN'),
            'client_id'     => env('AMOCRM_CLIENT_ID'),
            'client_secret' => env('AMOCRM_CLIENT_SECRET'),
            'redirect_uri'  => env('AMOCRM_REDIRECT_URI'),
            'code'          => env('AMOCRM_REDIRECT_CODE'),
        ]);

        $webinar = Webinar::where('status', 'wait')->first();

        if($webinar) {

            foreach ($webinar->viewers as $viewer) {

                try {

                    $contact = Contacts::search($viewer->phone, $viewer->email, $client);

                    if($contact == null)
                        $contact = Contacts::create($client, [
                            'phone' => $viewer->phone,
                            'email' => $viewer->email,
                        ]);

                    $leads = Leads::searchActiveLeads($contact, $client);

                    $status_id = \App\Models\Getcourse\Viewer::getStatusId($viewer->time);

                    if($leads == null) {

                        $lead = Leads::create($contact, $client, [
                            'status_id' => $status_id,
                        ]);
                    } else {

                        foreach ($leads as $lead) {
                            //45360766 заявка на подарок
                            //45360769 заявка на курс
                            if ($lead->status_id != 45360766 ||
                                $lead->status_id != 45360769) {

                                $lead = $client->service->leads()->find($lead->id);
                                $lead->status_id = $status_id;
                                $lead->save();

                                break;
                            }
                        }
                        $lead = Leads::create($contact, $client, [
                            'status_id' => $status_id,
                        ]);
                    }

                    $lead->attachTags([\App\Models\Getcourse\Viewer::getTag($viewer->time), 'живойвеб']);
                    $lead->save();

                    $note = $client->service->notes()->create();
                    $note->note_type = 4;
                    $note->text = Notes::formatBizonText($webinar, $viewer);
                    $note->element_type = 2;
                    $note->element_id = $lead->id;
                    $note->save();

                    $viewer->lead_id = $lead->id;
                    $viewer->contact_id = $contact->id;
                    $viewer->status = 'ok';
                    $viewer->save();

                } catch (Exception $exception) {

                    $viewer->status = $exception->getMessage();
                    $viewer->save();
                }

                $viewer->status = 'ok';
                $viewer->save();
            }

            $webinar->status = 'ok';
            $webinar->save();
        }
    }
}
