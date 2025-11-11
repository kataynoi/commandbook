<?php namespace App\Controllers;

use App\Models\CommandDocumentModel;
use App\Models\CommandAccessModel;
use App\Models\CommandDownloadModel; // !! ตัวบันทึก Log

class Download extends BaseController
{
    protected $session;

    public function __construct()
    {
        // ต้องมั่นใจว่าระบบ Line Login ของคุณ
        // เก็บ 'user_id' และ 'hospcode' ของคนที่ login ไว้ใน session
        $this->session = \Config\Services::session(); 
    }

    /**
     * Method นี้จะทำงานเมื่อ User สแกน QR Code
     */
    public function file($qr_token)
    {
        // 1. ตรวจสอบว่า Login หรือยัง (ผ่าน Line Login)
        if (!$this->session->get('isLoggedIn')) {
            // ถ้ายังไม่ Login, ให้ไปหน้า Login ก่อน
            // (หลังจาก Login สำเร็จ ต้องเด้งกลับมา URL นี้)
            return redirect()->to('auth/login?redirect_url=' . urlencode(current_url()));
        }

        // 2. ดึงข้อมูล User ที่ Login อยู่
        $userId = $this->session->get('user_id');
        $userHospcode = $this->session->get('hospcode'); // รหัสหน่วยงานของ User

        // 3. ค้นหาเอกสารจาก Token
        $docModel = new CommandDocumentModel();
        $doc = $docModel->where('qr_token', $qr_token)->first();

        if (!$doc) {
            // ไม่พบเอกสาร
            return $this->response->setStatusCode(404)->setBody('ไม่พบเอกสารที่ร้องขอ');
        }

        // 4. ตรวจสอบสิทธิ์ (หัวใจสำคัญ!)
        $accessModel = new CommandAccessModel();
        $hasAccess = $accessModel->where([
                                'command_id' => $doc['id'],
                                'hospcode'   => $userHospcode
                            ])->first();

        if (!$hasAccess) {
            // User คนนี้ (จาก Hospcode นี้) ไม่มีสิทธิ์เข้าถึงเอกสารฉบับนี้
            return $this->response->setStatusCode(403)->setBody('คุณไม่มีสิทธิ์เข้าถึงเอกสารนี้ (Access Denied)');
        }

        // 5. ถ้ามีสิทธิ์ -> บันทึก Log การดาวน์โหลด
        $logModel = new CommandDownloadModel();
        $logModel->insert([
            'command_id' => $doc['id'],
            'user_id'    => $userId,
            'hospcode'   => $userHospcode,
            'ip_address' => $this->request->getIPAddress()
        ]);

        // 6. ส่งไฟล์ให้ User ดาวน์โหลด
        $filePath = WRITEPATH . $doc['file_path'];

        if (!file_exists($filePath)) {
             return $this->response->setStatusCode(500)->setBody('ไม่พบไฟล์ในระบบ (File not found on server)');
        }

        // ใช้ response->download() ของ CI4 เพื่อส่งไฟล์
        // พารามิเตอร์ที่ 2 (null) จะให้บราวเซอร์พยายามแสดง PDF เลย (ถ้าทำได้)
        // setFileName() จะทำให้ชื่อไฟล์ตอนดาวน์โหลดเป็นชื่อเดิม (ไม่ใช่ชื่อสุ่ม)
        return $this->response
                    ->download($filePath, null)
                    ->setFileName($doc['file_name']);
    }
}