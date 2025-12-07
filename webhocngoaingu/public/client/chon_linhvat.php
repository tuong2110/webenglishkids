<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'Chọn Linh Vật | ' . $Database->site("TenWeb") . '';
$locationPage = 'chon_linhvat';
require_once(__DIR__ . "/../../public/client/header.php");

checkLogin();

// Kiểm tra xem người dùng đã chọn linh vật chưa
$taiKhoanEscaped = $Database->escape_string($_SESSION["account"]);
$checkLinhVat = $Database->get_row("SELECT * FROM nguoidung_linhvat WHERE TaiKhoan = '$taiKhoanEscaped'");

// Nếu đã chọn linh vật, redirect về trang chủ
if ($checkLinhVat) {
    header("Location: " . BASE_URL("Page/Home"));
    exit;
}

// Lấy danh sách linh vật
$listLinhVat = $Database->get_list("SELECT * FROM linhvat WHERE TrangThai = 1 ORDER BY MaLinhVat");

// Khởi tạo tim cho người dùng nếu chưa có
$taiKhoanEscaped = $Database->escape_string($_SESSION["account"]);
$checkTim = $Database->get_row("SELECT * FROM nguoidung_tim WHERE TaiKhoan = '$taiKhoanEscaped'");
if (!$checkTim) {
    $Database->query("INSERT INTO nguoidung_tim (TaiKhoan, SoTim) VALUES ('$taiKhoanEscaped', 5)");
}

// Khởi tạo điểm thưởng cho người dùng nếu chưa có
$checkDiem = $Database->get_row("SELECT * FROM nguoidung_diemthuong WHERE TaiKhoan = '$taiKhoanEscaped'");
if (!$checkDiem) {
    $Database->query("INSERT INTO nguoidung_diemthuong (TaiKhoan, SoDiem) VALUES ('$taiKhoanEscaped', 0)");
}

?>
<style>
    .chon-linhvat {
        max-width: 1200px;
        margin: 40px auto;
        padding: 20px;
    }
    
    .chon-linhvat__title {
        text-align: center;
        font-size: 32px;
        font-weight: bold;
        margin-bottom: 40px;
        color: #333;
    }
    
    .chon-linhvat__list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }
    
    .chon-linhvat__item {
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        cursor: pointer;
        border: 3px solid transparent;
    }
    
    .chon-linhvat__item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 12px rgba(0,0,0,0.15);
        border-color: #4CAF50;
    }
    
    .chon-linhvat__item.selected {
        border-color: #4CAF50;
        background: #f0f8f0;
    }
    
    .chon-linhvat__item-img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        margin-bottom: 20px;
        background: #f5f5f5;
    }
    
    .chon-linhvat__item-name {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
        color: #333;
    }
    
    .chon-linhvat__item-mota {
        font-size: 16px;
        color: #666;
        margin-bottom: 20px;
    }
    
    .chon-linhvat__btn {
        text-align: center;
        margin-top: 30px;
    }
    
    .btn-chon {
        background: #4CAF50;
        color: white;
        padding: 15px 40px;
        border: none;
        border-radius: 8px;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s;
    }
    
    .btn-chon:hover {
        background: #45a049;
    }
    
    .btn-chon:disabled {
        background: #ccc;
        cursor: not-allowed;
    }
</style>

<div class="grid">
    <div class="row main-page">
        <div class="nav-container">
            <?php include_once(__DIR__ . "/../../public/client/navigation.php"); ?>
        </div>

        <div class="main_content-container">
            <div class="chon-linhvat">
                <div class="chon-linhvat__title">Chọn Linh Vật Của Bạn</div>
                
                <div class="chon-linhvat__list" id="listLinhVat">
                    <?php foreach ($listLinhVat as $linhvat): ?>
                        <div class="chon-linhvat__item" data-ma="<?= $linhvat['MaLinhVat'] ?>">
                            <div style="position:relative; width:150px; height:150px; margin:0 auto 20px;">
                                <img src="<?= (strpos($linhvat['AnhDaiDien'], 'http') === 0) ? $linhvat['AnhDaiDien'] : BASE_URL(ltrim($linhvat['AnhDaiDien'], '/')) ?>" alt="<?= $linhvat['TenLinhVat'] ?>" class="chon-linhvat__item-img" style="width:150px; height:150px; object-fit:cover; border-radius:50%;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="chon-linhvat__item-placeholder" style="display:none; width:150px; height:150px; border-radius:50%; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); align-items:center; justify-content:center; color:white; font-size:48px;">🎮</div>
                            </div>
                            <div class="chon-linhvat__item-name"><?= htmlspecialchars($linhvat['TenLinhVat']) ?></div>
                            <div class="chon-linhvat__item-mota"><?= htmlspecialchars($linhvat['MoTa']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="chon-linhvat__btn">
                    <button class="btn-chon" id="btnChonLinhVat" disabled>Chọn Linh Vật</button>
                </div>
            </div>
        </div>

        <?php
        include_once(__DIR__ . "/../../public/client/navigation_mobile.php");
        ?>
    </div>
</div>

<script>
    let selectedLinhVat = null;
    
    // Xử lý click chọn linh vật
    document.querySelectorAll('.chon-linhvat__item').forEach(item => {
        item.addEventListener('click', function() {
            // Bỏ chọn tất cả
            document.querySelectorAll('.chon-linhvat__item').forEach(i => i.classList.remove('selected'));
            // Chọn item này
            this.classList.add('selected');
            selectedLinhVat = this.dataset.ma;
            // Enable button
            document.getElementById('btnChonLinhVat').disabled = false;
        });
    });
    
    // Xử lý submit chọn linh vật
    document.getElementById('btnChonLinhVat').addEventListener('click', function() {
        if (!selectedLinhVat) {
            toastr.error('Vui lòng chọn một linh vật', 'Lỗi!');
            return;
        }
        
        $.ajax({
            url: "<?= BASE_URL("assets/ajaxs/Game.php"); ?>",
            method: "POST",
            data: {
                type: 'ChonLinhVat',
                maLinhVat: selectedLinhVat
            },
            beforeSend: function() {
                $('#btnChonLinhVat').html('Đang xử lý...').prop('disabled', true);
            },
            success: function(response) {
                try {
                    console.log('Response:', response);
                    const json = typeof response === 'string' ? JSON.parse(response) : response;
                    console.log('Parsed JSON:', json);
                    
                    if (json.status === 'success') {
                        toastr.success(json.message, 'Thành công!');
                        setTimeout(function() {
                            window.location.href = "<?= BASE_URL("Page/Home") ?>";
                        }, 1000);
                    } else {
                        toastr.error(json.message || 'Có lỗi xảy ra', 'Lỗi!');
                        $('#btnChonLinhVat').html('Chọn Linh Vật').prop('disabled', false);
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                    console.error('Response:', response);
                    toastr.error('Lỗi xử lý dữ liệu: ' + e.message, 'Lỗi!');
                    $('#btnChonLinhVat').html('Chọn Linh Vật').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                toastr.error('Không thể kết nối đến server: ' + error, 'Lỗi!');
                $('#btnChonLinhVat').html('Chọn Linh Vật').prop('disabled', false);
            }
        });
    });
</script>

<?php require_once(__DIR__ . "/../../public/client/footer.php"); ?>

