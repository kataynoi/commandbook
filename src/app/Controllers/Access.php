<?php namespace App\Controllers;

use App\Models\CommandDocumentModel;
use App\Models\CommandAccessModel;

class Access extends BaseController
{
    protected $docModel;
    protected $accessModel;

    public function __construct()
    {
        $this->docModel = new CommandDocumentModel();
        $this->accessModel = new CommandAccessModel();
        helper('filesystem');
    }

    // GET /access/{token}
    public function index($token = null)
    {
        if (empty($token)) {
            log_message('warning', 'Access::index no token provided');
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        // หาเอกสารจาก token
        $doc = $this->docModel->where('qr_token', $token)->first();
        if (! $doc) {
            log_message('warning', 'Access::index token not found: ' . $token);
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        // ตรวจว่ามีบันทึกการจำกัดการเข้าถึงสำหรับเอกสารนี้หรือไม่
        $accessCount = $this->accessModel->where('command_id', $doc['id'])
                                         ->orWhere('doc_id', $doc['id'])
                                         ->countAllResults();

        // ถ้ามี row ใน access table ให้ตรวจ hospcode (ต้องล็อกอินมี hospcode)
        if ($accessCount > 0) {
            $roles = session()->get('roles') ?? [];
            $isAdmin = in_array(1, (array)$roles) || in_array(2, (array)$roles);

            if (! $isAdmin) {
                $userHosp = session()->get('hospcode');
                if (empty($userHosp)) {
                    log_message('warning', 'Access::index denied - no hospcode in session. token=' . $token);
                    return $this->response->setStatusCode(403)->setBody('Access Denied');
                }

                $has = $this->accessModel
                            ->groupStart()
                                ->where('command_id', $doc['id'])
                                ->orWhere('doc_id', $doc['id'])
                            ->groupEnd()
                            ->where('hospcode', $userHosp)
                            ->first();

                if (! $has) {
                    log_message('warning', 'Access::index forbidden. token=' . $token . ' hosp=' . $userHosp);
                    return $this->response->setStatusCode(403)->setBody('Access Denied');
                }
            }
        } else {
            // ไม่มีการจำกัด -> อนุญาตดาวน์โหลดโดยไม่ต้องล็อกอิน (token เป็นสิทธิพิเศษ)
            log_message('info', 'Access::index public download by token=' . $token);
        }

        // ตรวจไฟล์บนดิสก์
        $rel = $doc['file_path'] ?? null; // คาดว่าเป็น 'uploads/commands/<file>'
        if (empty($rel)) {
            log_message('error', 'Access::index missing file_path for doc=' . ($doc['id'] ?? 'n/a'));
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        $path = WRITEPATH . $rel;
        if (! is_file($path) || ! is_readable($path)) {
            log_message('error', 'Access::index file missing/unreadable: ' . $path);
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        // ส่งไฟล์ (inline) พร้อมชื่อจริงถ้ามี
        $fileName = $doc['file_name'] ?? basename($path);
        $mime = mime_content_type($path) ?: 'application/octet-stream';

        return $this->response
                    ->setHeader('Content-Type', $mime)
                    ->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"')
                    ->setBody(file_get_contents($path));
    }
}