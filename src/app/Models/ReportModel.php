<?php
// --------------------------------------------------------------------
// (1/4) Model: ReportModel.php (Updated)
// --------------------------------------------------------------------
// อัปเดตไฟล์ที่: app/Models/ReportModel.php
// ** เพิ่มการกรองข้อมูลตามจังหวัดหลักของระบบ **

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * ดึงข้อมูลสรุปจำนวนผู้ป่วยตามพื้นที่และระดับความเสี่ยง
     *
     * @param string $provinceCode รหัสจังหวัดหลักของระบบ
     * @param string|null $amphurCode รหัสอำเภอ 4 หลัก (ถ้ามี)
     * @param string|null $hospCode รหัสสถานบริการ 5 หลัก (ถ้ามี)
     * @return array ผลลัพธ์ของรายงาน
     */
    public function getPatientSummary($provinceCode, $amphurCode = null, $hospCode = null)
    {
        // --- เลือกระดับการ Group และ select area ---
        if (!empty($hospCode)) {
            // Group by village
            $groupBy = 'p.villagecodefull';
            $selectArea = 'v.villagename as area_name';
            $groupByArea = 'v.villagename';
            $joinTable = 'cvillage v';
            $joinCondition = 'p.villagecodefull = v.villagecodefull';
        } elseif (!empty($amphurCode)) {
            // Group by hospital
            $groupBy = 'p.hospcode';
            $selectArea = 'h.hosname as area_name';
            $groupByArea = 'h.hosname';
            $joinTable = 'chospital h';
            $joinCondition = 'p.hospcode = h.hoscode';
        } else {
            // Group by amphur
            $groupBy = 'p.ampurcodefull';
            $selectArea = 'a.ampurname as area_name';
            $groupByArea = 'a.ampurname';
            $joinTable = 'campur a';
            $joinCondition = 'p.ampurcodefull = a.ampurcodefull';
        }

        $builder = $this->db->table('patients p');
        $builder->select("$groupBy as area_code, $selectArea");
        $builder->select("COUNT(p.id) as total");

        $riskLevels = $this->db->table('risk_levels')->get()->getResultArray();
        foreach ($riskLevels as $level) {
            $builder->select("SUM(CASE WHEN p.risk_level_id = {$level['id']} THEN 1 ELSE 0 END) as level_{$level['id']}");
        }

        // Join the chosen area table
        $builder->join($joinTable, $joinCondition, 'left');

        // If we grouped by village but need hospital name for filtering/join, join hospital as well
        if (!empty($hospCode) && strpos($joinTable, 'cvillage') !== false) {
            $builder->join('chospital h', 'v.hospcode = h.hoscode', 'left');
        }

        // Always filter by province if provided
        if (!empty($provinceCode)) {
            $builder->where('p.changwatcode', $provinceCode);
        }

        if (!empty($hospCode)) {
            $builder->where('p.hospcode', $hospCode);
        } elseif (!empty($amphurCode)) {
            $builder->where('p.ampurcodefull', $amphurCode);
        }

        // Ensure GROUP BY covers all non-aggregated select columns (split into two calls)
        $builder->groupBy($groupBy);
        $builder->groupBy($groupByArea);

        $builder->orderBy('area_name', 'ASC');

        return [
            'data' => $builder->get()->getResultArray(),
            'riskLevels' => $riskLevels
        ];
    }

    /**
     * [ฟังก์ชันใหม่] ดึงข้อมูลสรุปการเยี่ยมบ้าน (คน/ครั้ง)
     */
    public function getVisitSummary($level = 'amphur', $filters = [])
    {
        $builder = $this->db->table('follow_ups fu');
        $builder->join('patients p', 'p.id = fu.patient_id');

        switch ($level) {
            case 'hospcode':
                // แก้ไขการ join กับตาราง chospital ผ่าน village
                $builder->join('village v', 'v.VID = p.villagecode', 'left');
                $builder->join('chospital h', 'h.hoscode = v.HOSPCODE', 'left');
                $areaField = 'h.hoscode';
                $areaNameField = 'h.hosname';
                $groupByFields = ['h.hoscode', 'h.hosname'];
                if (!empty($filters['amphurCode'])) {
                    $builder->where('p.ampurcodefull', $filters['amphurCode']);
                }
                break;
                
            case 'village':
                $builder->join('cvillage v', 'v.villagecodefull = p.villagecode', 'left');
                $areaField = 'v.villagecodefull';
                $areaNameField = 'CONCAT("หมู่ ", v.villageno, " ", v.villagename)';
                $groupByFields = ['v.villagecodefull', 'v.villageno', 'v.villagename'];
                if (!empty($filters['hospCode'])) {
                    $builder->join('village vl', 'vl.VID = p.villagecode', 'left');
                    $builder->where('vl.HOSPCODE', $filters['hospCode']);
                }
                break;
                
            case 'amphur':
            default:
                $builder->join('campur a', 'a.ampurcodefull = p.ampurcodefull', 'left');
                $areaField = 'a.ampurcodefull';
                $areaNameField = 'a.ampurname';
                $groupByFields = ['a.ampurcodefull', 'a.ampurname'];
                if (!empty($filters['provinceCode'])) {
                    $builder->where('p.changwatcode', $filters['provinceCode']);
                }
                break;
        }

        $builder->select("$areaField AS area_code, $areaNameField AS area_name");
        $builder->select('COUNT(DISTINCT fu.patient_id) AS person_count');
        $builder->select('COUNT(fu.id) AS visit_count');

        $builder->where("$areaField IS NOT NULL");
        
        foreach ($groupByFields as $field) {
            $builder->groupBy($field);
        }
        
        $builder->orderBy('area_name', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * [ฟังก์ชันใหม่] ดึงรายชื่อผู้ป่วยที่ถูกเยี่ยม ตามสิทธิ์การเข้าถึง
     */
    public function getVisitedPatientList($filters, $currentUser)
    {
        $builder = $this->db->table('patients p');
        $builder->select('p.cid, p.fullname, r.name as risk_level_name, r.color_hex, 
                          (SELECT MAX(fu.visit_date) FROM follow_ups fu WHERE fu.patient_id = p.id) as last_visit_date');
        $builder->join('risk_levels r', 'r.id = p.risk_level_id', 'left');

        // --- Core Logic: ต้องเป็นผู้ป่วยที่เคยถูกเยี่ยมเท่านั้น ---
        $builder->whereIn('p.id', function ($subquery) {
            $subquery->select('patient_id')->from('follow_ups');
        });

        // --- Filter ตามพื้นที่ที่เลือก ---
        $builder->where('p.villagecodefull', $filters['villageCode']);

        // --- !! สำคัญ: Filter ตามสิทธิ์และพื้นที่รับผิดชอบของผู้ใช้ !! ---
        $userRoles = $currentUser['roles'] ?? []; // สมมติว่า roles อยู่ใน session

        if (in_array('User-District', $userRoles)) {
            $builder->where('p.ampurcodefull', $currentUser['ampurcodefull']);
        } elseif (in_array('User-HealthCenter', $userRoles)) {
            $builder->where('p.hospcode', $currentUser['hospcode']);
        } elseif (in_array('User-Village', $userRoles)) {
            $builder->where('p.villagecodefull', $currentUser['villagecodefull']);
        }
        // กรณีเป็น User-Provincial หรือ SuperAdmin ไม่ต้อง filter เพิ่มเติม

        $builder->orderBy('p.fullname', 'ASC');

        return $builder->get()->getResultArray();
    }
}
