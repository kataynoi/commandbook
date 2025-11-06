<?php
namespace App\Models;
use CodeIgniter\Model;

class ChospitalModel extends Model
{
    protected $table = 'chospital';
    protected $primaryKey = 'hospcode';
    protected $returnType = 'array';
}