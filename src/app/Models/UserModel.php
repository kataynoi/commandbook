<?php
namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users'; // ชื่อตารางในฐานข้อมูล
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'fullname', 'username', 'position','cid', 'password', 'line_user_id', 'status',
        'changwatcode', 'ampurcodefull', 'hospcode', 'villagecodefull',
        'approved_by'
    ];

    // เปิดใช้งาน Timestamps เพื่อให้ created_at, updated_at ทำงานอัตโนมัติ
    protected $useTimestamps = true; 
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // (แนะนำ) เพิ่มฟังก์ชันสำหรับ Hash รหัสผ่านก่อนบันทึก
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * ทำการ Hash รหัสผ่านโดยอัตโนมัติ
     */
    protected function hashPassword(array $data): array
    {
        // ไม่ทำการ Hash ถ้าไม่มีการส่งรหัสผ่านใหม่ (เช่น กรณี Login ด้วย Line)
        if (!isset($data['data']['password']) || empty($data['data']['password'])) {
            unset($data['data']['password']); // ลบรหัสผ่านออกจากข้อมูลที่จะบันทึก
            return $data;
        }
        $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        return $data;
    }
}

?>