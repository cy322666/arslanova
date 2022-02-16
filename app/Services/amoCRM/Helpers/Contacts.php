<?php

namespace App\Services\amoCRM\Helpers;


use App\Services\amoCRM\Client;

abstract class Contacts
{
    public static function search(string $phone, string $email, Client $client)
    {
        $contacts = null;

        if($phone)
            $contacts = $client->service
                ->contacts()
                ->searchByPhone($phone);

        if(!$contacts->first()) {

            if($email)
                $contacts = $client->service
                    ->contacts()
                    ->searchByEmail($email);
        }

        return $contacts->first() ?? null;
    }

    public static function create(Client $client, array $values)
    {
        $contact = $client->service
            ->contacts()
            ->create();

        $contact->name = $values['name'] ?? 'Неизвестно';

        $contact->responsible_user_id = $values['responsible_user_id'] ?? null;

        if(isset($values['phone']))
            $contact->cf('Телефон')->setValue($values['phone']);

        if(isset($values['email']))
            $contact->cf('Email')->setValue($values['email']);

        $contact->save();

        return $contact;
    }
}
