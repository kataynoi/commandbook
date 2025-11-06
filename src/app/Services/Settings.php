<?php

namespace App\Services;

use App\Models\SettingsModel;

class Settings
{
    protected $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
    }

    public function get(string $key, $default = null)
    {
        return $this->settingsModel->get($key, $default);
    }

    public function set(string $key, string $value)
    {
        return $this->settingsModel->saveSetting($key, $value);
    }
}