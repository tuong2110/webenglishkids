<?php
// Disable all output buffering and set headers immediately
while (ob_get_level()) {
    ob_end_clean();
}

// Set headers FIRST before any output
header('Content-type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no'); // Disable buffering for nginx

// Start output buffering to catch any accidental output
ob_start();

// Suppress any warnings/notices that might cause output
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// Error handler để catch fatal errors
function handleError($errno, $errstr, $errfile, $errline) {
    ob_clean();
    echo "data: " . json_encode(['error' => 'Lỗi hệ thống: ' . $errstr]) . "\n\n";
    echo "data: [DONE]\n\n";
    ob_flush();
    flush();
    exit;
}
set_error_handler('handleError');

try {
    // Config.php sẽ tự động start session, không cần gọi lại
    require_once(__DIR__ . "/../../configs/config.php");
    require_once(__DIR__ . "/../../configs/function.php");
    
    if (!isset($_SESSION["account"])) {
        ob_clean();
        echo "data: " . json_encode(['error' => 'Vui lòng đăng nhập']) . "\n\n";
        echo "data: [DONE]\n\n";
        ob_flush();
        flush();
        exit;
    }
    
    checkLogin();
    
    // Clear any output that might have been generated
    ob_clean();
} catch (Exception $e) {
    ob_clean();
    echo "data: " . json_encode(['error' => 'Lỗi: ' . $e->getMessage()]) . "\n\n";
    echo "data: [DONE]\n\n";
    ob_flush();
    flush();
    exit;
}

$chat_room_id = isset($_GET['chat_room_id']) ? $_GET['chat_room_id'] : '';

if (empty($chat_room_id)) {
    echo "data: " . json_encode(['error' => 'Thiếu chat_room_id']) . "\n\n";
    echo "data: [DONE]\n\n";
    ob_flush();
    flush();
    exit;
}

// Check user và room
try {
    $checkChatBotRoom = $Database->get_row("select * from chatbot_room where TaiKhoan = '" . $Database->escape_string($_SESSION["account"]) . "' and MaRoom = '" . $Database->escape_string($chat_room_id) . "' ");
    if ($checkChatBotRoom <= 0) {
        echo "data: " . json_encode(['error' => 'Vui lòng tạo room']) . "\n\n";
        echo "data: [DONE]\n\n";
        ob_flush();
        flush();
        exit;
    }
} catch (Exception $e) {
    echo "data: " . json_encode(['error' => 'Lỗi kiểm tra room: ' . $e->getMessage()]) . "\n\n";
    echo "data: [DONE]\n\n";
    ob_flush();
    flush();
    exit;
}

// Lấy lịch sử chat
try {
    $results = $Database->get_list("SELECT * FROM message_chatbot_room where MaRoom = '" . $Database->escape_string($chat_room_id) . "' ORDER BY ThoiGian ASC");
    $history = array();
    foreach ($results as $item) {
        $history[] = ['role' => $item["Role"], 'content' => $item['NoiDung']];
    }

    // Lấy tin nhắn cuối cùng của user
    $latest_user_message = "";
    $results = $Database->get_row("SELECT * FROM message_chatbot_room where MaRoom = '" . $Database->escape_string($chat_room_id) . "' and Role = 'user' ORDER BY ThoiGian desc limit 1");
    if ($results > 0) {
        $latest_user_message = $results['NoiDung'];
    }
} catch (Exception $e) {
    echo "data: " . json_encode(['error' => 'Lỗi lấy lịch sử: ' . $e->getMessage()]) . "\n\n";
    echo "data: [DONE]\n\n";
    ob_flush();
    flush();
    exit;
}

// Kiểm tra OpenAI API key
$openai_api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';

if (empty($openai_api_key)) {
    echo "data: " . json_encode(['error' => 'OPENAI_API_KEY chưa được cấu hình trong database. Vui lòng thêm API key vào bảng hethong (TenThamSo=OPENAI_API_KEY).']) . "\n\n";
    echo "data: [DONE]\n\n";
    ob_flush();
    flush();
    exit;
}

