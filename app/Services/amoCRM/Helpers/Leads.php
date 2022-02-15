<?php

namespace App\Services\amoCRM\Helpers;

use App\Services\amoCRM\Client;

abstract class Leads
{
    public static function search($contact, Client $client, int $pipeline_id)
    {
        if($contact->leads) {

            foreach ($contact->leads as $lead) {

                if ($lead->status_id != 143 &&
                    $lead->status_id != 142 &&
                    $lead->pipeline_id == $pipeline_id) {

                    return $client->service
                        ->leads()
                        ->find($lead->id);
                }
            }
        }
        return null;
    }

    public static function create($contact, Client $client, array $values)
    {
        $lead = $client->service
            ->leads()
            ->create();

        $lead->name = 'Новый посетитель Вебинара';

        $lead->status_id = $values['status_id'];
        $lead->responsible_user_id = $values['response_user_id'];

        $lead->contacts_id = $contact->id;
        $lead->save();

        return $lead;
    }
}
