<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'Game | ' . $Database->site("TenWeb") . '';
$locationPage = 'game';
require_once(__DIR__ . "/../../public/client/header.php");

checkLogin();

// Kiểm tra xem người dùng đã chọn linh vật chưa
$taiKhoanEscaped = $Database->escape_string($_SESSION["account"]);
$checkLinhVat = $Database->get_row("SELECT * FROM nguoidung_linhvat WHERE TaiKhoan = '$taiKhoanEscaped'");
if (!$checkLinhVat) {
    header("Location: " . BASE_URL("Page/ChonLinhVat"));
    exit;
}

// Lấy thông tin tim
$userTim = $Database->get_row("SELECT * FROM nguoidung_tim WHERE TaiKhoan = '$taiKhoanEscaped'");
if (!$userTim) {
    $Database->query("INSERT INTO nguoidung_tim (TaiKhoan, SoTim) VALUES ('$taiKhoanEscaped', 5)");
    $userTim = $Database->get_row("SELECT * FROM nguoidung_tim WHERE TaiKhoan = '$taiKhoanEscaped'");
}

// Lấy danh sách game
$listGame = $Database->get_list("SELECT * FROM game WHERE TrangThai = 1 ORDER BY MaGame");

?>
<style>
    .game {
        max-width: 1200px;
        margin: 40px auto;
        padding: 20px;
    }
    
    .game__header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 40px;
        color: white;
        display: flex;
        justify-content: space-around;
        align-items: center;
    }
    
    .game__header-item {
        text-align: center;
    }
    
    .game__header-label {
        font-size: 16px;
        opacity: 0.9;
        margin-bottom: 10px;
    }
    
    .game__header-value {
        font-size: 32px;
        font-weight: bold;
    }
    
    .game__title {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 30px;
        color: #333;
    }
    
    .game__list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
    }
    
    .game__item {
        background: #fff;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .game__item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 12px rgba(0,0,0,0.15);
    }
    
    .game__item-img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 12px;
        background: #f5f5f5;
    }
    
    .game__item-name {
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 10px;
        color: #333;
    }
    
    .game__item-mota {
        font-size: 14px;
        color: #666;
        margin-bottom: 15px;
        min-height: 40px;
    }
    
    .game__item-info {
        display: flex;
        justify-content: space-around;
        margin-bottom: 20px;
        padding: 15px;
        background: #f5f5f5;
        border-radius: 8px;
    }
    
    .game__item-info-item {
        text-align: center;
    }
    
    .game__item-info-label {
        font-size: 12px;
        color: #666;
        margin-bottom: 5px;
    }
    
    .game__item-info-value {
        font-size: 18px;
        font-weight: bold;
        color: #333;
    }
    
    .game__item-btn {
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
    
    .game__item-btn:hover {
        background: #45a049;
    }
    
    .game__item-btn:disabled {
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
            <div class="game">
                <div class="game__header">
                    <div class="game__header-item">
                        <div class="game__header-label">Tim của bạn</div>
                        <div class="game__header-value" id="soTim"><?= $userTim['SoTim'] ?? 0 ?></div>
                    </div>
                </div>
                
                <div class="game__title">Chọn Game</div>
                
                <div class="game__list">
                    <?php foreach ($listGame as $game): ?>
                        <div class="game__item">
                            <div style="position:relative; width:150px; height:150px; margin:0 auto 15px;">
                                <img src="<?= (strpos($game['AnhDaiDien'], 'http') === 0) ? $game['AnhDaiDien'] : BASE_URL(ltrim($game['AnhDaiDien'], '/')) ?>" alt="<?= $game['TenGame'] ?>" class="game__item-img" style="width:150px; height:150px; object-fit:cover; border-radius:12px;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="game__item-placeholder" style="display:none; width:150px; height:150px; border-radius:12px; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); align-items:center; justify-content:center; color:white; font-size:48px;">🎯</div>
                            </div>
                            <div class="game__item-name"><?= htmlspecialchars($game['TenGame']) ?></div>
                            <div class="game__item-mota"><?= htmlspecialchars($game['MoTa']) ?></div>
                            <div class="game__item-info">
                                <div class="game__item-info-item">
                                    <div class="game__item-info-label">Tim cần</div>
                                    <div class="game__item-info-value"><?= $game['SoTimCanThiet'] ?></div>
                                </div>
                                <div class="game__item-info-item">
                                    <div class="game__item-info-label">Điểm thắng</div>
                                    <div class="game__item-info-value">+<?= $game['DiemThang'] ?></div>
                                </div>
                            </div>
                            <button class="game__item-btn" 
                                    data-ma="<?= $game['MaGame'] ?>" 
                                    data-tim="<?= $game['SoTimCanThiet'] ?>"
                                    <?= ($userTim['SoTim'] ?? 0) < $game['SoTimCanThiet'] ? 'disabled' : '' ?>>
                                Chơi Ngay
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
    // Xử lý chơi game
    document.querySelectorAll('.game__item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const maGame = this.dataset.ma;
            const soTim = parseInt(this.dataset.tim);
            
            if (this.disabled) {
                toastr.warning('Bạn không đủ tim để chơi game này. Hãy mua thêm tim tại Shop!', 'Cảnh báo!');
                return;
            }
            
            // Mở modal chơi game (sẽ được implement sau)
            window.location.href = "<?= BASE_URL("Page/ChoiGame") ?>?maGame=" + maGame;
        });
    });
</script>

<?php require_once(__DIR__ . "/../../public/client/footer.php"); ?>

