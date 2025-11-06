<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Models\CchangwatModel;

class AdminController extends BaseController
{
    /**
     * แสดงหน้าหลักสำหรับอนุมัติผู้ใช้
     */
    public function manageUsers()
    {
        $session = session();
        $userRole = $session->get('roles');
        // โหลดข้อมูล Roles ทั้งหมดเพื่อส่งไปให้ Modal ใช้สร้าง Checkbox
        $cchangwatModel = new CchangwatModel();
        $provinces = $cchangwatModel->orderBy('changwatname', 'ASC')->findAll();

        $roleModel = new RoleModel();
        $query = $roleModel->orderBy('id', 'ASC');
        if (in_array(3, $userRole)) {
            // ถ้าใช่, ให้เพิ่มเงื่อนไข "ไม่เอา id 1 และ 2" เข้าไป
            $query->whereNotIn('id', [1, 2, 7]);
        }
        //$query->whereNotIn('id', [1, 2]);
       $data = [
        'all_roles' => $query->findAll(),
        'provinces' => $provinces, // เพิ่มตรงนี้
    ];
        log_activity('manage_users', 'เปิดหน้าจัดการผู้ใช้');
        return view('admin/manage_users_view', $data);
    }

    /**
     * ดึงข้อมูลผู้ใช้สำหรับแสดงใน DataTables (จัดการสิทธิ์การมองเห็นที่นี่)
     */
    public function fetchUsers()
    {
        $userModel = new UserModel();

        // Query Builder เพื่อดึงข้อมูลผู้ใช้พร้อมกับรายชื่อสิทธิ์ (Roles) ของพวกเขา
        $builder = $userModel
            ->select("users.id,users.status,users.fullname,users.position,chospital.hosname, GROUP_CONCAT(roles.role_name SEPARATOR ', ') as roles_list")
            ->join('user_roles', 'user_roles.user_id = users.id', 'left')
            ->join('roles', 'roles.id = user_roles.role_id', 'left')
            ->join('chospital', 'users.hospcode = chospital.hoscode', 'left')
            ->groupBy('users.id');

        // --- Logic การกรองข้อมูลตามสิทธิ์ ---
        // ถ้าไม่ใช่ SuperAdmin (ID 1) หรือ Adminจังหวัด (ID 2)
        if (!in_array(1, $this->currentUserRoles) && !in_array(2, $this->currentUserRoles)) {
            // และถ้าเป็น Adminอำเภอ (ID 3)
            if (in_array(3, $this->currentUserRoles)) {
                // ให้เห็นเฉพาะผู้ใช้ในอำเภอของตัวเอง
                $builder->where('users.ampurcodefull', $this->currentUser['ampurcodefull']);
            } else {
                // ถ้าไม่มีสิทธิ์ใดๆ เลย ให้ return ข้อมูลว่าง
                return $this->response->setJSON(['data' => []]);
            }
        }

        $users = $builder->findAll();
        log_activity('fetch_users', 'ดึงข้อมูลผู้ใช้ทั้งหมด');

        return $this->response->setJSON(['data' => $users]);
    }

    /**
     * ดึงข้อมูลผู้ใช้คนเดียวพร้อมสิทธิ์ทั้งหมดสำหรับหน้าแก้ไข
     */
    public function fetchUserForEdit($userId)
    {
        $userModel = new UserModel();
        $userRoleModel = new UserRoleModel();

        $user = $userModel->find($userId);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }

        $user['roles'] = array_column($userRoleModel->where('user_id', $userId)->findAll(), 'role_id');
        log_activity('fetch_user_for_edit', "ดูข้อมูลผู้ใช้สำหรับแก้ไข user_id: $userId");

        return $this->response->setJSON($user);
    }

    public function getUserDetails($id)
    {
        $userModel = new UserModel();
        $userRoleModel = new UserRoleModel();
        
        $user = $userModel->find($id);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }

        $userRoles = $userRoleModel->where('user_id', $id)->findColumn('role_id') ?? [];

        $data = [
            'id'              => $user['id'],
            'fullname'        => $user['fullname'],
            'position'        => $user['position'],
            'status'          => $user['status'],
            'roles'           => $userRoles,
            'changwatcode'    => $user['changwatcode'],
            'ampurcodefull'   => $user['ampurcodefull'],
            'hospcode'        => $user['hospcode'],
            'villagecodefull' => $user['villagecodefull'],
        ];

        return $this->response->setJSON($data);
    }

