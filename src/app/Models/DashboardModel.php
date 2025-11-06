<?php
// --------------------------------------------------------------------
// (1/4) Model: DashboardModel.php
// --------------------------------------------------------------------
// สร้างไฟล์ใหม่ที่: app/Models/DashboardModel.php
// ** มีการแก้ไขในไฟล์นี้ **

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getSummaryCardData($provinceCode = null, $amphurCode = null, $hospCode = null)
    {
        $builder = $this->db->table('patients');
        $builder->select('patients.risk_level_id, b.name, b.color_hex, COUNT(patients.id) as total');
        $builder->join('risk_levels b', 'patients.risk_level_id = b.id', 'left');
        
        if ($hospCode) {
            $builder->join('cvillage', 'patients.villagecode = cvillage.villagecodefull', 'left');
            $builder->where('cvillage.hospcode', $hospCode);
        }
        if ($provinceCode) {
            $builder->where('patients.changwatcode', $provinceCode);
        }
        if ($amphurCode) {
            $builder->where('patients.ampurcodefull', $amphurCode);
        }
        
        $builder->groupBy('patients.risk_level_id, b.name, b.color_hex');
        $builder->orderBy('patients.risk_level_id', 'ASC');
        return $builder->get()->getResultArray();
    }

    public function getStackedBarData($provinceCode = null, $amphurCode = null, $hospCode = null)
    {
        $builder = $this->db->table('patients p');
        $builder->select('p.ampurcodefull, a.ampurname, p.risk_level_id, r.name as risk_level_name, r.color_hex, COUNT(p.id) as total');
        $builder->join('campur a', 'p.ampurcodefull = a.ampurcodefull', 'left');
        $builder->join('risk_levels r', 'p.risk_level_id = r.id', 'left');
        $builder->where('p.ampurcodefull IS NOT NULL');
        
        if ($hospCode) {
            $builder->join('cvillage', 'p.villagecode = cvillage.villagecodefull', 'left');
            $builder->where('cvillage.hospcode', $hospCode);
        }
        if ($provinceCode) {
            $builder->where('p.changwatcode', $provinceCode);
        }
        if ($amphurCode) {
            $builder->where('p.ampurcodefull', $amphurCode);
        }
        
        $builder->groupBy('p.ampurcodefull, a.ampurname, p.risk_level_id, r.name, r.color_hex');
        $builder->orderBy('a.ampurname, p.risk_level_id');
        $rawData = $builder->get()->getResultArray();

        if (empty($rawData)) {
            return ['labels' => [], 'datasets' => []];
        }

        $labels = []; $datasets = []; $riskLevels = []; $amphurData = [];

        foreach ($rawData as $row) {
            if (!in_array($row['ampurname'], $labels)) $labels[] = $row['ampurname'];
            if (!isset($riskLevels[$row['risk_level_id']])) {
                $riskLevels[$row['risk_level_id']] = ['label' => $row['risk_level_name'], 'backgroundColor' => $row['color_hex'], 'data' => []];
            }
            $amphurData[$row['ampurname']][$row['risk_level_id']] = $row['total'];
        }

        foreach ($riskLevels as $riskId => $details) {
            $dataForRisk = [];
            foreach ($labels as $amphurName) {
                $dataForRisk[] = $amphurData[$amphurName][$riskId] ?? 0;
            }
            $datasets[] = ['label' => $details['label'], 'data' => $dataForRisk, 'backgroundColor' => $details['backgroundColor']];
        }
        
        return ['labels' => $labels, 'datasets' => $datasets];
    }

    public function getGenderData($provinceCode = null, $amphurCode = null, $hospCode = null)
    {
        $builder = $this->db->table('patients');
        $builder->select("CASE WHEN sex = '1' THEN 'ชาย' WHEN sex = '2' THEN 'หญิง' ELSE 'ไม่ระบุ' END as gender, COUNT(id) as total");
        
        if ($hospCode) {
            $builder->join('cvillage', 'patients.villagecode = cvillage.villagecodefull', 'left');
            $builder->where('cvillage.hospcode', $hospCode);
        }
        if ($provinceCode) {
            $builder->where('patients.changwatcode', $provinceCode);
        }
        if ($amphurCode) {
            $builder->where('patients.ampurcodefull', $amphurCode);
        }
        
        $builder->groupBy('gender');
        $builder->orderBy('gender', 'ASC');
        return $builder->get()->getResultArray();
    }
    
    public function getAgeGroupData($provinceCode = null, $amphurCode = null, $hospCode = null)
    {
        $builder = $this->db->table('patients');
        $builder->select("FLOOR(age / 5) * 5 AS age_group_start, COUNT(id) AS total");
        $builder->where('patients.age IS NOT NULL AND patients.age >= 0');
        if ($hospCode) {
            $builder->join('cvillage', 'patients.villagecode = cvillage.villagecodefull', 'left');
            $builder->where('cvillage.hospcode', $hospCode);
        }
        if ($provinceCode) {
            $builder->where('patients.changwatcode', $provinceCode);
        }
        if ($amphurCode) {
            $builder->where('patients.ampurcodefull', $amphurCode);
        }
        
        $builder->groupBy('age_group_start');
        $builder->orderBy('age_group_start', 'ASC');
        $result = $builder->get()->getResultArray();
        
        return [
            'labels' => array_map(fn($row) => ($row['age_group_start'] ?? 'ไม่ทราบ') . '-' . (($row['age_group_start'] ?? 0) + 4), $result),
            'data' => array_column($result, 'total')
        ];
    }
}
?>