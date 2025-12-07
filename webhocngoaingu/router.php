<?php
/**
 * Router for PHP built-in server
 * This file handles URL routing when using PHP built-in server
 */

$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Remove leading slash
$requestPath = ltrim($requestPath, '/');

// Remove query string from path
$requestPath = explode('?', $requestPath)[0];

// Route mapping based on .htaccess rules
$routes = [
    // Admin routes
    'admin/home' => 'public/admin/Home.php',
    'admin/users' => 'public/admin/Users.php',
    'admin/system' => 'public/admin/EditSystem.php',
    'admin/chatgpt' => 'public/admin/ChatGPT.php',
    'admin/courses' => 'public/admin/Courses.php',
    
    // Auth routes
    'Auth/DangNhap' => 'public/client/login.php',
    'Auth/DangKy' => 'public/client/signup.php',
    'Auth/QuenMatKhau' => 'public/client/quen_mat_khau.php',
    'Auth/DangXuat' => 'public/client/logout.php',
    'Auth/KichHoatEmail' => 'public/client/active_email.php',
    
    // Page routes
    'policy' => 'public/client/policy.php',
    'terms' => 'public/client/terms.php',
    'Page/KhoiTaoTaiKhoan' => 'public/client/setup_account.php',
    'Page/Home' => 'public/client/home_page.php',
    'Page/Study' => 'public/client/study.php',
    'Page/OnTap' => 'public/client/on_tap.php',
    'Page/OnSieuToc' => 'public/client/on_sieu_toc.php',
    'Page/OnTuKho' => 'public/client/on_tu_kho.php',
    'Page/KhoaHoc' => 'public/client/khoa_hoc.php',
    'Page/AboutUs' => 'public/client/about_us.php',
    'Page/BangXepHang' => 'public/client/bxh.php',
    'Page/ChatBot' => 'public/client/chat_bot.php',
    'Page/ChatBotStream' => 'public/chat-gpt/event-stream.php',
    'Page/ChonLinhVat' => 'public/client/chon_linhvat.php',
    'Page/LinhVat' => 'public/client/linhvat_page.php',
    'Page/Game' => 'public/client/game.php',
    'Page/ChoiGame' => 'public/client/choi_game.php',
    'Page/Shop' => 'public/client/shop.php',
    
    
    // Script xem và cập nhật
    'xem_tai_khoan_admin' => 'xem_tai_khoan_admin.php',
    'xem_tai_khoan_admin.php' => 'xem_tai_khoan_admin.php',
    'cap_nhat_thoi_gian_tu_vung_cung_gio' => 'cap_nhat_thoi_gian_tu_vung_cung_gio.php',
    'cap_nhat_thoi_gian_tu_vung_cung_gio.php' => 'cap_nhat_thoi_gian_tu_vung_cung_gio.php',
    
    // Script import từ vựng CSV
    'import_tuvung_csv' => 'import_tuvung_csv.php',
    'import_tuvung_csv.php' => 'import_tuvung_csv.php',
    
    // Script đọc file ETS
    'read_ets' => 'read_ets.php',
    'read_ets.php' => 'read_ets.php',
    
    // Script import ETS Excel
    'import_ets_excel' => 'import_ets_excel.php',
    'import_ets_excel.php' => 'import_ets_excel.php',
    
    // Script tạo 3 chặng
    'tao_3_chang' => 'tao_3_chang.php',
    'tao_3_chang.php' => 'tao_3_chang.php',
    
    // Script import SQL chặng
    'import_sql_chang' => 'import_sql_chang.php',
    'import_sql_chang.php' => 'import_sql_chang.php',
];

// Handle dynamic routes
if (preg_match('#^admin/users/edit/([A-Za-z0-9-]+)$#', $requestPath, $matches)) {
    $_GET['account'] = $matches[1];
    require __DIR__ . '/public/admin/EditUser.php';
    exit;
}

if (preg_match('#^admin/courses/edit/([A-Za-z0-9-]+)$#', $requestPath, $matches)) {
    $_GET['id'] = $matches[1];
    require __DIR__ . '/public/admin/EditCourse.php';
    exit;
}

if (preg_match('#^admin/courses/([A-Za-z0-9-]+)/lesson/edit/([A-Za-z0-9-]+)$#', $requestPath, $matches)) {
    $_GET['maKhoaHoc'] = $matches[1];
    $_GET['maBaiHoc'] = $matches[2];
    require __DIR__ . '/public/admin/EditLesson.php';
    exit;
}

if (preg_match('#^admin/courses/([A-Za-z0-9-]+)/lesson/([A-Za-z0-9-]+)/vocabulary/edit/([A-Za-z0-9-]+)$#', $requestPath, $matches)) {
    $_GET['maKhoaHoc'] = $matches[1];
    $_GET['maBaiHoc'] = $matches[2];
    $_GET['maTuVung'] = $matches[3];
    require __DIR__ . '/public/admin/EditVocabulary.php';
    exit;
}

    if (preg_match('#^Page/KhoaHoc/([A-Za-z0-9-]+)$#', $requestPath, $matches)) {
        $_GET['maKhoaHoc'] = $matches[1];
        require __DIR__ . '/public/client/khoa_hoc_chi_tiet.php';
        exit;
    }

    if (preg_match('#^Page/Chang/([A-Za-z0-9-]+)/([A-Za-z0-9-]+)$#', $requestPath, $matches)) {
        $_GET['maKhoaHoc'] = $matches[1];
        $_GET['maChang'] = $matches[2];
        require __DIR__ . '/public/client/chang_chi_tiet.php';
        exit;
    }

if (preg_match('#^Page/BaiHoc/([A-Za-z0-9-]+)/([A-Za-z0-9-]+)$#', $requestPath, $matches)) {
    $_GET['maKhoaHoc'] = $matches[1];
    $_GET['maBaiHoc'] = $matches[2];
    require __DIR__ . '/public/client/bai_hoc_khoa_hoc_chi_tiet.php';
    exit;
}

if (preg_match('#^Page/TrangCaNhan/(.*)/(.*)$#', $requestPath, $matches)) {
    $_GET['taiKhoan'] = $matches[1];
    $_GET['type'] = $matches[2];
    require __DIR__ . '/public/client/profile_page.php';
    exit;
}

if (preg_match('#^Page/CaiDat/(.*)$#', $requestPath, $matches)) {
    $_GET['type'] = $matches[1];
    require __DIR__ . '/public/client/setting.php';
    exit;
}

// Handle static routes
if (isset($routes[$requestPath])) {
    $file = __DIR__ . '/' . $routes[$requestPath];
    if (file_exists($file)) {
        require $file;
        exit;
    }
}

// Handle root path
if ($requestPath === '' || $requestPath === 'index.php') {
    require __DIR__ . '/index.php';
    exit;
}

// Handle static files (CSS, JS, images, etc.)
if (file_exists(__DIR__ . '/' . $requestPath)) {
    return false; // Let PHP built-in server handle it
}

// 404 - File not found
http_response_code(404);
echo "404 - Page not found: " . htmlspecialchars($requestPath);
exit;

