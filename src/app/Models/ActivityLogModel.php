<?php
// --------------------------------------------------------------------
// (3/6) Model: สร้าง ActivityLogModel
// --------------------------------------------------------------------
// สร้างไฟล์ใหม่ทั้งหมดที่: app/Models/ActivityLogModel.php

namespace App\Models;
use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'user_id', 'action', 'description', 'target_id', 
        'ip_address', 'user_agent'
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at'; // ไม่มี updated_at
}
?>