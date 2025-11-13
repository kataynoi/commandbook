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
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        if (empty($token)) {
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        // หาเอกสารจาก token
        $doc = $this->docModel->where('qr_token', $token)->first();
        if (! $doc) {
            log_message('warning', 'Access::index token not found: ' . $token);
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        // ตรวจสิทธิ์: role 1/2 ข้ามการตรวจ, คนอื่นตรวจ hospcode
        $roles = session()->get('roles') ?? [];
        $isAdmin = in_array(1, (array)$roles) || in_array(2, (array)$roles);
        //dd($isAdmin); // แสดงค่า $isAdmin แล้วหยุดทำงาน
        if (! $isAdmin) {
            $userHosp = session()->get('hospcode');
            if (empty($userHosp)) {
                return $this->response->setStatusCode(403)->setBody('Access Denied');
            }
            // รองรับทั้ง schema ที่เก็บ doc_id หรือ command_id
            $has = $this->accessModel
                        ->groupStart()
                            ->Where('command_id', $doc['id'])
                        ->groupEnd()
                        ->where('hospcode', $userHosp)
                        ->first();
            if (! $has) {
                log_message('warning', 'Access::index forbidden. token=' . $token . ' hosp=' . $userHosp);
                return $this->response->setStatusCode(403)->setBody('Access Denied');
            }
        }

        // ตรวจไฟล์บนดิสก์
        $rel = $doc['file_path'] ?? null; // ควรเป็น 'uploads/commands/<file>'
        if (empty($rel)) {
            log_message('error', 'Access::index missing file_path for doc=' . ($doc['id'] ?? 'n/a'));
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        $path = WRITEPATH . $rel;
        if (! is_file($path) || ! is_readable($path)) {
            log_message('error', 'Access::index file missing/unreadable: ' . $path);
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        // ส่งไฟล์ PDF inline (หรือใช้ Content-Type ตามจริง)
        $fileName = $doc['file_name'] ?? basename($path);
        return $this->response->setHeader('Content-Type', 'application/pdf')
                              ->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"')
                              ->setBody(file_get_contents($path));
    }
}