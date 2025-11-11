<?php
// --------------------------------------------------------------------
// (2/4) Controller: DashboardController.php (Refactored)
// --------------------------------------------------------------------
// อัปเดตไฟล์ที่: app/Controllers/DashboardController.php
// ** เพิ่ม try-catch เพื่อดักจับข้อผิดพลาด **

namespace App\Controllers;

use App\Models\DashboardModel;
use App\Models\SettingsModel;
use App\Models\CchangwatModel;
use App\Models\CampurModel;
use App\Models\ChospitalModel;
use Exception; // <-- เพิ่ม Exception

class DashboardController extends BaseController
{
    public function index()
    {
               $settingsModel = new SettingsModel();
        $cchangwatModel = new CchangwatModel();
        $campurModel = new CampurModel();

        $provinceCode = $settingsModel->get('system_province_code');
        $provinceName = '';
        $amphurs = [];

        if ($provinceCode) {
            $province = $cchangwatModel->find($provinceCode);
            $provinceName = $province ? $province['changwatname'] : '';
            $amphurs = $campurModel->where('changwatcode', $provinceCode)
                                   ->orderBy('ampurname', 'ASC')
                                   ->findAll();
        }

        return view('dashboard/dashboard_view', [
            'provinceCode' => $provinceCode,
            'provinceName' => $provinceName,
            'amphurs' => $amphurs,
        ]);
    }

}
?>