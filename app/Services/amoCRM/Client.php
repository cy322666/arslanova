<?php

namespace App\Services\amoCRM;

use App\Services\amoCRM\Helpers\Contacts;
use App\Services\amoCRM\Helpers\Leads;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Ufee\Amo\Base\Storage\Oauth\AbstractStorage;
use Ufee\Amo\Base\Storage\Oauth\FileStorage;
use Ufee\Amo\Base\Models\QueryModel;
use Ufee\Amo\Oauthapi;
use App\Models\Api\Logger;

class Client
{
    public $service;

    /**
     * @throws Exception
     */
    public function init(array $access = []): static
    {
        $this->service = Oauthapi::setInstance([
            'domain' => $access['subdomain'],
            'client_id' => $access['client_id'],
            'client_secret' => $access['client_secret'],
            'redirect_uri' => $access['redirect_uri'],
        ]);

        \Ufee\Amo\Services\Account::setCacheTime(1800);//TODO 3600

        //$this->service->queries->logs(storage_path('amocrm/logs'));//env//TODO
        $this->service->queries->setDelay(0.5);
        $this->service->queries->cachePath(storage_path('amocrm/cache'));//env

        try {

            $this->service->account;

        } catch (Exception $exception) {

            $this->service->fetchAccessToken($access['code']);
        }
        return $this;
    }
}
