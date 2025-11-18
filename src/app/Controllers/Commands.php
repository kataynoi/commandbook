<?php namespace App\Controllers;

use App\Models\CommandDocumentModel;
use App\Models\CommandAccessModel;
use App\Models\ChospitalModel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel; // <-- อย่าลืม import บรรทัดนี้
use chillerlan\QRCode\Common\Version;


class Commands extends BaseController
{
    protected $session;
    protected $docModel;
    protected $accessModel;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->docModel = new CommandDocumentModel();
        $this->accessModel = new CommandAccessModel();
        helper(['filesystem', 'text', 'form', 'url']);
    }

    /**
     * หน้า Dashboard (หน้าแรกหลังล็อกอิน)
     */
    public function index()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        return view('command_list');
    }

    /**
     * Data source for DataTables server-side processing
     */
    public function fetch()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400);
        }

        $builder = $this->docModel
            ->select('command_documents.id, command_documents.doc_number, command_documents.doc_title, command_documents.doc_date, command_documents.qr_token, users.fullname as uploader_name, command_documents.created_at') // <-- เพิ่ม created_at ตรงนี้
            ->join('users', 'users.id = command_documents.uploaded_by', 'left')
            ->groupBy('command_documents.id');

        // Normalize roles
        $rawRoles = session()->get('roles');
        if (is_string($rawRoles)) {
            $decoded = json_decode($rawRoles, true);
            $rawRoles = $decoded !== null ? $decoded : $rawRoles;
        }
        $roles = array_map('intval', (array) $rawRoles);
        $isAdminOrUploader = count(array_intersect($roles, [1,2])) > 0;

        try {
            if ($isAdminOrUploader) {
                // Admin/Uploader เห็นทั้งหมด
                $docs = $builder->orderBy('command_documents.created_at', 'DESC')->findAll();
            } else {
                // User ทั่วไปเห็นเฉพาะที่ได้รับสิทธิ์
                $userHosp = session()->get('hospcode');
                $accessDocIds = $this->accessModel->where('hospcode', $userHosp)->findColumn('command_id');
                if ($accessDocIds === null) {
                    $accessDocIds = [];
                }
                
                if (empty($accessDocIds)) {
                    $docs = [];
                } else {
                    $docs = $builder->whereIn('command_documents.id', $accessDocIds)
                                    ->orderBy('command_documents.created_at', 'DESC')
                                    ->findAll();
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Commands::fetch model error: ' . $e->getMessage());
            $docs = [];
        }

        $data = [];
        foreach ($docs as $d) {
            $qr = isset($d['qr_token']) ? $d['qr_token'] : (isset($d['token']) ? $d['token'] : (isset($d['file_token']) ? $d['file_token'] : ''));
            $data[] = [
                'id' => $d['id'],
                'doc_number' => $d['doc_number'],
                'doc_title' => $d['doc_title'],
                'doc_date' => $d['doc_date'],
                'uploader_name' => isset($d['uploader_name']) ? $d['uploader_name'] : 'N/A',
                'qr_token' => $qr,
                'created_at' => $d['created_at'],
            ];
        }

        log_message('debug', 'Commands::fetch returned ' . count($data) . ' docs');

        return $this->response->setJSON(['data' => $data]);
    }

    public function get($id = null)
    {
        if (! $this->request->isAJAX() || empty($id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid Request']);
        }

        try {
            // 1. ดึงข้อมูลเอกสารหลักพร้อมชื่อผู้อัปโหลด
            $doc = $this->docModel
                        ->select('command_documents.*, users.fullname as uploader_name')
                        ->join('users', 'users.id = command_documents.uploaded_by', 'left')
                        ->find($id);

            if (! $doc) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Document not found']);
            }

            // 2. ดึงรายชื่อหน่วยงานที่ได้รับสิทธิ์ พร้อมชื่อเต็ม
            $accessList = $this->accessModel
                               ->select('command_access.hospcode, chospital.hospname')
                               ->join('chospital', 'chospital.hospcode = command_access.hospcode', 'left')
                               ->where('command_access.command_id', $id)
                               ->orderBy('chospital.hospname', 'ASC')
                               ->findAll();

            // 3. เพิ่มรายชื่อหน่วยงานเข้าไปในข้อมูลที่จะส่งกลับไป
            $doc['access_list'] = $accessList;

            return $this->response->setJSON($doc);

        } catch (\Throwable $e) {
            log_message('error', 'Commands::get error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Server error']);
        }
    }

    /**
     * ลบเอกสาร (เฉพาะ role 1,2)
     */
    public function delete($id = null)
    {
        // Normalize id: accept parameter, POST or GET
        $commandId = $id;
        if (empty($commandId)) {
            $postId = $this->request->getPost('id');
            if (!empty($postId)) {
                $commandId = $postId;
            } else {
                $getId = $this->request->getGet('id');
                $commandId = !empty($getId) ? $getId : null;
            }
        }
        $roles = session()->get('roles');
        if ($roles === null) {
            $roles = [];
        }
        if (empty($commandId)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing id']);
        }

        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $roles = session()->get('roles');
        if ($roles === null) {
            $roles = [];
        }

        if (! (in_array(1, $roles) || in_array(2, $roles))) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $doc = $this->docModel->find($commandId);
        if (! $doc) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        // ลบไฟล์จริงถ้ามี (ปรับ field ชื่อ file_path ตาม model ของคุณ)
        if (! empty($doc['file_path'])) {
            @unlink(WRITEPATH . $doc['file_path']);
        }

        if ($this->docModel->delete($commandId)) {
            // ลบ access records ด้วย
            $this->accessModel->where('command_id', $commandId)->delete();
            return $this->response->setJSON(['success' => true]);
        }

        return $this->response->setStatusCode(500)->setJSON(['error' => 'Delete failed']);
    }

    /**
     * หน้าอัปโหลดใหม่ / แก้ไข (เซ็ต $hospitals ให้ view)
     */
    public function create()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $hosModel = new ChospitalModel();
        $rawRoles = session()->get('roles');
        if ($rawRoles === null) {
            $rawRoles = [];
        }
    
        // โหลดรายการหน่วยงานเพื่อส่งให้ view
        $hospitals = $hosModel->orderBy('hospname', 'ASC')->findAll();
    
        $data = [
            'hospitals' => $hospitals,
            'doc' => [], // ค่าเริ่มต้นสำหรับฟอร์ม
            'access' => [] // ค่าเริ่มต้นสำหรับฟอร์ม
        ];

        // ตรวจสอบว่าเป็นการแก้ไขหรือไม่
        $editId = $this->request->getGet('edit');
        if ($editId) {
            // --- เพิ่มการตรวจสอบสิทธิ์ ---
            $rawRoles = session()->get('roles');
            if ($rawRoles === null) {
                $rawRoles = [];
            }
            $roles = array_map('intval', (array) $rawRoles);
            $canEdit = in_array(1, $roles, true) || in_array(2, $roles, true);

            if (!$canEdit) {
                // ถ้าไม่มีสิทธิ์ ให้ redirect กลับไปหน้าหลักพร้อมข้อความแจ้งเตือน
                return redirect()->to('/commands')->with('error', 'คุณไม่มีสิทธิ์แก้ไขเอกสารนี้');
            }
            // --- จบการตรวจสอบสิทธิ์ ---

            $doc = $this->docModel->find($editId);
            if ($doc) {
                $data['doc'] = $doc;
                // ดึงรายการ hospcode ที่เคยเลือกไว้สำหรับเอกสารนี้
                $accessList = $this->accessModel
                                   ->where('command_id', $editId)
                                   ->findColumn('hospcode');
                $data['access'] = $accessList !== null ? $accessList : [];
            }
        }

        return view('upload_form', $data);
    }

    /**
     * บันทึกการอัปโหลด (validate, move uploaded file, save record, save access records)
     */
    public function save()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // 1. ตรวจสอบว่าเป็นโหมดแก้ไขหรือไม่ โดยการอ่านค่า 'id' จากฟอร์ม
        $id = $this->request->getPost('id');
        $isEdit = !empty($id);

        // 2. กำหนด Validation Rules
        $rules = [
            'doc_number' => 'required|string|max_length[100]',
            'doc_title'  => 'required|string|max_length[255]',
            'doc_date'   => 'required|valid_date',
            'hospcodes'  => 'required',
        ];

        // กฎสำหรับไฟล์: บังคับให้อัปโหลดเฉพาะตอน "สร้างใหม่" เท่านั้น
        // ตอน "แก้ไข" การอัปโหลดไฟล์เป็นทางเลือก
        if (!$isEdit) {
            $rules['command_file'] = [
                'rules' => 'uploaded[command_file]|mime_in[command_file,application/pdf]|max_size[command_file,20480]', // 20MB
                'errors' => [
                    'uploaded' => 'กรุณาเลือกไฟล์คำสั่ง',
                    'mime_in' => 'ต้องเป็นไฟล์ .pdf เท่านั้น',
                    'max_size' => 'ไฟล์มีขนาดใหญ่เกิน 20MB',
                ]
            ];
        } else {
            // ถ้าเป็นการแก้ไข และมีการอัปโหลดไฟล์ใหม่ ให้ตรวจสอบไฟล์นั้นด้วย
            if ($this->request->getFile('command_file') && $this->request->getFile('command_file')->isValid()) {
                 $rules['command_file'] = [
                    'rules' => 'mime_in[command_file,application/pdf]|max_size[command_file,20480]',
                    'errors' => [
                        'mime_in' => 'ต้องเป็นไฟล์ .pdf เท่านั้น',
                        'max_size' => 'ไฟล์มีขนาดใหญ่เกิน 20MB',
                    ]
                ];
            }
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->listErrors());
        }

        // 3. จัดการไฟล์ (ถ้ามีการอัปโหลด)
        $file = $this->request->getFile('command_file');
        $fileData = [];

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/commands', $newName);
            
            $fileData = [
                'file_name'  => $file->getClientName(), // ชื่อไฟล์เดิม
                'file_path'  => 'uploads/commands/' . $newName, // เส้นทางที่เก็บจริง
                'file_size'  => $file->getSize(), // ขนาดไฟล์ (bytes)
            ];

            // ถ้าเป็นการแก้ไขและมีไฟล์เก่า ให้ลบไฟล์เก่าทิ้ง
            if ($isEdit) {
                $oldDoc = $this->docModel->find($id);
                if ($oldDoc && !empty($oldDoc['file_path'])) {
                    @unlink(WRITEPATH . $oldDoc['file_path']);
                }
            }
        }

        // 4. เตรียมข้อมูลเพื่อบันทึกลง Database
        $docData = [
            'doc_number' => $this->request->getPost('doc_number'),
            'doc_title'  => $this->request->getPost('doc_title'),
            'description'  => $this->request->getPost('description'),
            'doc_date'   => $this->request->getPost('doc_date'),
            'uploaded_by'=> $this->session->get('user_id')
        ];

        // รวมข้อมูลไฟล์เข้าไป (ถ้ามี)
        if (!empty($fileData)) {
            $docData = array_merge($docData, $fileData);
        }

        // ถ้าเป็นการสร้างใหม่ ให้สร้าง qr_token ด้วย
        if (!$isEdit) {
            $docData['qr_token'] = bin2hex(random_bytes(32));
        }

        // 5. บันทึกข้อมูลลง Database (ใช้ Transaction)
        $this->db = \Config\Database::connect();
        $this->db->transStart();

        try {
            $commandId = $id; // ใช้ ID เดิมสำหรับการแก้ไข
            if ($isEdit) {
                // โหมดแก้ไข: อัปเดตข้อมูลเดิม
                $this->docModel->update($id, $docData);
            } else {
                // โหมดสร้างใหม่: เพิ่มข้อมูลใหม่
                $this->docModel->insert($docData);
                $commandId = $this->docModel->getInsertID(); // ดึง ID ที่เพิ่งสร้าง
            }

            // อัปเดตตาราง command_access
            $hospcodes = $this->request->getPost('hospcodes');
            // ลบของเก่าออกทั้งหมดก่อน แล้วค่อยเพิ่มของใหม่
            $this->accessModel->where('command_id', $commandId)->delete();
            
            $accessData = [];
            foreach ($hospcodes as $hospcode) {
                $accessData[] = ['command_id' => $commandId, 'hospcode' => $hospcode];
            }
            if (!empty($accessData)) {
                $this->accessModel->insertBatch($accessData);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return redirect()->back()->withInput()->with('errors', 'ไม่สามารถบันทึกข้อมูลได้');
            }

            // 6. Redirect ไปหน้า Success หรือหน้า List
            if ($isEdit) {
                return redirect()->to('/commands')->with('message', 'แก้ไขเอกสารเรียบร้อยแล้ว');
            } else {
                return redirect()->to('commands/success')->with('qr_token', $docData['qr_token']);
            }

        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('errors', $e->getMessage());
        }
    }

    /**
     * หน้า success
     */
    public function success()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // ดึงค่า qr_token จาก flashdata ที่ส่งมากับ redirect
        $data['qr_token'] = session()->getFlashdata('qr_token');

        // ถ้าไม่มี token อาจจะ redirect กลับหรือแสดงข้อความ
        if (empty($data['qr_token'])) {
            log_message('warning', 'Accessed success page without a qr_token.');
        }

        return view('upload_success', $data);
    }

    public function qr($token = null)
    {
        if (empty($token)) {
            return $this->response->setStatusCode(404);
        }

        try {
            // URL ที่จะให้ QR Code ชี้ไป
            $accessUrl = site_url('access/' . $token);

            // ตั้งค่าสำหรับไลบรารี QR Code
            $options = new QROptions([
                'version'      => Version::AUTO, // ความซับซ้อนของ QR, 5 เพียงพอสำหรับ URL
                'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'     => QRCode::ECC_L, // Error Correction Level
                'scale'        => 5, // ขนาดของแต่ละจุด
                'imageBase64'  => false, // เราต้องการข้อมูลภาพดิบ ไม่ใช่ base64
            ]);

            // สร้าง QR Code
            $qrcode = new QRCode($options);
            $imageData = $qrcode->render($accessUrl);

            // ส่งข้อมูลภาพกลับไปให้เบราว์เซอร์
            return $this->response
                        ->setBody($imageData)
                        ->setHeader('Content-Type', 'image/png')
                        ->setHeader('Content-Length', strlen($imageData));

        } catch (\Exception $e) {
            log_message('error', 'QR Generation Failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500, 'Could not generate QR code.');
        }
    }
}