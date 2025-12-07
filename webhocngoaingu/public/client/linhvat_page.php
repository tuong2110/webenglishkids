<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'Linh Vật | ' . $Database->site("TenWeb") . '';
$locationPage = 'linhvat_page';
require_once(__DIR__ . "/../../public/client/header.php");

checkLogin();

// Kiểm tra xem người dùng đã chọn linh vật chưa
$taiKhoanEscaped = $Database->escape_string($_SESSION["account"]);
$userLinhVat = $Database->get_row("SELECT nguoidung_linhvat.*, linhvat.AnhDaiDien, linhvat.MoTa 
    FROM nguoidung_linhvat 
    INNER JOIN linhvat ON nguoidung_linhvat.MaLinhVat = linhvat.MaLinhVat 
    WHERE nguoidung_linhvat.TaiKhoan = '$taiKhoanEscaped'");

if (!$userLinhVat) {
    header("Location: " . BASE_URL("Page/ChonLinhVat"));
    exit;
}

// Lấy vật phẩm của người dùng
$userVatPham = $Database->get_list("SELECT nguoidung_vatpham.*, shop_vatpham.TenVatPham, shop_vatpham.LoaiVatPham, shop_vatpham.AnhDaiDien
    FROM nguoidung_vatpham 
    INNER JOIN shop_vatpham ON nguoidung_vatpham.MaVatPham = shop_vatpham.MaVatPham
    WHERE nguoidung_vatpham.TaiKhoan = '$taiKhoanEscaped' AND nguoidung_vatpham.SoLuong > 0
    ORDER BY shop_vatpham.LoaiVatPham, shop_vatpham.MaVatPham");

// Xác định ảnh linh vật theo cấp độ
function getLinhVatImage($maLinhVat, $capDo) {
    $linhVatNames = [
        1 => 'sutu',
        2 => 'bachtuoc', 
        3 => 'khi'
    ];
    $name = $linhVatNames[$maLinhVat] ?? 'sutu';
    return '/assets/img/anhlinhvat/' . $name . '-cap' . $capDo . '.png';
}

$currentImage = getLinhVatImage($userLinhVat['MaLinhVat'], $userLinhVat['CapDo']);

// Tính XP cần để lên cấp
$xpPerLevel = [0, 100, 300, 600]; // XP cần cho cấp 1->2, 2->3, 3->4
$currentLevelXP = $xpPerLevel[$userLinhVat['CapDo'] - 1] ?? 0;
$nextLevelXP = $xpPerLevel[$userLinhVat['CapDo']] ?? 999999;
$xpNeeded = $nextLevelXP - $userLinhVat['KinhNghiem'];
$xpProgress = $userLinhVat['CapDo'] >= 3 ? 100 : (($userLinhVat['KinhNghiem'] - $currentLevelXP) / ($nextLevelXP - $currentLevelXP) * 100);

?>
<style>
    .linhvat-page {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .linhvat-page__header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .linhvat-page__title {
        font-size: 32px;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
    }
    
    .linhvat-page__main {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .linhvat-display-card {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        text-align: center;
    }
    
    .linhvat-display-card__image {
        width: 200px;
        height: 200px;
        margin: 0 auto 20px;
        border-radius: 50%;
        border: 4px solid #4CAF50;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    .linhvat-display-card__image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .linhvat-display-card__name {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
    }
    
    .linhvat-display-card__level {
        font-size: 18px;
        color: #666;
        margin-bottom: 20px;
    }
    
    .linhvat-display-card__xp {
        margin-top: 20px;
    }
    
    .linhvat-display-card__xp-label {
        font-size: 14px;
        color: #666;
        margin-bottom: 8px;
    }
    
    .linhvat-display-card__xp-bar {
        width: 100%;
        height: 20px;
        background: #e0e0e0;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 8px;
    }
    
    .linhvat-display-card__xp-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #4CAF50, #45a049);
        transition: width 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
        font-weight: bold;
    }
    
    .linhvat-display-card__xp-text {
        font-size: 12px;
        color: #999;
    }
    
    .linhvat-vatpham {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .linhvat-vatpham__title {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
    }
    
    .linhvat-vatpham__list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 15px;
    }
    
    .linhvat-vatpham__item {
        background: #f5f5f5;
        border-radius: 12px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .linhvat-vatpham__item:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .linhvat-vatpham__item-image {
        width: 80px;
        height: 80px;
        margin: 0 auto 10px;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .linhvat-vatpham__item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .linhvat-vatpham__item-name {
        font-size: 12px;
        color: #333;
        margin-bottom: 5px;
    }
    
    .linhvat-vatpham__item-quantity {
        font-size: 14px;
        font-weight: bold;
        color: #4CAF50;
    }
    
    .linhvat-vatpham__empty {
        text-align: center;
        padding: 40px;
        color: #999;
    }
    
    @media (max-width: 768px) {
        .linhvat-page__main {
            grid-template-columns: 1fr;
        }
        
        .linhvat-display-card__image {
            width: 150px;
            height: 150px;
        }
    }
</style>

<div class="grid">
    <div class="row main-page">
        <div class="nav-container">
            <?php include_once(__DIR__ . "/../../public/client/navigation.php"); ?>
        </div>

        <div class="main_content-container">
            <div class="linhvat-page">
                <div class="linhvat-page__header">
                    <div class="linhvat-page__title">Linh Vật Của Bạn</div>
                </div>
                
                <div class="linhvat-page__main">
                    <div class="linhvat-display-card">
                        <div class="linhvat-display-card__image">
                            <img src="<?= BASE_URL(ltrim($currentImage, '/')) ?>" 
                                 alt="<?= htmlspecialchars($userLinhVat['TenLinhVat']) ?>"
                                 onerror="this.onerror=null; this.src='<?= BASE_URL('/assets/img/anhlinhvat/4.png') ?>';">
                        </div>
                        <div class="linhvat-display-card__name"><?= htmlspecialchars($userLinhVat['TenLinhVat']) ?></div>
                        <div class="linhvat-display-card__level">Cấp <?= $userLinhVat['CapDo'] ?><?= $userLinhVat['CapDo'] >= 3 ? ' (Tối đa)' : '' ?></div>
                        <div class="linhvat-display-card__xp">
                            <div class="linhvat-display-card__xp-label">Kinh nghiệm</div>
                            <div class="linhvat-display-card__xp-bar">
                                <div class="linhvat-display-card__xp-bar-fill" style="width: <?= min(100, max(0, $xpProgress)) ?>%;">
                                    <?= $xpProgress > 10 ? round($xpProgress) . '%' : '' ?>
                                </div>
                            </div>
                            <div class="linhvat-display-card__xp-text">
                                <?= number_format($userLinhVat['KinhNghiem']) ?> / <?= number_format($nextLevelXP) ?> XP
                                <?= $userLinhVat['CapDo'] < 3 ? '(Cần thêm ' . number_format($xpNeeded) . ' XP để lên cấp)' : '' ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="linhvat-vatpham">
                        <div class="linhvat-vatpham__title">Vật Phẩm Của Bạn</div>
                        <?php if (empty($userVatPham)): ?>
                            <div class="linhvat-vatpham__empty">
                                Bạn chưa có vật phẩm nào. Hãy mua tại Shop!
                            </div>
                        <?php else: ?>
                            <div class="linhvat-vatpham__list">
                                <?php foreach ($userVatPham as $vatpham): ?>
                                    <div class="linhvat-vatpham__item" 
                                         data-ma="<?= $vatpham['MaVatPham'] ?>"
                                         data-loai="<?= $vatpham['LoaiVatPham'] ?>"
                                         onclick="useVatPham(<?= $vatpham['MaVatPham'] ?>, '<?= $vatpham['LoaiVatPham'] ?>')">
                                        <div class="linhvat-vatpham__item-image">
                                            <img src="<?= (strpos($vatpham['AnhDaiDien'], 'http') === 0) ? $vatpham['AnhDaiDien'] : BASE_URL(ltrim($vatpham['AnhDaiDien'], '/')) ?>" 
                                                 alt="<?= htmlspecialchars($vatpham['TenVatPham']) ?>"
                                                 onerror="this.onerror=null; this.style.display='none';">
                                        </div>
                                        <div class="linhvat-vatpham__item-name"><?= htmlspecialchars($vatpham['TenVatPham']) ?></div>
                                        <div class="linhvat-vatpham__item-quantity">x<?= $vatpham['SoLuong'] ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php include_once(__DIR__ . "/../../public/client/navigation_mobile.php"); ?>
    </div>
</div>

<script>
    function useVatPham(maVatPham, loaiVatPham) {
        if (loaiVatPham !== 'vatpham_linhvat') {
            toastr.warning('Vật phẩm này không thể sử dụng cho linh vật', 'Thông báo');
            return;
        }
        
        $.ajax({
            url: "<?= BASE_URL("assets/ajaxs/Game.php"); ?>",
            method: "POST",
            data: {
                type: 'SuDungVatPham',
                maVatPham: maVatPham
            },
            success: function(response) {
                try {
                    const json = typeof response === 'string' ? JSON.parse(response) : response;
                    if (json.status === 'success') {
                        toastr.success(json.message, 'Thành công!');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(json.message, 'Lỗi!');
                    }
                } catch (e) {
                    console.error('Parse error:', e, response);
                    toastr.error('Lỗi xử lý dữ liệu', 'Lỗi!');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                toastr.error('Không thể kết nối đến server', 'Lỗi!');
            }
        });
    }
</script>

<?php require_once(__DIR__ . "/../../public/client/footer.php"); ?>

