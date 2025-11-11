<?php namespace App\Models;
use CodeIgniter\Model;

class CommandDownloadModel extends Model
{
    protected $table = 'command_downloads';
    protected $allowedFields = ['command_id', 'user_id', 'hospcode', 'ip_address'];
}