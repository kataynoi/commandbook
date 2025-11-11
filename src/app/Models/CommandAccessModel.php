<?php namespace App\Models;
use CodeIgniter\Model;

class CommandAccessModel extends Model
{
    protected $table = 'command_access';
    protected $allowedFields = ['command_id', 'hospcode'];
}