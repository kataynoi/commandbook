<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ApiConfig extends BaseConfig
{
    public string $APIUrl = 'http://localhost:8081';
    public string $username = 'admin';
    public string $password = '123456';
}