public function approveUser()
    {
        $userId = $this->request->getPost('user_id');
        $status = $this->request->getPost('status');
        $roles = $this->request->getPost('roles') ?? [];

        // --- Validation ---
        $rules = [
            'user_id' => 'required|is_not_unique[users.id]',
            'status'  => 'required|in_list[0,1,2,3]',
            'roles'   => 'permit_empty|is_array'
        ];
        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => $this->validator->getErrors()]);
        }

        $userModel = new UserModel();
        $userRoleModel = new UserRoleModel();
        
        $this->db->transStart();
        
        // 1. อัปเดตสถานะผู้ใช้
        $userModel->update($userId, [
            'status' => $status,
            'approved_by' => $this->currentUser['id']
        ]);

        // 2. อัปเดต Roles
        $userRoleModel->where('user_id', $userId)->delete();
        if (!empty($roles)) {
            $roleData = [];
            foreach ($roles as $roleId) {
                $roleData[] = ['user_id' => $userId, 'role_id' => $roleId];
            }
            $userRoleModel->insertBatch($roleData);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Database transaction failed']);
        }
        
        log_activity('approve_user', "อนุมัติและกำหนดสิทธิ์ผู้ใช้ ID: {$userId}", $userId);
        return $this->response->setJSON(['success' => 'User status and roles updated successfully.']);
    }


    /**
     * อัปเดตข้อมูลผู้ใช้และสิทธิ์ที่ได้รับมอบหมาย
     */
     public function updateUser()
    {
        $userId = $this->request->getPost('user_id');

        // --- Validation ---
        $rules = [
            'user_id'         => 'required|is_not_unique[users.id]',
            'fullname'        => 'required|min_length[3]|max_length[150]',
            'position'        => 'required|max_length[100]',
            'changwatcode'    => 'required|exact_length[2]',
            'ampurcodefull'   => 'required|exact_length[4]',
            'hospcode'        => 'permit_empty|exact_length[5]',
            'villagecodefull' => 'permit_empty|exact_length[8]'
        ];
        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => $this->validator->getErrors()]);
        }
        
        $dataToUpdate = [
            'fullname'        => $this->request->getPost('fullname'),
            'position'        => $this->request->getPost('position'),
            'changwatcode'    => $this->request->getPost('changwatcode'),
            'ampurcodefull'   => $this->request->getPost('ampurcodefull'),
            'hospcode'        => $this->request->getPost('hospcode'),
            'villagecodefull' => $this->request->getPost('villagecodefull'),
        ];
        
        $userModel = new UserModel();
        if ($userModel->update($userId, $dataToUpdate)) {
            log_activity('update_user', "แก้ไขข้อมูลผู้ใช้ ID: {$userId}", $userId);
            return $this->response->setJSON(['success' => 'User details updated successfully.']);
        }

        return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to update user.']);
    }

    /**
     * ฟังก์ชันภายในเพื่อกรอง Role ที่ Admin ปัจจุบันสามารถกำหนดให้ผู้อื่นได้
     */
    private function getAllowedRolesForEditing()
    {
        $roleModel = new RoleModel();

        // SuperAdmin (ID 1) สามารถให้ได้ทุก Role
        if (in_array(1, $this->currentUserRoles)) {
            return $roleModel->findAll();
        }
        // Adminจังหวัด (ID 2) ให้ได้ทุก Role ยกเว้น SuperAdmin
        if (in_array(2, $this->currentUserRoles)) {
            return $roleModel->where('id !=', 1)->findAll();
        }
        // Adminอำเภอ (ID 3) ให้ได้ทุก Role ยกเว้น SuperAdmin และ Adminจังหวัด
        if (in_array(3, $this->currentUserRoles)) {
            return $roleModel->where('id >', 2)->findAll();
        }
        // กรณีอื่นๆ (ไม่มีสิทธิ์)
        return [];
    }

    public function userApproval()
    {
        // --- ตรวจสอบสิทธิ์ ---
        // อนุญาตให้เฉพาะ 'Adminอำเภอ' (Role ID = 3) เข้าถึงหน้านี้
        if (!in_array(3, $this->currentUserRoles)) {
            return redirect()->to('/')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $roleModel = new RoleModel();
        $excludedRoles = [1, 2];
        $data = [
            // ดึง Role ทั้งหมดไปแสดงใน Modal (ยกเว้น SuperAdmin)
            'roles' => $roleModel->whereNotIn('id', $excludedRoles)->findAll()
        ];
        log_activity('user_approval', 'เปิดหน้าอนุมัติผู้ใช้');

        return view('admin/approval_view', $data);
    }

    /**
     * ดึงข้อมูลผู้ใช้ที่รออนุมัติสำหรับ DataTables (ผ่าน AJAX)
     */
    public function fetchPendingUsers()
    {
        // ตรวจสอบสิทธิ์อีกครั้งเพื่อความปลอดภัย
        if (!in_array(3, $this->currentUserRoles)) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $userModel = new UserModel();

        // ดึง ampurcodefull ของ Admin ที่ Login อยู่ จาก BaseController
        $adminAmphurCode = $this->currentUser['ampurcodefull'];

        // ค้นหาผู้ใช้ที่ status = 0 และมี ampurcodefull ตรงกับ Admin
        $pendingUsers = $userModel->where('status', 0)
            ->where('ampurcodefull', $adminAmphurCode)
            ->findAll();
        log_activity('fetch_pending_users', 'ดึงข้อมูลผู้ใช้ที่รออนุมัติ');

        $data['data'] = $pendingUsers;
        return $this->response->setJSON($data);
    }

    /**
     * ประมวลผลการอนุมัติผู้ใช้และกำหนดสิทธิ์ (ผ่าน AJAX)
     */
    public function processApproval()
    {
        // ตรวจสอบสิทธิ์
        if (!in_array(3, $this->currentUserRoles)) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $userModel = new UserModel();
        $userRoleModel = new UserRoleModel();

        $userId = $this->request->getPost('user_id');
        $roles = $this->request->getPost('roles'); // นี่คือ Array ของ role_id

        // --- เริ่ม Transaction ---
        $this->db->transStart();

        // 1. อัปเดตสถานะผู้ใช้
        $userModel->update($userId, [
            'status' => 1, // 1 = ใช้งานได้
            'approved_by' => $this->currentUser['id'] // ID ของ Admin ที่อนุมัติ
        ]);

        // 2. ลบ Role เก่า (ถ้ามี) และกำหนด Role ใหม่
        $userRoleModel->where('user_id', $userId)->delete();
        if (!empty($roles)) {
            foreach ($roles as $roleId) {
                $userRoleModel->insert([
                    'user_id' => $userId,
                    'role_id' => $roleId
                ]);
            }
        }

        // --- จบ Transaction ---
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
        }
        log_activity('process_approval', "อนุมัติผู้ใช้ user_id: $userId");
        return $this->response->setJSON(['status' => 'success', 'message' => 'อนุมัติผู้ใช้งานสำเร็จ']);
    }

    /**
     * ประมวลผลการปฏิเสธผู้ใช้ (ผ่าน AJAX)
     */
    public function rejectUser($id)
    {
        // ตรวจสอบสิทธิ์
        if (!in_array(3, $this->currentUserRoles)) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $userModel = new UserModel();
        $userModel->update($id, ['status' => 3]); // 3 = ถูกปฏิเสธ
        log_activity('reject_user', "ปฏิเสธผู้ใช้ user_id: $id");

        return $this->response->setJSON(['status' => 'success', 'message' => 'ปฏิเสธการสมัครเรียบร้อยแล้ว']);
    }

    public function getProvinces()
    {
        $cchangwatModel = new CchangwatModel();
        $provinces = $cchangwatModel->orderBy('changwatname', 'ASC')->findAll();
        return $this->response->setJSON($provinces);
    }
}
