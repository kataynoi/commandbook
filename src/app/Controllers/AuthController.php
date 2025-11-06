<?php
// --------------------------------------------------------------------
// File: app/Controllers/AuthController.php
// --------------------------------------------------------------------
// Controller หลักสำหรับจัดการระบบยืนยันตัวตน (Authentication)
// --------------------------------------------------------------------
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserRoleModel;
use CodeIgniter\Controller;
use App\Models\CchangwatModel;
use App\Models\CampurModel;
use App\Models\ChospitalModel; // <-- เพิ่ม Model ตำบล
use App\Models\CvillageModel; // <-- เพิ่ม Model หมู่บ้าน
use App\Models\SettingsModel;

helper('activityLog');
// <-- เรียก helper ที่นี่ (หลัง namespace, ก่อน class)
class AuthController extends Controller
{
    /**
     * แสดงหน้าฟอร์มสำหรับ Login
     */
    public function index()
    {
        // ถ้าผู้ใช้ Login อยู่แล้ว ให้ redirect ไปหน้า dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('dashboard');
        }

        helper(['form']);
        return view('auth/login');
    }
    /**
     * แสดงหน้าฟอร์มสมัครสมาชิก
     */
    public function register()
    {
        $settingsModel = new SettingsModel();
        $campurModel = new CampurModel();
        $cchangwatModel = new CchangwatModel();

        $default_province = $settingsModel->get('system_province_code');
        $provinceName = '';
        $amphurs = [];

        if ($default_province) {
            $province = $cchangwatModel->find($default_province);
            $provinceName = $province ? $province['changwatname'] : '';
            $amphurs = $campurModel->where('changwatcode', $default_province)
                ->orderBy('ampurname', 'ASC')
                ->findAll();
        }

        $data = [
            'changwats' => $cchangwatModel->orderBy('changwatname', 'ASC')->findAll(),
            'default_province' => $default_province, // <-- ส่งค่าจังหวัดเริ่มต้นไปที่ View
            'provinceName' => $provinceName,
            'amphurs' => $amphurs,
        ];
        log_activity('register_view', 'เปิดหน้าสมัครสมาชิก');
        return view('auth/register_view', $data);
    }
    /**
     * ประมวลผลข้อมูลที่ส่งมาจากฟอร์ม Login
     */
    public function attemptLogin()
    {
        $userModel = new UserModel();
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // 1. ค้นหาผู้ใช้จาก Username
        $user = $userModel->where('username', $username)->first();

        // 2. ตรวจสอบว่ามีผู้ใช้ และรหัสผ่านถูกต้องหรือไม่
        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
        }

        // 3. *** จุดที่แก้ไข: ตรวจสอบสถานะ (Status) ของผู้ใช้ ***
        if ($user['status'] != 1) {
            $statusMessage = '';
            switch ($user['status']) {
                case 0:
                    $statusMessage = 'บัญชีของคุณกำลังรอการอนุมัติจากผู้ดูแลระบบ';
                    break;
                case 2:
                    $statusMessage = 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ';
                    break;
                case 3:
                    $statusMessage = 'การสมัครของคุณถูกปฏิเสธ กรุณาติดต่อผู้ดูแลระบบ';
                    break;
                default:
                    $statusMessage = 'บัญชีของคุณไม่สามารถใช้งานได้';
                    break;
            }
            // ส่งข้อความแจ้งเตือนกลับไปที่หน้า Login
            return redirect()->back()->withInput()->with('error', $statusMessage);
        }

        // 4. หากทุกอย่างถูกต้อง: สร้าง Session
        $userRoleModel = new UserRoleModel();
        $roles = $userRoleModel->getRolesForUser($user['id']);

        $this->createUserSession($user);
        log_activity('login', "ผู้ใช้ '{$user['username']}' เข้าสู่ระบบสำเร็จ");

        // 5. ส่งไปหน้า Dashboard
        return redirect()->to('/dashboard');
    }

    /**
     * [ฟังก์ชันชั่วคราว] สำหรับรีเซ็ตรหัสผ่านของผู้ใช้
     * เพื่อสร้าง Hash ที่ถูกต้องสำหรับสภาพแวดล้อมปัจจุบัน
     */
    public function resetPassword()
    {
        $userModel = new UserModel();

        // ค้นหาผู้ใช้ 'mana' จาก ID (จากข้อมูลดีบักคือ id=3)
        $userId = 3;

        // ข้อมูลใหม่ที่จะอัปเดต (รหัสผ่านใหม่คือ '1234')
        $data = [
            'password' => '1234'
        ];

        // ใช้ Model เพื่อบันทึก ซึ่งจะไปเรียกใช้ beforeUpdate hook (hashPassword) โดยอัตโนมัติ
        if ($userModel->update($userId, $data)) {
            log_activity('reset_password', "รีเซ็ตรหัสผ่านสำหรับ user_id: {$userId}");
            echo "<h1>รีเซ็ตรหัสผ่านสำหรับผู้ใช้ 'mana' สำเร็จ!</h1>";
            echo "<p>รหัสผ่านใหม่คือ '1234'</p>";
            echo "<p>ตอนนี้คุณสามารถกลับไปที่หน้า Login และเข้าระบบได้แล้ว</p>";
            echo "<p><a href='" . site_url('login') . "'>กลับไปหน้า Login</a></p>";
        } else {
            log_activity('reset_password_fail', "รีเซ็ตรหัสผ่านไม่สำเร็จ user_id: {$userId}");
            echo "<h1>เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน</h1>";
        }
    }
    public function attemptRegister()
    {
        // 1. ตั้งกฎการตรวจสอบข้อมูล (เพิ่ม villagecodefull)
        $rules = [
            'fullname'      => 'required|min_length[3]|max_length[150]',
            'position'      => 'required|max_length[100]',
            'cid'          => 'required|valid_cid|is_unique[users.cid]', // <-- ตรวจสอบเลขบัตรประชาชน (CID) และไม่ซ้ำกัน
            'username'      => 'required|min_length[4]|max_length[50]|is_unique[users.username]',
            'password'     => 'required|strong_password',
            'pass_confirm'  => 'required|matches[password]',
            'changwatcode'  => 'required',
            'ampurcodefull' => 'permit_empty',
            'hospcode'      => 'permit_empty', // <-- เปลี่ยนจากตำบลเป็น รพ.สต.
            'villagecodefull' => 'permit_empty' // <-- หมู่บ้านอาจจะไม่บังคับ (ถ้าต้องการบังคับให้เปลี่ยนเป็น 'required')
        ];

        $messages = [
            'password' => [
                'strong_password' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร ประกอบด้วยตัวพิมพ์ใหญ่, พิมพ์เล็ก, ตัวเลข และสัญลักษณ์พิเศษ (!@#$%^&*)'
            ],
            'cid' => [ // <-- จุดที่แก้ไข
                'valid_cid' => 'เลขบัตรประชาชนไม่ถูกต้อง',
                'is_unique' => 'เลขบัตรประชาชนนี้ถูกใช้ลงทะเบียนแล้ว'
            ]
        ];

        // 2. ตรวจสอบข้อมูลพร้อมกับข้อความ Error ที่กำหนดเอง
        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $data = [
            'fullname'        => $this->request->getPost('fullname'),
            'position'        => $this->request->getPost('position'),
            'cid'             => $this->request->getPost('cid'), // <-- ตรวจสอบเลขบัตรประชาชน
            'username'        => $this->request->getPost('username'),
            'password'        => $this->request->getPost('password'),
            'changwatcode'    => $this->request->getPost('changwatcode'),
            'ampurcodefull'   => $this->request->getPost('ampurcodefull'),
            'hospcode'        => $this->request->getPost('hospcode'), // <-- บันทึก hospcode
            'villagecodefull' => $this->request->getPost('villagecodefull'), // <-- เพิ่มข้อมูลหมู่บ้าน
            'status'          => 0, // 0 = รออนุมัติ
        ];

        // ตรวจสอบว่ามีข้อมูลจาก LINE ใน session หรือไม่
        if (session()->has('line_register_data')) {
            $lineData = session()->get('line_register_data');
            $data['line_user_id'] = $lineData['line_user_id'];
            // ไม่ต้องตั้งรหัสผ่าน ถ้าสมัครผ่าน LINE (อาจจะให้ตั้งทีหลัง)
        }
        if ($userModel->save($data)) {
            log_activity('register', "สมัครสมาชิกใหม่ username: {$data['username']}");
            session()->remove('line_register_data'); // ล้าง session หลังสมัครสำเร็จ
            return redirect()->to('/login')->with('message', 'สมัครสมาชิกสำเร็จ! กรุณารอการอนุมัติ');
        }
        log_activity('register_fail', "สมัครสมาชิกไม่สำเร็จ username: {$data['username']}");
        return redirect()->back()->withInput()->with('error', 'เกิดข้อผิดพลาด');
    }

    /**
     * เริ่มต้นกระบวนการ Login ด้วย LINE
     */
    public function lineLogin()
    {
        log_activity('line_login', 'เริ่มต้น LINE Login');
        $state = bin2hex(random_bytes(16));
        session()->set('line_state', $state);

        $uri = "https://access.line.me/oauth2/v2.1/authorize";
        $params = [
            'response_type' => 'code',
            'client_id'     => getenv('line.channelId'),
            'redirect_uri'  => getenv('line.callbackUrl'),
            'state'         => $state,
            'scope'         => 'profile openid email',
        ];

        return redirect()->to($uri . '?' . http_build_query($params));
    }

    /**
     * รับข้อมูลหลังจากผู้ใช้ยืนยันตัวตนที่ LINE
     */
    public function lineCallback()
    {
        log_activity('line_callback', 'LINE Callback');
        $code = $this->request->getVar('code');
        $state = $this->request->getVar('state');

        // ตรวจสอบ state เพื่อป้องกัน CSRF attack
        if (empty($state) || $state !== session()->get('line_state')) {
            return redirect()->to('/login')->with('error', 'Invalid state');
        }

        try {
            // 1. แลก Code เป็น Access Token
            $client = \Config\Services::curlrequest();
            $tokenResponse = $client->post('https://api.line.me/oauth2/v2.1/token', [
                'form_params' => [
                    'grant_type'    => 'authorization_code',
                    'code'          => $code,
                    'redirect_uri'  => getenv('line.callbackUrl'),
                    'client_id'     => getenv('line.channelId'),
                    'client_secret' => getenv('line.channelSecret'),
                ]
            ]);
            $tokenData = json_decode($tokenResponse->getBody());

            // 2. ใช้ Access Token เพื่อดึงข้อมูล Profile
            $profileResponse = $client->get('https://api.line.me/v2/profile', [
                'headers' => ['Authorization' => 'Bearer ' . $tokenData->access_token]
            ]);
            $lineProfile = json_decode($profileResponse->getBody());

            // 3. ตรวจสอบว่ามีผู้ใช้ที่ผูกกับ LINE ID นี้แล้วหรือยัง
            $userModel = new UserModel();
            $user = $userModel->where('line_user_id', $lineProfile->userId)->first();

            echo '<pre>';
            print_r($user);
            echo '</pre>';
            //exit;
            if ($user) {
                // --- กรณีเคยผูกบัญชีแล้ว: ทำการ Login ทันที ---
                if ($user['status'] != 1) { // ตรวจสอบสถานะก่อน
                    return redirect()->to('/login')->with('error', 'บัญชีของคุณยังไม่ได้รับการอนุมัติหรือถูกระงับ');
                }
                $this->createUserSession($user);
                return redirect()->to('/dashboard');
            } else {
                // --- กรณีผู้ใช้ใหม่: ส่งไปหน้าลงทะเบียน ---
                $lineData = [
                    'line_user_id' => $lineProfile->userId,
                    'fullname'     => $lineProfile->displayName,
                ];
                session()->set('line_register_data', $lineData);
                return redirect()->to('/register');
            }
        } catch (\Exception $e) {
            log_message('error', '[LINE Login] ' . $e->getMessage());
            log_activity('line_callback_error', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับ LINE: ' . $e->getMessage());
            return redirect()->to('/login')->with('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับ LINE');
        }
    }

    /**
     * ฟังก์ชันสร้าง Session (แยกออกมาเพื่อใช้ซ้ำ)
     */
    private function createUserSession(array $user)
    {
        $userRoleModel = new UserRoleModel();
        $roles = $userRoleModel->getRolesForUser($user['id']);

        $sessionData = [
            'user_id'       => $user['id'],
            'username'      => $user['username'],
            'fullname'      => $user['fullname'],
            'changwatcode'  => $user['changwatcode'],   // <-- เพิ่ม
            'ampurcodefull' => $user['ampurcodefull'],
            'hospcode'      => $user['hospcode'],
            'villagecode' => $user['villagecodefull'], // <-- เพิ่ม
            'roles'         => $roles,
            'isLoggedIn'    => true,
        ];
        session()->set($sessionData);
    }


    /**
     * ฟังก์ชันสำหรับ AJAX เพื่อดึงข้อมูลอำเภอ
     */
    public function getAmphures()
    {
        log_activity('get_amphures', 'ajax ดึงข้อมูลอำเภอ');
        $provinceCode = $this->request->getVar('province_code');
        $campurModel = new CampurModel();
        $amphures = $campurModel->where('changwatcode', $provinceCode)->orderBy('ampurname', 'ASC')->findAll();
        return $this->response->setJSON($amphures);
    }
    public function getHospitals()
    {
        log_activity('get_hospitals', 'ajax ดึงข้อมูลโรงพยาบาล');
        $amphureCode = $this->request->getVar('amphurcode'); // รหัสอำเภอ 4 หลัก
        if (strlen($amphureCode) !== 4) {
            return $this->response->setJSON([]);
        }
        $provCode = substr($amphureCode, 0, 2);
        $distCode = substr($amphureCode, 2, 2);

        $hospitalModel = new ChospitalModel();
        // ค้นหา รพ.สต. (hostype '07') และ สสช. (hostype '08')
        $hospitals = $hospitalModel->where('provcode', $provCode)
            ->where('distcode', $distCode)
            ->whereIn('hostype', ['01', '02', '06', '07', '08', '13', '18'])
            ->orderBy('hosname', 'ASC')
            ->findAll();
        return $this->response->setJSON($hospitals);
    }

    // <-- อัปเดตฟังก์ชันดึงข้อมูลหมู่บ้าน ให้รับ hospcode แทน -->

    // <-- ฟังก์ชันใหม่สำหรับดึงข้อมูลหมู่บ้าน -->
    public function getVillages()
    {
        log_activity('get_villages', 'ajax ดึงข้อมูลหมู่บ้าน');
        $hospcode = $this->request->getVar('hospcode');
        $cvillageModel = new CvillageModel();
        $villages = $cvillageModel->where('hospcode', $hospcode)->orderBy('villagename', 'ASC')->findAll();
        return $this->response->setJSON($villages);
    }
    /**
     * ทำการ Logout ออกจากระบบ
     */
    public function logout()
    {
        log_activity('logout', 'ออกจากระบบ');
        session()->destroy();
        return redirect()->to('login');
    }
}
