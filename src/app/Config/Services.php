<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use App\Services\Settings;

class Services extends BaseService
{
    public static function settings($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('settings');
        }

        return new Settings();
    }
}