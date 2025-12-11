<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\UserModel;

class ActivityLogController extends BaseController
{
    protected $logModel;
    protected $userModel;

    public function __construct()
    {
        $this->logModel = new ActivityLogModel();
        $this->userModel = new UserModel();
    }

    /**
     * หน้าแสดงรายการ Activity Logs (เฉพาะ Admin)
     */
    public function index()
    {
        // ตรวจสอบว่า Login แล้วหรือไม่
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // ตรวจสอบว่าเป็น Admin (role 1) หรือไม่
        $userRoles = session()->get('roles');
        if (empty($userRoles)) {
            $userRoles = [];
        }
        if (!in_array(1, $userRoles)) {
            return redirect()->to('/dashboard')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $data = [
            'title' => 'Activity Logs - ประวัติการใช้งานระบบ'
        ];

        return view('admin/activity_logs', $data);
    }

    /**
     * API สำหรับ DataTables - ดึงข้อมูล Logs
     */
    public function fetch()
    {
        // ตรวจสอบว่าเป็น Admin
        $userRoles = session()->get('roles');
        if (empty($userRoles)) {
            $userRoles = [];
        }
        if (!in_array(1, $userRoles)) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $request = $this->request;
        
        // รับค่าจาก DataTables
        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        if (empty($start)) $start = 0;
        $length = $request->getPost('length');
        if (empty($length)) $length = 10;
        $searchValue = '';
        $search = $request->getPost('search');
        if (!empty($search['value'])) {
            $searchValue = $search['value'];
        }
        $orderColumnIndex = 0;
        $orderDir = 'desc';
        $order = $request->getPost('order');
        if (!empty($order[0]['column'])) {
            $orderColumnIndex = $order[0]['column'];
        }
        if (!empty($order[0]['dir'])) {
            $orderDir = $order[0]['dir'];
        }

        // กำหนดคอลัมน์ที่จะเรียงลำดับ
        $columns = ['activity_logs.id', 'users.fullname', 'activity_logs.action', 'activity_logs.description', 'activity_logs.created_at'];
        $orderColumn = 'activity_logs.created_at';
        if (isset($columns[$orderColumnIndex])) {
            $orderColumn = $columns[$orderColumnIndex];
        }

        // Query พื้นฐาน - JOIN กับ users
        $builder = $this->logModel->builder();
        $builder->select('activity_logs.*, users.fullname, users.hospcode')
                ->join('users', 'users.id = activity_logs.user_id', 'left');

        // ถ้ามีการค้นหา
        if (!empty($searchValue)) {
            $builder->groupStart()
                    ->like('users.fullname', $searchValue)
                    ->orLike('activity_logs.action', $searchValue)
                    ->orLike('activity_logs.description', $searchValue)
                    ->orLike('activity_logs.ip_address', $searchValue)
                    ->groupEnd();
        }

        // นับจำนวนทั้งหมดหลังกรอง
        $recordsFiltered = $builder->countAllResults(false);

        // เรียงลำดับและ Limit
        $builder->orderBy($orderColumn, $orderDir)
                ->limit($length, $start);

        $logs = $builder->get()->getResultArray();

        // นับจำนวนทั้งหมดก่อนกรอง
        $recordsTotal = $this->logModel->countAll();

        // ส่งข้อมูลกลับในรูปแบบ JSON สำหรับ DataTables
        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $logs,
            'token' => csrf_hash() // ส่ง CSRF token ใหม่กลับไป
        ]);
    }

    /**
     * ลบ Log (เฉพาะ Admin)
     */
    public function delete($id = null)
    {
        // ตรวจสอบว่าเป็น Admin
        $userRoles = session()->get('roles');
        if (empty($userRoles)) {
            $userRoles = [];
        }
        if (!in_array(1, $userRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        if ($id === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid ID']);
        }

        if ($this->logModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'ลบ Log สำเร็จ']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'ไม่สามารถลบ Log ได้']);
    }

    /**
     * ล้าง Logs เก่า (เฉพาะ Admin)
     * ลบ Logs ที่เก่ากว่า X วัน
     */
    public function cleanup()
    {
        // ตรวจสอบว่าเป็น Admin
        $userRoles = session()->get('roles');
        if (empty($userRoles)) {
            $userRoles = [];
        }
        if (!in_array(1, $userRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $days = $this->request->getPost('days');
        if (empty($days)) {
            $days = 90; // ค่าเริ่มต้น 90 วัน
        }

        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $builder = $this->logModel->builder();
        $builder->where('created_at <', $date);
        $deleted = $builder->delete();

        if ($deleted) {
            return $this->response->setJSON([
                'success' => true, 
                'message' => "ลบ Logs เก่ากว่า {$days} วันสำเร็จ (ลบ {$deleted} รายการ)"
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'ไม่มี Logs ที่จะลบ']);
    }
}
