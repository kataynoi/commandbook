<?php
// --------------------------------------------------------------------
// (2/4) Model: สร้าง DelPatientModel
// --------------------------------------------------------------------
// สร้างไฟล์ใหม่ทั้งหมดที่: app/Models/DelPatientModel.php

namespace App\Models;

use CodeIgniter\Model;

class DelPatientModel extends Model
{
    protected $table            = 'del_patient';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false; // สำคัญ: เราจะใช้ ID เดิมจากตาราง patients
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id', 'id_card', 'screening_date', 'firstname', 'sex', 'lastname', 
        'birthdate', 'age', 'changwatcode', 'ampurcodefull', 'tamboncodefull', 
        'villagecode', 'house_id', 'lat', 'long', 'address_text', 'phone_number', 
        'main_diagnosis_icd10', 'risk_level_id', 'entry_type_id', 'registrar_id',
        'created_at', 'updated_at', 'deleted_by', 'deleted_at'
    ];
}
?>