<?php

// app/Helpers/thai_date_helper.php
use CodeIgniter\I18n\Time;

if (!function_exists('to_thai_datetime')) {
    /**
     * แปลง YYYY-MM-DD HH:MM:SS เป็นรูปแบบวันที่และเวลาภาษาไทย
     * เช่น 3 สิงหาคม 2568 เวลา 22:16 น.
     *
     * @param string $dateTimeString ค่าวันที่และเวลา
     * @param bool   $includeTime    ต้องการให้แสดงเวลาด้วยหรือไม่
     * @param bool   $shortMonth     ใช้เดือนแบบย่อ (เช่น ม.ค.) หรือไม่
     * @return string วันที่รูปแบบภาษาไทย
     */
    function to_thai_datetime(string $dateTimeString, bool $includeTime = true, bool $shortMonth = false): string
    {
        if (empty($dateTimeString)) {
            return '';
        }

        $timestamp = strtotime($dateTimeString);

        $thai_day_arr = ["อาทิตย์", "จันทร์", "อังคาร", "พุธ", "พฤหัสบดี", "ศุกร์", "เสาร์"];

        $thai_month_arr = [
            "0" => "",
            "1" => "มกราคม",
            "2" => "กุมภาพันธ์",
            "3" => "มีนาคม",
            "4" => "เมษายน",
            "5" => "พฤษภาคม",
            "6" => "มิถุนายน",
            "7" => "กรกฎาคม",
            "8" => "สิงหาคม",
            "9" => "กันยายน",
            "10" => "ตุลาคม",
            "11" => "พฤศจิกายน",
            "12" => "ธันวาคม"
        ];

        $thai_month_arr_short = [
            "0" => "",
            "1" => "ม.ค.",
            "2" => "ก.พ.",
            "3" => "มี.ค.",
            "4" => "เม.ย.",
            "5" => "พ.ค.",
            "6" => "มิ.ย.",
            "7" => "ก.ค.",
            "8" => "ส.ค.",
            "9" => "ก.ย.",
            "10" => "ต.ค.",
            "11" => "พ.ย.",
            "12" => "ธ.ค."
        ];

        $day = date('d', $timestamp);
        $month = date('n', $timestamp); // 'n' for month number without leading zeros
        $year = date('Y', $timestamp) + 543; // แปลงเป็นปี พ.ศ.

        $month_name = $shortMonth ? $thai_month_arr_short[$month] : $thai_month_arr[$month];

        $formatted_date = "$day $month_name $year";

        if ($includeTime) {
            $time = date('H:i', $timestamp);
            $formatted_date .= " เวลา $time น.";
        }

        return $formatted_date;
    }
}
// In app/Helpers/date_helper.php



if (!function_exists('mysqlToThaiDate')) {
    /**
     * แปลงวันที่รูปแบบ MySQL เป็นวันที่รูปแบบไทย
     *
     * @param string $mysqlDate   วันที่ในรูปแบบ 'YYYY-MM-DD' หรือ 'YYYY-MM-DD HH:MM:SS'
     * @param bool   $shortMonth  ใช้เดือนแบบย่อหรือไม่ (true = ใช่)
     * @param bool   $includeTime แสดงเวลาด้วยหรือไม่ (true = ใช่)
     *
     * @return string วันที่รูปแบบไทย เช่น "4 สิงหาคม 2568" หรือ "4 ส.ค. 2568 เวลา 10:55"
     */
    if (!function_exists('formatToThaiDateSlash')) {
        /**
         * แปลงวันที่ 'YYYY/MM/DD' หรือ 'YYYY-MM-DD' เป็น 'DD/MM/YYYY' (พ.ศ.)
         *
         * @param string $dateString วันที่ในรูปแบบ ค.ศ.
         *
         * @return string วันที่รูปแบบไทย เช่น "01/02/2568"
         */
        function formatToThaiDateSlash(string $dateString): string
        {
            if (empty($dateString)) {
                return '';
            }
    
            // ใช้ Time Class ของ CI4 ซึ่งรองรับทั้ง YYYY/MM/DD และ YYYY-MM-DD
            $time = Time::parse($dateString);
    
            // 'd' = วันที่มีเลข 0 นำหน้า, 'm' = เดือนที่มีเลข 0 นำหน้า
            $day = $time->format('d');
            $month = $time->format('m');
            $thaiYear = $time->getYear() + 543;
    
            return "{$day}/{$month}/{$thaiYear}";
        }
    }
}