// Chuẩn bị messages cho OpenAI
$openai_messages = [];
// Lấy tối đa 20 tin nhắn gần nhất để tránh quá dài
$recent_history = array_slice($history, -20);
foreach ($recent_history as $msg) {
    $openai_messages[] = [
        'role' => $msg['role'] == 'user' ? 'user' : 'assistant',
        'content' => $msg['content']
    ];
}
// Thêm tin nhắn hiện tại
if (!empty($latest_user_message)) {
    $openai_messages[] = [
        'role' => 'user',
        'content' => $latest_user_message
    ];
}

// Nếu không có tin nhắn nào, thêm tin nhắn mặc định
if (empty($openai_messages)) {
    $openai_messages[] = [
        'role' => 'user',
        'content' => 'Xin chào!'
    ];
}

// OpenAI API endpoint với streaming
$openai_url = "https://api.openai.com/v1/chat/completions";

// Chuẩn bị request cho OpenAI
$openai_data = json_encode([
    'model' => 'gpt-3.5-turbo',
    'messages' => $openai_messages,
    'temperature' => 0.7,
    'max_tokens' => 1000,
    'stream' => true
]);

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $openai_api_key
];

// Gọi OpenAI API với streaming
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $openai_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $openai_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_BUFFERSIZE, 1); // Buffer nhỏ để stream nhanh hơn

$buffer = '';
$error_response = '';
$is_error = false;

curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) use (&$buffer, &$error_response, &$is_error) {
    // Kiểm tra xem có phải error response không (không phải streaming)
    if (strpos($data, 'data: ') === false && strpos($data, '{') !== false) {
        $is_error = true;
        $error_response .= $data;
        return strlen($data);
    }
    
    // Xử lý streaming response
    $buffer .= $data;
    $lines = explode("\n", $buffer);
    // Giữ lại dòng cuối chưa hoàn chỉnh
    $buffer = array_pop($lines);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }
        if ($line === 'data: [DONE]') {
            return strlen($data);
        }
        if (strpos($line, 'data: ') === 0) {
            $json = substr($line, 6);
            if (empty($json)) {
                continue;
            }
            $decoded = json_decode($json, true);
            if ($decoded && isset($decoded['choices'][0]['delta']['content'])) {
                $content = $decoded['choices'][0]['delta']['content'];
                echo "data: " . json_encode([
                    'choices' => [['delta' => ['content' => $content]]]
                ]) . "\n\n";
                ob_flush();
                flush();
            }
        }
    }
    return strlen($data);
});

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Nếu có error response, dùng nó
if ($is_error && !empty($error_response)) {
    $response = $error_response;
}

// Xử lý lỗi
if ($curl_error) {
    echo "data: " . json_encode(['error' => 'Lỗi kết nối: ' . $curl_error]) . "\n\n";
    echo "data: [DONE]\n\n";
    ob_flush();
    flush();
    exit;
}

if ($http_code != 200) {
    $error_data = json_decode($response, true);
    $error_msg = 'Lỗi từ OpenAI API';
    
    if ($http_code == 401) {
        $error_msg = 'Lỗi xác thực API key. Vui lòng kiểm tra OPENAI_API_KEY trong database. API key phải bắt đầu bằng "sk-".';
    } else if ($http_code == 429) {
        $error_detail = '';
        if (isset($error_data['error']['message'])) {
            $error_detail = ': ' . $error_data['error']['message'];
        }
        $error_msg = 'Quá nhiều requests' . $error_detail . '. Có thể do: 1) Đã hết credit trong tài khoản OpenAI, 2) Vượt quá rate limit. Vui lòng kiểm tra tại https://platform.openai.com/usage hoặc thử lại sau vài phút.';
    } else if ($http_code == 500) {
        $error_msg = 'Lỗi server OpenAI. Vui lòng thử lại sau.';
    } else if ($http_code == 402 || $http_code == 403) {
        $error_msg = 'Tài khoản OpenAI chưa có credit hoặc đã hết credit. Vui lòng nạp tiền tại https://platform.openai.com/account/billing';
    } else {
        if (isset($error_data['error']['message'])) {
            $error_msg = $error_data['error']['message'];
        } else {
            $error_msg = 'HTTP ' . $http_code . ': Lỗi không xác định từ OpenAI API';
        }
    }
    echo "data: " . json_encode(['error' => $error_msg]) . "\n\n";
    echo "data: [DONE]\n\n";
    ob_flush();
    flush();
    exit;
}

echo "data: [DONE]\n\n";
ob_flush();
flush();
