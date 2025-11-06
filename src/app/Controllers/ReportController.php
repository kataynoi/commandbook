<?php
// --------------------------------------------------------------------
// (2/4) Controller: ReportController.php (Updated)
// --------------------------------------------------------------------
// อัปเดตไฟล์ที่: app/Controllers/ReportController.php
// ** แก้ไขฟังก์ชัน patientSummary และ getReportData **

namespace App\Controllers;

use App\Models\ReportModel;
use App\Models\CampurModel;
use App\Models\ChospitalModel;
use App\Models\SettingsModel; // <-- เพิ่ม Model Settings
use App\Models\CchangwatModel; // <-- เพิ่ม Model จังหวัด

class ReportController extends BaseController
{
    /**
     * แสดงหน้าหลักของรายงาน
     */
    public function patientSummary()
    {
        $settingsModel = new SettingsModel();
        $campurModel = new CampurModel();
        $cchangwatModel = new CchangwatModel();

        // ดึงรหัสจังหวัดหลักของระบบ
        $provinceCode = $settingsModel->get('system_province_code');
        
        $provinceName = '';
        $amphurs = [];

        if ($provinceCode) {
            // ดึงชื่อจังหวัด
            $province = $cchangwatModel->find($provinceCode);
            $provinceName = $province ? $province['changwatname'] : 'ไม่พบจังหวัด';
            
            // ดึงข้อมูลอำเภอเฉพาะในจังหวัดนั้นๆ
            $amphurs = $campurModel->where('changwatcode', $provinceCode)
                                   ->orderBy('ampurname', 'ASC')
                                   ->findAll();
        }
        
        $data = [
            'provinceName' => $provinceName,
            'amphurs' => $amphurs
        ];

        return view('reports/patient_summary_view', $data);
    }

    /**
     * Endpoint สำหรับ AJAX เพื่อดึงข้อมูลรายงาน
     */
    public function getReportData()
    {
        $reportModel = new ReportModel();
        $settingsModel = new SettingsModel();

        // ดึงจังหวัดหลักของระบบมาใช้ในการ Query เสมอ
        $provinceCode = $settingsModel->get('system_province_code');
        $amphurCode = $this->request->getPost('amphur_code');
        $hospCode = $this->request->getPost('hosp_code');

        try {
            $result = $reportModel->getPatientSummary($provinceCode, $amphurCode, $hospCode);
            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', '[ReportController] getReportData: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูลรายงาน',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Endpoint สำหรับ AJAX เพื่อดึงข้อมูล รพ.สต. ในอำเภอ
     */
   public function getHospitalsInAmphur()
    {
        $amphurCode = $this->request->getPost('amphur_code');
        if (empty($amphurCode)) {
            return $this->response->setJSON([]);
        }

        $provCode = substr($amphurCode, 0, 2);
        $distCode = substr($amphurCode, 2, 2);

        $hospitalModel = new ChospitalModel();
        $hospitals = $hospitalModel->where('provcode', $provCode)
                                   ->where('distcode', $distCode)
                                   ->whereIn('hostype', ['07', '08', '18']) 
                                   ->orderBy('hosname', 'ASC')
                                   ->findAll();
        
        return $this->response->setJSON($hospitals);
    }

    /**
     * ตัวอย่างเมธอดลบข้อมูลรายงาน (ถ้ามี)
     */
    public function deleteReport($id)
    {
        try {
            $reportModel = new ReportModel();
            if ($reportModel->delete($id)) {
                log_activity('delete_report', 'ลบข้อมูลรายงานสำเร็จ', $id);
                return $this->response->setJSON(['status' => 'success', 'message' => 'ลบข้อมูลสำเร็จ']);
            }
            log_activity('delete_report_fail', 'เกิดข้อผิดพลาดในการลบข้อมูล', $id);
            return $this->response->setJSON(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล']);
        } catch (\Exception $e) {
            log_message('error', '[ReportController] deleteReport: ' . $e->getMessage());
            log_activity('delete_report_exception', 'Exception: ' . $e->getMessage(), $id);
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'เกิดข้อผิดพลาดในการลบข้อมูล',
                'message' => $e->getMessage()
            ]);
        }
    }
public function visitSummary()
    {
        $settingsModel = new SettingsModel();
        $campurModel = new CampurModel();

        // ดึงจังหวัดหลักของระบบ
        $provinceCode = $settingsModel->get('system_province_code');
        if (!$provinceCode) {
            // จัดการกรณีที่ยังไม่ได้ตั้งค่าจังหวัด
            return view('errors/html/error_404', ['message' => 'กรุณาตั้งค่าจังหวัดหลักของระบบก่อน']);
        }

        // ดึงชื่อจังหวัดและอำเภอในจังหวัดนั้น
        $cchangwatModel = new CchangwatModel();
        $provinceName = $cchangwatModel->find($provinceCode)['changwatname'] ?? 'ไม่พบจังหวัด';
        $amphurs = $campurModel->where('changwatcode', $provinceCode)->orderBy('ampurname', 'ASC')->findAll();

        $data = [
            'provinceCode' => $provinceCode,
            'provinceName' => $provinceName,
            'amphurs'      => $amphurs
        ];

        return view('reports/visit_summary_view', $data);
    }

    /**
     * [ฟังก์ชันใหม่] AJAX endpoint สำหรับดึงข้อมูลรายงานการเยี่ยมบ้าน
     */
    public function ajaxGetVisitSummary()
    {
        $reportModel = new ReportModel();
        
        $level = $this->request->getPost('level') ?? 'amphur';
        $filters = [
            'provinceCode' => $this->request->getPost('provinceCode'),
            'amphurCode'   => $this->request->getPost('amphurCode'),
            'hospCode'     => $this->request->getPost('hospCode'),
            'villageCode'  => $this->request->getPost('villageCode'),
        ];

        // ตรวจสอบว่าควรแสดงรายชื่อผู้ป่วยหรือยัง (เมื่อเลือกระดับหมู่บ้าน)
        if ($level === 'village' && !empty($filters['villageCode'])) {
            $data = $reportModel->getVisitedPatientList($filters, $this->currentUser);
            $response = [
                'type' => 'patient_list',
                'data' => $data
            ];
        } else {
            // แสดงข้อมูลสรุปตามพื้นที่
            $data = $reportModel->getVisitSummary($level, $filters);
            $response = [
                'type' => 'summary',
                'data' => $data
            ];
        }

        return $this->response->setJSON($response);
    }

}
?>