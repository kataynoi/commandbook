<?php
// --------------------------------------------------------------------
// (2/6) Helper: สร้าง ActivityLogHelper
// --------------------------------------------------------------------
// สร้างไฟล์ใหม่ทั้งหมดที่: app/Helpers/ActivityLogHelper.php

//namespace App\Helpers;

use App\Models\ActivityLogModel;
use CodeIgniter\HTTP\RequestInterface;

if (!function_exists('log_activity')) 
{
    /**
     * บันทึกกิจกรรมของผู้ใช้งานลงในฐานข้อมูล
     *
     * @param string      $action      ประเภทของการกระทำ (เช่น 'create_patient')
     * @param string      $description คำอธิบายกิจกรรม (เช่น 'เพิ่มผู้ป่วยใหม่ชื่อ สมชาย ใจดี')
     * @param int|null    $targetId    ID ของข้อมูลที่เกี่ยวข้อง
     */
    function log_activity(string $action, string $description, ?int $targetId = null)
    {
        $request = \Config\Services::request();
        $session = session();

        // ตรวจสอบว่ามีการ Login อยู่หรือไม่
        if (!$session->get('isLoggedIn')) {
            return; // ไม่ต้องบันทึกถ้ายังไม่ Login
        }

        $logData = [
            'user_id'     => $session->get('user_id'),
            'action'      => $action,
            'description' => $description,
            'target_id'   => $targetId,
            'ip_address'  => $request->getIPAddress(),
            'user_agent'  => substr((string) $request->getUserAgent(), 0, 255),
        ];

        // ใช้ Model ในการบันทึกข้อมูล
        $logModel = new ActivityLogModel();
        $logModel->insert($logData);
    }
}
?>