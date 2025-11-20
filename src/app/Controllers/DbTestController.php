<?php

namespace App\Controllers;

class DbTestController extends BaseController
{
    public function index()
    {
        // ปิดการแสดงผลของ Debug Toolbar ชั่วคราวสำหรับหน้านี้
        if (ENVIRONMENT === 'development') {
            // --- แก้ไขบรรทัดนี้ให้เป็นวิธีของ CodeIgniter 4 ---
            $app = service('codeigniter');
            if (property_exists($app, 'toolbar')) {
                $app->toolbar->disable();
            }
        }

        header('Content-Type: text/html; charset=utf-8');

        $db = null;
        try {
            // พยายามเชื่อมต่อฐานข้อมูลโดยใช้การตั้งค่า default
            $db = \Config\Database::connect();
            
            // ลอง query ง่ายๆ เพื่อยืนยันการเชื่อมต่อ
            $db->query('SELECT 1');

            echo "<h1><span style='color:green;'>✔</span> เชื่อมต่อฐานข้อมูลสำเร็จ!</h1>";
            // --- แก้ไขการแสดงผลตรงนี้ ---
            echo "<p>แอปพลิเคชันสามารถเชื่อมต่อกับฐานข้อมูล '<strong>" . $db->database . "</strong>' บน Host '<strong>" . $db->hostname . "</strong>' ได้แล้ว</p>";
            echo "<hr>";
            echo "<h3>การตั้งค่าที่ใช้ (จาก .env):</h3>";
            echo "<ul>";
            echo "<li>Hostname: " . env('database.default.hostname') . "</li>";
            echo "<li>Database: " . env('database.default.database') . "</li>";
            echo "<li>Username: " . env('database.default.username') . "</li>";
            echo "</ul>";

        } catch (\Throwable $e) {
            // หากเกิดข้อผิดพลาดในการเชื่อมต่อ
            echo "<h1><span style='color:red;'>❌</span> เชื่อมต่อฐานข้อมูลล้มเหลว!</h1>";
            echo "<p>กรุณาตรวจสอบการตั้งค่าในไฟล์ <strong>.env</strong> ของคุณให้ถูกต้อง</p>";
            echo "<hr>";
            echo "<h3>รายละเอียดข้อผิดพลาด:</h3>";
            echo "<pre style='background-color:#f5f5f5; padding:15px; border:1px solid #ccc; border-radius:5px;'>";
            echo "<strong>Error Code:</strong> " . $e->getCode() . "\n";
            echo "<strong>Error Message:</strong> " . $e->getMessage() . "\n";
            echo "</pre>";
        } finally {
            // ปิดการเชื่อมต่อเสมอถ้ามันถูกเปิด
            if ($db && $db->connID) {
                $db->close();
            }
        }
    }
}