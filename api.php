<?php
// กำหนด Header ให้รองรับการทำงานแบบ API (JSON)
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// ตอบกลับ CORS preflight (OPTIONS) ทันที ไม่ต้องประมวลผลต่อ
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$file = __DIR__ . '/registrations.json'; // ใช้ path สัมบูรณ์กันปัญหา working directory

// ฟังก์ชันช่วยอ่านข้อมูลเดิมแบบปลอดภัย (กันไฟล์ JSON เสีย)
function read_registrations($file) {
    if (!file_exists($file)) return [];
    $raw = file_get_contents($file);
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

switch ($action) {
    case 'register':
        // รับข้อมูลจากหน้าเว็บที่ส่งมาเป็น JSON
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (is_array($data)) {
            $current_data = read_registrations($file);

            // เพิ่ม timestamp ให้ข้อมูล
            $data['timestamp'] = date('Y-m-d H:i:s');
            $current_data[] = $data;

            // เขียนแบบ LOCK_EX กันการเขียนชนกันเมื่อมีผู้ลงทะเบียนพร้อมกัน
            $written = file_put_contents(
                $file,
                json_encode($current_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                LOCK_EX
            );

            if ($written !== false) {
                echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลเรียบร้อย']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถบันทึกไฟล์ได้ (ตรวจสอบสิทธิ์การเขียนไฟล์)']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ถูกต้อง']);
        }
        break;

    case 'getList':
        // ส่งข้อมูลรายชื่อผู้ลงทะเบียนกลับไปแสดงผล (การันตีว่าเป็น array เสมอ)
        echo json_encode(read_registrations($file), JSON_UNESCAPED_UNICODE);
        break;

    case 'getContent':
        // ส่งข้อมูล URL ของสื่อ PR และ เอกสารต่างๆ กลับไปแสดงผล
        echo json_encode([
            'prImageUrl' => 'https://placehold.co/800x600/6366f1/FFF?text=PR+Banner',
            'docFileUrl' => '#',
            'docFileName' => 'โครงการวิชาการ_อัปเดต.pdf',
            'locationMapUrl' => 'https://placehold.co/800x500/1e293b/FFF?text=Map+Location'
        ], JSON_UNESCAPED_UNICODE);
        break;

    default:
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบ API ปลายทาง']);
        break;
}
