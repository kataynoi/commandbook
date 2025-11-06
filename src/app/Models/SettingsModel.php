<?php
// --------------------------------------------------------------------
// (3/6) Model: SettingsModel.php
// --------------------------------------------------------------------
// สร้าง Model ใหม่ทั้งหมดที่: app/Models/SettingsModel.php

namespace App\Models;
use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'key';  // Changed from 'id' to 'key'
    protected $useAutoIncrement = false;  // Added because key is not auto-increment
    protected $allowedFields = ['key', 'value'];
    protected $returnType = 'array';

    public function get(string $key, $default = null)
    {
        $setting = $this->where('key', $key)->first();
        return $setting ? $setting['value'] : $default;
    }

    public function saveSetting(string $key, string $value)
    {
        $data = ['key' => $key, 'value' => $value];
        // Use replace to handle both insert and update
        return $this->replace($data);
    }
    
}
?>