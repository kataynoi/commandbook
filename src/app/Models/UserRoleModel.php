<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UserRoleModel
 * * Model นี้ทำหน้าที่เป็นตัวแทนของตารางเชื่อม (pivot table) 'user_roles'
 * * ซึ่งใช้สำหรับเชื่อมโยงผู้ใช้ (users) กับสิทธิ์ (roles) ที่ได้รับ
 * * ทำให้เกิดความสัมพันธ์แบบ Many-to-Many (ผู้ใช้หนึ่งคนมีได้หลายสิทธิ์)
 */
class UserRoleModel extends Model
{
    /**
     * ชื่อตารางที่ Model นี้เชื่อมต่ออยู่
     *
     * @var string
     */
    protected $table = 'user_roles';

    /**
     * Primary Key ของตารางเชื่อมนี้เป็น Composite Key (user_id, role_id)
     * CodeIgniter สามารถจัดการกับ Composite Key ได้ในการทำงานส่วนใหญ่
     * เราจึงไม่จำเป็นต้องกำหนด $primaryKey โดยตรง
     *
     * @var array
     */
    // protected $primaryKey = ['user_id', 'role_id']; // ใช้สำหรับอ้างอิง

    /**
     * ประเภทข้อมูลที่ถูกส่งกลับมาจากฟังก์ชัน find* ต่างๆ
     *
     * @var string
     */
    protected $returnType = 'array';

    /**
     * รายชื่อฟิลด์ที่อนุญาตให้ทำการบันทึกข้อมูลผ่าน Model นี้ได้
     *
     * @var array
     */
    protected $allowedFields = [
        'user_id',
        'role_id'
    ];

    /**
     * ตารางนี้ไม่ได้ใช้ timestamps (created_at, updated_at)
     *
     * @var bool
     */
    protected $useTimestamps = false;

    /**
     * ค้นหาสิทธิ์ทั้งหมดที่ผู้ใช้คนหนึ่งมี
     *
     * @param int $userId ไอดีของผู้ใช้
     * @return array trả về một mảng chứa ID của các vai trò
     */
    public function getRolesForUser(int $userId): array
    {
        $roles = $this->where('user_id', $userId)->findAll();
        
        // ส่งกลับค่าเป็น array ที่มีเฉพาะค่า role_id เท่านั้น
        return array_column($roles, 'role_id');
    }

    /**
     * กำหนดสิทธิ์ให้กับผู้ใช้
     *
     * @param int $userId ไอดีของผู้ใช้
     * @param int $roleId ไอดีของสิทธิ์
     * @return bool คืนค่า true เมื่อสำเร็จ, false เมื่อล้มเหลว
     */
    public function assignRoleToUser(int $userId, int $roleId): bool
    {
        $data = [
            'user_id' => $userId,
            'role_id' => $roleId
        ];

        // ใช้ 'ignore(true)' เพื่อป้องกันข้อผิดพลาดกรณีที่สิทธิ์นั้นถูกกำหนดให้ผู้ใช้แล้ว
        return $this->ignore(true)->insert($data);
    }

    /**
     * ลบสิทธิ์ออกจากผู้ใช้
     *
     * @param int $userId ไอดีของผู้ใช้
     * @param int $roleId ไอดีของสิทธิ์
     * @return bool คืนค่า true เมื่อสำเร็จ, false เมื่อล้มเหลว
     */
    public function removeRoleFromUser(int $userId, int $roleId): bool
    {
        return $this->where('user_id', $userId)
                    ->where('role_id', $roleId)
                    ->delete();
    }

    public function syncRolesForUser(int $userId, array $roles = [])
    {
        // 1. ลบสิทธิ์เก่าทั้งหมดของผู้ใช้นี้ออกไปก่อน
        $this->where('user_id', $userId)->delete();

        // 2. ถ้า Array ของสิทธิ์ใหม่ที่ส่งมาไม่ว่างเปล่า
        if (!empty($roles)) {
            $dataToInsert = [];
            // วนลูปเพื่อเตรียมข้อมูลสำหรับเพิ่มลงฐานข้อมูล
            foreach ($roles as $roleId) {
                $dataToInsert[] = [
                    'user_id' => $userId,
                    'role_id' => $roleId
                ];
            }
            // 3. เพิ่มข้อมูลสิทธิ์ใหม่ทั้งหมดในครั้งเดียว (มีประสิทธิภาพมากกว่า)
            $this->insertBatch($dataToInsert);
        }
    }
}
