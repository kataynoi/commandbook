<?php
// --------------------------------------------------------------------
// File: app/Config/Filters.php (แก้ไขไฟล์นี้)
// --------------------------------------------------------------------
// สร้าง Alias สำหรับ Filter
// --------------------------------------------------------------------
// ... ใน public array $aliases



// --------------------------------------------------------------------
// File: app/Filters/AuthFilter.php (สร้างไฟล์ใหม่)
// --------------------------------------------------------------------
// Filter สำหรับตรวจสอบว่าผู้ใช้ Login แล้วหรือยัง
// --------------------------------------------------------------------
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // ถ้า session 'isLoggedIn' ไม่มีอยู่ หรือเป็น false
        if (!session()->get('isLoggedIn')) {
            // ให้ redirect ไปหน้า login
            return redirect()->to('login');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // ไม่ต้องทำอะไร
    }
}

// --------------------------------------------------------------------
// File: app/Controllers/Dashboard.php (Controller ตัวอย่างสำหรับทดสอบ)
// --------------------------------------------------------------------
namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        // ใช้ Template ที่เราสร้างไว้
        return view('dashboard');
    }
}

?>