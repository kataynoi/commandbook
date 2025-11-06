<?php
// --------------------------------------------------------------------
// (2/6) Controller: SettingsController.php
// --------------------------------------------------------------------
// สร้าง Controller ใหม่ทั้งหมดที่: app/Controllers/SettingsController.php
// สำหรับหน้าตั้งค่าการเชื่อมต่อ API (สำหรับ SuperAdmin)

namespace App\Controllers;

use App\Models\SettingsModel;
use App\Models\CchangwatModel; // <-- เพิ่ม Model จังหวัด

class SettingsController extends BaseController
{
    public function index()
    {
        // อนุญาตให้เฉพาะ SuperAdmin (Role ID 1) เข้าถึงหน้านี้
        if (!in_array(1, $this->currentUserRoles)) {
            return redirect()->to('/')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $settingsModel = new SettingsModel();
        $data = [
            'api_url'      => $settingsModel->get('api_url'),
            'api_username' => $settingsModel->get('api_username'),
            'api_password' => $settingsModel->get('api_password'),
        ];

        log_activity('settings_view', 'เปิดหน้าตั้งค่า API');

        return view('admin/settings_view', $data);
    }

    public function save()
    {
        if (!in_array(1, $this->currentUserRoles)) {
            return redirect()->to('/')->with('error', 'คุณไม่มีสิทธิ์ดำเนินการ');
        }

        $settingsModel = new SettingsModel();
        $settingsModel->saveSetting('api_url', $this->request->getPost('api_url'));
        $settingsModel->saveSetting('api_username', $this->request->getPost('api_username'));

        // บันทึกรหัสผ่านเฉพาะเมื่อมีการกรอกใหม่
        if ($this->request->getPost('api_password')) {
            $settingsModel->saveSetting('api_password', $this->request->getPost('api_password'));
        }

        // ล้าง Token เก่าเพื่อให้ระบบขอใหม่ในการเรียกครั้งถัดไป
        $settingsModel->saveSetting('api_token', '');
        $settingsModel->saveSetting('api_token_expires_at', '');

        log_activity('settings_save', 'บันทึกการตั้งค่า API');

        return redirect()->to('/admin/settings')->with('success', 'บันทึกการตั้งค่าสำเร็จ');
    }
    public function province()
    {
        // อนุญาตให้เฉพาะ SuperAdmin (Role ID 1) เข้าถึงหน้านี้
        if (!in_array(1, $this->currentUserRoles)) {
            return redirect()->to('/')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $settingsModel = new SettingsModel();
        $cchangwatModel = new CchangwatModel();

        $data = [
            'provinces' => $cchangwatModel->orderBy('changwatname', 'ASC')->findAll(),
            'current_province_code' => $settingsModel->get('system_province_code'),
        ];

        log_activity('settings_province_view', 'เปิดหน้าตั้งค่าจังหวัดหลัก');

        return view('admin/settings_province_view', $data);
    }

    /**
     * บันทึกการตั้งค่าจังหวัดหลัก
     */
    public function saveProvince()
    {
        if (!in_array(1, $this->currentUserRoles)) {
            return redirect()->to('/')->with('error', 'คุณไม่มีสิทธิ์ดำเนินการ');
        }

        $settingsModel = new SettingsModel();
        $provinceCode = $this->request->getPost('system_province_code');

        $settingsModel->saveSetting('system_province_code', $provinceCode);

        log_activity('settings_province_save', 'บันทึกการตั้งค่าจังหวัดหลัก');

        return redirect()->to('/admin/settings/province')->with('success', 'บันทึกการตั้งค่าจังหวัดสำเร็จ');
    }
}
