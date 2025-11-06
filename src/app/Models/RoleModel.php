<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * RoleModel
 * * Model นี้ทำหน้าที่เป็นตัวแทนของตาราง 'roles' ในฐานข้อมูล
 * * ใช้สำหรับจัดการข้อมูลสิทธิ์การใช้งานต่างๆ ของระบบ
 */
class RoleModel extends Model
{
    /**
     * ชื่อตารางที่ Model นี้เชื่อมต่ออยู่
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * Primary Key ของตาราง
     *
     * @var string
     */
    protected $primaryKey = 'id';

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
        'role_name',
        'role_description'
    ];

    /**
     * ตารางนี้ไม่ได้ใช้ timestamps (created_at, updated_at)
     *
     * @var bool
     */
    protected $useTimestamps = false;
}
