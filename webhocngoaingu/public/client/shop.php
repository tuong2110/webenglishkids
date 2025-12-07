<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'Shop | ' . $Database->site("TenWeb") . '';
$locationPage = 'shop';
require_once(__DIR__ . "/../../public/client/header.php");

checkLogin();

// Lấy thông tin tim và điểm thưởng
$taiKhoanEscaped = $Database->escape_string($_SESSION["account"]);
$userTim = $Database->get_row("SELECT * FROM nguoidung_tim WHERE TaiKhoan = '$taiKhoanEscaped'");
$userDiem = $Database->get_row("SELECT * FROM nguoidung_diemthuong WHERE TaiKhoan = '$taiKhoanEscaped'");

if (!$userTim) {
    $Database->query("INSERT INTO nguoidung_tim (TaiKhoan, SoTim) VALUES ('$taiKhoanEscaped', 5)");
    $userTim = $Database->get_row("SELECT * FROM nguoidung_tim WHERE TaiKhoan = '$taiKhoanEscaped'");
}

if (!$userDiem) {
    $Database->query("INSERT INTO nguoidung_diemthuong (TaiKhoan, SoDiem) VALUES ('$taiKhoanEscaped', 0)");
    $userDiem = $Database->get_row("SELECT * FROM nguoidung_diemthuong WHERE TaiKhoan = '$taiKhoanEscaped'");
}

// Lấy danh sách vật phẩm
$listVatPham = $Database->get_list("SELECT * FROM shop_vatpham WHERE TrangThai = 1 ORDER BY LoaiVatPham, GiaDiem");

?>
<style>
    .shop {
        max-width: 1200px;
        margin: 40px auto;
        padding: 20px;
    }
    
    .shop__header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 40px;
        color: white;
        display: flex;
        justify-content: space-around;
        align-items: center;
    }
    
    .shop__header-item {
        text-align: center;
    }
    
    .shop__header-label {
        font-size: 16px;
        opacity: 0.9;
        margin-bottom: 10px;
    }
    
    .shop__header-value {
        font-size: 32px;
        font-weight: bold;
    }
    
    .shop__title {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 30px;
        color: #333;
    }
    
    .shop__list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 25px;
    }
    
    .shop__item {
        background: #fff;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .shop__item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 12px rgba(0,0,0,0.15);
    }
    
    .shop__item-img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 12px;
        background: #f5f5f5;
    }
    
    .shop__item-name {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 10px;
        color: #333;
    }
    
    .shop__item-mota {
        font-size: 14px;
        color: #666;
        margin-bottom: 15px;
        min-height: 40px;
    }
    
    .shop__item-gia {
        font-size: 18px;
        font-weight: bold;
        color: #4CAF50;
        margin-bottom: 15px;
    }
    
    .shop__item-btn {
        background: #4CAF50;
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s;
        width: 100%;
    }
    
    .shop__item-btn:hover {
        background: #45a049;
    }
    
    .shop__item-btn:disabled {
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
            <div class="shop">
                <div class="shop__header">
                    <div class="shop__header-item">
                        <div class="shop__header-label">Tim của bạn</div>
                        <div class="shop__header-value" id="soTim"><?= $userTim['SoTim'] ?? 0 ?></div>
                    </div>
                    <div class="shop__header-item">
                        <div class="shop__header-label">Điểm thưởng</div>
                        <div class="shop__header-value" id="soDiem"><?= $userDiem['SoDiem'] ?? 0 ?></div>
                    </div>
                </div>
                
                <div class="shop__title">Cửa Hàng</div>
                
                <div class="shop__list">
                    <?php foreach ($listVatPham as $vatpham): ?>
                        <div class="shop__item">
                            <div style="position:relative; width:120px; height:120px; margin:0 auto 15px;">
                                <?php if (strpos($vatpham['AnhDaiDien'], 'emoji:') === 0): ?>
                                    <?php 
                                    $emoji = str_replace('emoji:', '', $vatpham['AnhDaiDien']);
                                    ?>
                                    <div style="width:120px; height:120px; border-radius:12px; background:linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); display:flex; align-items:center; justify-content:center; font-size:48px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                        <?= $emoji ?>
                                    </div>
                                <?php else: ?>
                                    <img src="<?= (strpos($vatpham['AnhDaiDien'], 'http') === 0) ? $vatpham['AnhDaiDien'] : BASE_URL(ltrim($vatpham['AnhDaiDien'], '/')) ?>" alt="<?= $vatpham['TenVatPham'] ?>" class="shop__item-img" style="width:120px; height:120px; object-fit:cover; border-radius:12px;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="shop__item-placeholder" style="display:none; width:120px; height:120px; border-radius:12px; background:linear-gradient(135deg, #f093fb 0%, #f5576c 100%); align-items:center; justify-content:center; color:white; font-size:36px;">🛒</div>
                                <?php endif; ?>
                            </div>
                            <div class="shop__item-name"><?= htmlspecialchars($vatpham['TenVatPham']) ?></div>
                            <div class="shop__item-mota"><?= htmlspecialchars($vatpham['MoTa']) ?></div>
                            <div class="shop__item-gia"><?= number_format($vatpham['GiaDiem']) ?> điểm</div>
                            <button class="shop__item-btn" 
                                    data-ma="<?= $vatpham['MaVatPham'] ?>" 
                                    data-gia="<?= $vatpham['GiaDiem'] ?>"
                                    <?= ($userDiem['SoDiem'] ?? 0) < $vatpham['GiaDiem'] ? 'disabled' : '' ?>>
                                Mua Ngay
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php
        include_once(__DIR__ . "/../../public/client/navigation_mobile.php");
        ?>
    </div>
</div>

<script>
    // Xử lý mua vật phẩm
    document.querySelectorAll('.shop__item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const maVatPham = this.dataset.ma;
            const gia = parseInt(this.dataset.gia);
            
            if (this.disabled) {
                toastr.warning('Bạn không đủ điểm để mua vật phẩm này', 'Cảnh báo!');
                return;
            }
            
            if (!confirm('Bạn có chắc muốn mua vật phẩm này?')) {
                return;
            }
            
            $.ajax({
                url: "<?= BASE_URL("assets/ajaxs/Game.php"); ?>",
                method: "POST",
                data: {
                    type: 'MuaVatPham',
                    maVatPham: maVatPham
                },
                beforeSend: function() {
                    btn.html('Đang xử lý...').prop('disabled', true);
                },
                success: function(response) {
                    try {
                        const json = JSON.parse(response);
                        if (json.status === 'success') {
                            toastr.success(json.message, 'Thành công!');
                            // Cập nhật số điểm và tim
                            $('#soDiem').text(json.data.soDiem);
                            $('#soTim').text(json.data.soTim);
                            // Reload để cập nhật button states
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            toastr.error(json.message, 'Lỗi!');
                            btn.html('Mua Ngay').prop('disabled', false);
                        }
                    } catch (e) {
                        toastr.error('Có lỗi xảy ra', 'Lỗi!');
                        btn.html('Mua Ngay').prop('disabled', false);
                    }
                },
                error: function() {
                    toastr.error('Có lỗi xảy ra', 'Lỗi!');
                    btn.html('Mua Ngay').prop('disabled', false);
                }
            });
        });
    });
</script>

<?php require_once(__DIR__ . "/../../public/client/footer.php"); ?>

