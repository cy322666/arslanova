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

    public static function searchActiveLeads($contact, $pipeline_id): ?array
    {
        $leads = $contact->leads->toArray();

        if($leads) {

            foreach ($leads as $lead) {

                if ($lead['status_id'] != 143 &&
                    $lead['status_id'] != 142) {

                    $array_leads[] = $lead;
                }
            }
        }
        return $array_leads ?? null;
    }

    public static function create($contact, Client $client, array $values)
    {
        $lead = $client->service
            ->leads()
            ->create();

        $lead->status_id = $values['status_id'] ?? null;
        $lead->name      = $values['name'] ?? 'Новая сделка';
        $lead->responsible_user_id = $values['responsible_user_id'] ?? null;

        $lead->contacts_id = $contact->id;
        $lead->save();

        return $lead;
    }
}
