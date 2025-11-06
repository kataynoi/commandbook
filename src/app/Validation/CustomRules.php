<?php
// --------------------------------------------------------------------
// (1/7) สร้าง Custom Validation Rule
// --------------------------------------------------------------------
// สร้างไฟล์ใหม่ที่: app/Validation/CustomRules.php
// ไฟล์นี้ใช้สำหรับสร้างกฎการตรวจสอบข้อมูลของเราเอง

namespace App\Validation;

class CustomRules
{
    /**
     * ตรวจสอบความแข็งแรงของรหัสผ่าน
     * - ต้องมีอย่างน้อย 8 ตัวอักษร
     * - ต้องมีตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว (A-Z)
     * - ต้องมีตัวพิมพ์เล็กอย่างน้อย 1 ตัว (a-z)
     * - ต้องมีตัวเลขอย่างน้อย 1 ตัว (0-9)
     * - ต้องมีสัญลักษณ์พิเศษอย่างน้อย 1 ตัว (!@#$%^&*)
     */
    public function strong_password(string $str): bool
    {
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/';
        return (bool) preg_match($pattern, $str);
    }

    
    /**
     * ตรวจสอบความถูกต้องของเลขบัตรประชาชนไทย (Thai CID)
     * ตามอัลกอริทึม Checksum
     */
    public function valid_cid(string $str): bool
    {
        if (strlen($str) !== 13 || !is_numeric($str)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$str[$i] * (13 - $i);
        }

        $checkDigit = (11 - ($sum % 11)) % 10;

        return (int)$str[12] === $checkDigit;
    }
}
?>