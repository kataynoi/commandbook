<?php namespace App\Controllers;

use App\Models\CommandDocumentModel;
use App\Models\CommandAccessModel;
use App\Models\ChospitalModel;

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
     * DataTable AJAX source: return JSON list of documents filtered by role
     */
    public function fetch()
    {
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

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
                $docs = $this->docModel->orderBy('created_at', 'DESC')->findAll();
            } else {
                $userHosp = session()->get('hospcode');
                $accessDocIds = $this->accessModel->where('hospcode', $userHosp)->findColumn('doc_id') ?? [];
                $docs = empty($accessDocIds)
                    ? []
                    : $this->docModel->whereIn('id', $accessDocIds)->orderBy('created_at', 'DESC')->findAll();
            }
        } catch (\Throwable $e) {
            log_message('error', 'Commands::fetch model error: ' . $e->getMessage());
            $docs = [];
        }

        $data = [];
        foreach ($docs as $d) {
            // รองรับชื่อคอลัมน์หลายรูปแบบ (qr_token, token, file_token)
            $qr = $d['qr_token'] ?? $d['token'] ?? $d['file_token'] ?? $d['file_path'] ?? '';
            // ถ้า file_path เก็บ path ให้ลองสร้าง token-like ค่า (ไม่บังคับ)
            $data[] = [
                'id' => $d['id'] ?? ($d['command_id'] ?? null),
                'doc_number' => $d['doc_number'] ?? ($d['number'] ?? ''),
                'doc_title' => $d['doc_title'] ?? ($d['title'] ?? ''),
                'doc_date' => $d['doc_date'] ?? '',
                'uploader_name' => $d['uploader_name'] ?? $d['created_by'] ?? session()->get('username'),
                'qr_token' => $qr,
                'created_at' => $d['created_at'] ?? '',
            ];
        }

        log_message('debug', 'Commands::fetch returned ' . count($data) . ' docs');

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * คืนรายละเอียดเอกสาร (ใช้เมื่อคลิกชื่อแสดงรายละเอียด)
     */
    public function get($id = null)
    {
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        // Ensure $id is available: accept it from parameter, route, query or POST
        $id = $id ?? $this->request->getVar('id') ?? $this->request->getGet('id') ?? null;
        if ($id === null) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing id']);
        }

        $doc = $this->docModel->find($id);
        if (! $doc) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        $roles = session()->get('roles') ?? [];
        $isAdminOrUploader = in_array(1, $roles) || in_array(2, $roles);
        if (! $isAdminOrUploader) {
            $userHosp = session()->get('hospcode');
            $has = $this->accessModel->where('doc_id', $id)->where('hospcode', $userHosp)->first();
            if (! $has) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
            }
        }

        return $this->response->setJSON($doc);
    }

    /**
     * ลบเอกสาร (เฉพาะ role 1,2)
     */
    public function delete($id = null)
    {
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $roles = session()->get('roles') ?? [];
        if (! (in_array(1, $roles) || in_array(2, $roles))) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $doc = $this->docModel->find($id);
        if (! $doc) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        // ลบไฟล์จริงถ้ามี (ปรับ field ชื่อ file_path ตาม model ของคุณ)
        if (! empty($doc['file_path'])) {
            @unlink(WRITEPATH . $doc['file_path']);
        }

        if ($this->docModel->delete($id)) {
            // ลบ access records ด้วย
            $this->accessModel->where('doc_id', $id)->delete();
            return $this->response->setJSON(['success' => true]);
        }

        return $this->response->setStatusCode(500)->setJSON(['error' => 'Delete failed']);
    }

    /**
     * หน้าอัปโหลดใหม่ / แก้ไข (เซ็ต $hospitals ให้ view)
     */
    public function new()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $hosModel = new ChospitalModel();
        $hospitals = $hosModel->orderBy('hospname', 'ASC')->findAll();

        $data = ['hospitals' => $hospitals];

        // ถ้ามี ?edit=ID ให้โหลดข้อมูลมาแสดง
        $editId = $this->request->getGet('edit');
        if ($editId) {
            $doc = $this->docModel->find($editId);
            if ($doc) {
                $data['doc'] = $doc;
                $data['access'] = $this->accessModel->where('doc_id', $editId)->findColumn('hospcode') ?? [];
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

        $id = $this->request->getPost('id'); // มีเมื่อแก้ไข
        $isEdit = ! empty($id);

        $rules = [
            'doc_number' => 'required|max_length[255]',
            'doc_title'  => 'required|max_length[1000]',
            'doc_date'   => 'required',
            'hospcodes'  => 'required'
        ];

        if (! $isEdit) {
            $rules['command_file'] = 'uploaded[command_file]|max_size[command_file,102400]|mime_in[command_file,application/pdf]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('command_file');

        if (! $isEdit) {
            if (! $file || ! $file->isValid()) {
                $errMsg = 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์';
                if ($file) {
                    $errCode = $file->getError();
                    $errMsg .= " (upload error code: {$errCode})";
                    log_message('error', "Commands::save upload error code={$errCode}");
                } else {
                    log_message('error', 'Commands::save no file uploaded');
                }
                return redirect()->back()->withInput()->with('error', $errMsg);
            }
        }

        // เตรียมตัวแปรเก็บข้อมูลไฟล์
        $filePath = null;
        $fileName = null;
        $fileSize = null;

        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $destDir = WRITEPATH . 'uploads/commands/';
            if (! is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            try {
                // เก็บข้อมูลต้นฉบับก่อนย้าย
                $fileName = $file->getClientName();
                $fileSize = (int) $file->getSize();

                $newName = $file->getRandomName();
                $file->move($destDir, $newName);
                $filePath = 'uploads/commands/' . $newName;

                log_message('info', "Commands::save file moved to {$filePath}; original={$fileName}; size={$fileSize}");
            } catch (\Exception $e) {
                log_message('error', 'Commands::save move failed: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'ไม่สามารถบันทึกไฟล์ได้ กรุณาตรวจสอบสิทธิ์โฟลเดอร์');
            }
        }

        $uploader = session()->get('username') ?? 'system';
        $qrToken = $this->request->getPost('qr_token') ?? bin2hex(random_bytes(12));
        $uploadedBy = session()->get('user_id') ?? null;
        if (empty($uploadedBy)) {
            log_message('error', 'Commands::save missing session user_id');
            return redirect()->back()->withInput()->with('error', 'ไม่พบข้อมูลผู้ใช้งาน โปรดล็อกอินใหม่');
        }

        $data = [
            'doc_number'   => $this->request->getPost('doc_number'),
            'doc_title'    => $this->request->getPost('doc_title'),
            'doc_date'     => $this->request->getPost('doc_date'),
            'description'  => $this->request->getPost('description'),
            'qr_token'     => $qrToken,
            'file_path'    => $filePath ?? null,
            'file_name'    => $fileName ?? null,
            'file_size'    => $fileSize ?? 0,
            'uploaded_by'  => $uploadedBy,
            'created_at'   => date('Y-m-d H:i:s'),
        ];

        $this->docModel->transStart();

        if ($isEdit) {
            $ok = $this->docModel->update($id, $data);
            $docId = $id;
        } else {
            $insertId = $this->docModel->insert($data);
            $docId = $insertId ?: $this->docModel->getInsertID();
        }

        // บันทึกสิทธิ์: ใช้ column command_id ตาม schema
        $hospcodes = $this->request->getPost('hospcodes') ?? [];
        if (! is_array($hospcodes)) $hospcodes = [$hospcodes];

        // ลบสิทธิ์เก่าโดยใช้ command_id
        $this->accessModel->where('command_id', $docId)->delete();

        foreach ($hospcodes as $hc) {
            $this->accessModel->insert([
                'command_id' => $docId,
                'hospcode'   => $hc
            ]);
        }

        $this->docModel->transComplete();

        if (! $this->docModel->transStatus()) {
            log_message('error', 'Commands::save transaction failed. doc errors: ' . print_r($this->docModel->errors(), true));
            return redirect()->back()->withInput()->with('error', 'บันทึกไม่สำเร็จ ตรวจสอบ log');
        }

        // เก็บ token ให้ success view
        session()->setFlashdata('qr_token', $qrToken);

        return redirect()->to('/commands/success');
    }

    /**
     * หน้า success
     */
    public function success()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        return view('upload_success');
    }

    public function qr($token = null)
    {
        if (empty($token)) {
            return $this->response->setStatusCode(404);
        }

        // URL ที่ผู้ใช้จะเข้าถึงไฟล์จริง
        $accessUrl = site_url('access/' . $token);

        // Google Charts QR URL
        $googleQr = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=' . urlencode($accessUrl);

        // Fetch image from Google Charts (use curl if available, else file_get_contents)
        $img = false;
        $httpCode = 0;
        $error = null;

        if (function_exists('curl_init')) {
            $ch = curl_init($googleQr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $img = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);
            if ($curlErr) {
                $error = $curlErr;
            }
        } elseif (ini_get('allow_url_fopen')) {
            try {
                $context = stream_context_create(['http' => ['timeout' => 10]]);
                $img = @file_get_contents($googleQr, false, $context);
                // No easy HTTP code here; assume success if $img not false
            } catch (\Throwable $e) {
                $error = $e->getMessage();
                $img = false;
            }
        } else {
            $error = 'No HTTP client available (curl or allow_url_fopen required).';
        }

        if ($img === false || ($httpCode !== 0 && $httpCode >= 400)) {
            log_message('error', 'Commands::qr fetch failed. token=' . $token . ' http=' . $httpCode . ' err=' . ($error ?? 'none'));
            return $this->response->setStatusCode(502, 'Failed to fetch QR image');
        }

        // ส่งภาพกลับเป็น binary png
        return $this->response->setBody($img)
                             ->setHeader('Content-Type', 'image/png')
                             ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}