<?php

namespace App\Controllers;

use App\Models\CommandDocumentModel;
use App\Models\UserModel;
use App\Models\ActivityLogModel;

class DashboardController extends BaseController
{
    protected $docModel;
    protected $userModel;
    protected $logModel;

    public function __construct()
    {
        $this->docModel = new CommandDocumentModel();
        $this->userModel = new UserModel();
        $this->logModel = new ActivityLogModel();
    }

    public function index()
    {
        // ตรวจสอบการ Login
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Dashboard - ระบบหนังสือคำสั่ง'
        ];

        return view('dashboard/dashboard_view', $data);
    }

    /**
     * API สำหรับดึงข้อมูล Dashboard (AJAX)
     */
    public function getData()
    {
        try {
            // 1. จำนวนหนังสือคำสั่งทั้งหมด
            $totalDocuments = $this->docModel->countAll();

            // 2. จำนวนหนังสือคำสั่งเดือนนี้
            $currentMonth = date('Y-m');
            $documentsThisMonth = $this->docModel
                ->where('DATE_FORMAT(created_at, "%Y-%m")', $currentMonth)
                ->countAllResults();

            // 3. จำนวนผู้ใช้งานทั้งหมด
            $totalUsers = $this->userModel->where('status', 1)->countAllResults();

            // 4. จำนวนผู้ใช้งานรออนุมัติ
            $pendingUsers = $this->userModel->where('status', 0)->countAllResults();

            // 5. จำนวนการดาวน์โหลดทั้งหมด
            $totalDownloads = $this->logModel
                ->where('action', 'download_document')
                ->countAllResults();

            // 6. จำนวนการดาวน์โหลดเดือนนี้
            $downloadsThisMonth = $this->logModel
                ->where('action', 'download_document')
                ->where('DATE_FORMAT(created_at, "%Y-%m")', $currentMonth)
                ->countAllResults();

            // 7. หนังสือล่าสุด 5 รายการ
            $recentDocuments = $this->docModel
                ->select('command_documents.*, users.fullname as uploader_name')
                ->join('users', 'users.id = command_documents.uploaded_by', 'left')
                ->orderBy('command_documents.created_at', 'DESC')
                ->limit(5)
                ->find();

            // 8. สถิติการดาวน์โหลดแยกตามเอกสาร (Top 10)
            $topDownloads = $this->logModel
                ->select('activity_logs.target_id, COUNT(*) as download_count, command_documents.doc_title, command_documents.doc_number')
                ->join('command_documents', 'command_documents.id = activity_logs.target_id', 'left')
                ->where('activity_logs.action', 'download_document')
                ->groupBy('activity_logs.target_id')
                ->orderBy('download_count', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();

            // 9. สถิติการอัปโหลดแยกตามเดือน (6 เดือนล่าสุด)
            $uploadsByMonth = $this->docModel
                ->select('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->where('created_at >=', date('Y-m-01', strtotime('-5 months')))
                ->groupBy('month')
                ->orderBy('month', 'ASC')
                ->get()
                ->getResultArray();

            // 10. สถิติการดาวน์โหลดแยกตามเดือน (6 เดือนล่าสุด)
            $downloadsByMonth = $this->logModel
                ->select('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->where('action', 'download_document')
                ->where('created_at >=', date('Y-m-01', strtotime('-5 months')))
                ->groupBy('month')
                ->orderBy('month', 'ASC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'totalDocuments' => $totalDocuments,
                    'documentsThisMonth' => $documentsThisMonth,
                    'totalUsers' => $totalUsers,
                    'pendingUsers' => $pendingUsers,
                    'totalDownloads' => $totalDownloads,
                    'downloadsThisMonth' => $downloadsThisMonth,
                    'recentDocuments' => $recentDocuments,
                    'topDownloads' => $topDownloads,
                    'uploadsByMonth' => $uploadsByMonth,
                    'downloadsByMonth' => $downloadsByMonth
                ]
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}