<?php
namespace App\Models;
use CodeIgniter\Model;

class CampurModel extends Model
{
    protected $table = 'campur';
    protected $primaryKey = 'ampurcodefull';
    protected $returnType = 'array';
}