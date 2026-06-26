<?php
// กำหนด Header ให้รองรับการทำงานแบบ API (JSON)
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'register':
        // รับข้อมูลจากหน้าเว็บที่ส่งมาเป็น JSON
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if($data) {
            // บันทึกข้อมูลลงไฟล์ registrations.json (เสมือนเป็นฐานข้อมูล)
            $file = 'registrations.json';
            $current_data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
            
            // เพิ่ม timestamp ให้ข้อมูล
            $data['timestamp'] = date('Y-m-d H:i:s');
            $current_data[] = $data;
            
            if(file_put_contents($file, json_encode($current_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
                echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลเรียบร้อย']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถบันทึกไฟล์ได้']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ถูกต้อง']);
        }
        break;

    case 'getList':
        // ส่งข้อมูลรายชื่อผู้ลงทะเบียนกลับไปแสดงผล
        $file = 'registrations.json';
        if (file_exists($file)) {
            echo file_get_contents($file);
        } else {
            echo json_encode([]);
        }
        break;

    case 'getContent':
        // ส่งข้อมูล URL ของสื่อ PR และ เอกสารต่างๆ กลับไปแสดงผล
        echo json_encode([
            'prImageUrl' => 'https://placehold.co/800x600/6366f1/FFF?text=PR+Banner',
            'docFileUrl' => '#',
            'docFileName' => 'โครงการวิชาการ_อัปเดต.pdf',
            'locationMapUrl' => 'https://placehold.co/800x500/1e293b/FFF?text=Map+Location'
        ]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบ API ปลายทาง']);
        break;
}
?>