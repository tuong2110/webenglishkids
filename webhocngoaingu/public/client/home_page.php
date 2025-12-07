<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'Trang chủ học tập | ' . $Database->site('TenWeb') . '';
$locationPage = 'home_page';
$META_TITLE = "5Fs Group - Trang chủ học tập";
$META_IMAGE = "https://i.imgur.com/TxJhptu.png";
$META_DESCRIPTION = "5Fs Group - Trang chủ học tập";
$META_SITE = BASE_URL("Page/Home");
require_once(__DIR__ . "/../../public/client/header.php");
checkLogin();

// Lấy thông tin linh vật của người dùng
$taiKhoanEscaped = $Database->escape_string($_SESSION["account"]);
$userLinhVat = $Database->get_row("SELECT nguoidung_linhvat.*, linhvat.AnhDaiDien, linhvat.MoTa 
    FROM nguoidung_linhvat 
    INNER JOIN linhvat ON nguoidung_linhvat.MaLinhVat = linhvat.MaLinhVat 
    WHERE nguoidung_linhvat.TaiKhoan = '$taiKhoanEscaped'");

// Hàm xác định ảnh linh vật theo cấp độ
function getLinhVatImage($maLinhVat, $capDo) {
    $linhVatNames = [
        1 => 'sutu',
        2 => 'bachtuoc', 
        3 => 'khi'
    ];
    $name = $linhVatNames[$maLinhVat] ?? 'sutu';
    return '/assets/img/anhlinhvat/' . $name . '-cap' . $capDo . '.png';
}
?>
<style>
    <?= include_once(__DIR__ . "/../../assets/css/home_page.css");
    ?>
    
    /* Linh vật hiển thị - rất nhỏ gọn */
    .linhvat-display {
        position: fixed;
        bottom: 15px;
        right: 15px;
        width: 20px;
        height: 20px;
        z-index: 1000;
        cursor: pointer;
        transition: transform 0.3s ease;
    }
    
    .linhvat-display:hover {
        transform: scale(1.2);
    }
    
    .linhvat-display img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 1px solid #4CAF50;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .linhvat-display__info {
        position: absolute;
        bottom: -25px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.9);
        color: white;
        padding: 2px 4px;
        border-radius: 3px;
        font-size: 7px;
        white-space: nowrap;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
    
    .linhvat-display:hover .linhvat-display__info {
        opacity: 1;
    }
    
    @media (max-width: 768px) {
        .linhvat-display {
            width: 18px;
            height: 18px;
            bottom: 10px;
            right: 10px;
        }
    }
</style>
<div class="grid">
    <div class="row main-page">
        <div class="nav-container">
            <?php
            include_once(__DIR__ . "/../../public/client/navigation.php");
            ?>
        </div>

        <div class="main_content-container">
            <?php if ($userLinhVat): 
                $linhVatImage = getLinhVatImage($userLinhVat['MaLinhVat'], $userLinhVat['CapDo']);
            ?>
                <div class="linhvat-display" title="<?= htmlspecialchars($userLinhVat['TenLinhVat']) ?>">
                    <img src="<?= BASE_URL(ltrim($linhVatImage, '/')) ?>" 
                         alt="<?= htmlspecialchars($userLinhVat['TenLinhVat']) ?>"
                         onerror="this.onerror=null; this.src='<?= BASE_URL('/assets/img/anhlinhvat/4.png') ?>';">
                    <div class="linhvat-display__info">
                        <?= htmlspecialchars($userLinhVat['TenLinhVat']) ?><br>
                        Cấp <?= $userLinhVat['CapDo'] ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="my-course">
                <div class="page__title">Khóa học của tôi:</div>
                <?php

                foreach ($Database->get_list(" SELECT * FROM dangkykhoahoc INNER JOIN khoahoc ON dangkykhoahoc.MaKhoaHoc = khoahoc.MaKhoaHoc AND dangkykhoahoc.TaiKhoan = '" . $_SESSION['account'] . "' and khoahoc.TrangThaiKhoaHoc = 1  ") as $row) {
                    $soTuDaHoc = $Database->num_rows("SELECT * FROM hoctuvung A inner join tuvung B on A.MaTuVung = B.MaTuVung and A.MaKhoaHoc = B.MaKhoaHoc and A.MaBaiHoc = B.MaBaiHoc and A.TaiKhoan = '" . $_SESSION["account"] . "' AND A.MaKhoaHoc = '" . $row["MaKhoaHoc"] . "' and B.TrangThaiTuVung = 1 ");
                    $tongSoTuVung = $Database->num_rows("SELECT * FROM tuvung WHERE MaKhoaHoc = '" . $row["MaKhoaHoc"] . "' and TrangThaiTuVung = 1 ");
                    $tongSoTuVungKho = $Database->num_rows("SELECT * FROM hoctuvung A inner join tuvung B on A.MaTuVung = B.MaTuVung and A.MaKhoaHoc = B.MaKhoaHoc and A.MaBaiHoc = B.MaBaiHoc and A.TaiKhoan = '" . $_SESSION["account"] . "' AND A.MaKhoaHoc = '" . $row["MaKhoaHoc"] . "' and B.TrangThaiTuVung = 1 and A.TuKho = 1");
                    $soHocVien = $Database->num_rows("SELECT * FROM dangkykhoahoc WHERE MaKhoaHoc = '" . $row["MaKhoaHoc"] . "'  ");

                    if ($tongSoTuVung == 0) {
                        $tienTrinhHoc = 0;
                    } else {
                        $tienTrinhHoc = floor(($soTuDaHoc / $tongSoTuVung) * 100);
                    }

                ?>

                    <div class="my-course__plan card">
                        <img src=<?= $row["LinkAnh"] ?> alt="" class="my-course__plan-img">
                        <div class="my-course__plan-content">
                            <div class="my-course__plan-heading">
                                <div class="my-course__plan-heading-text">
                                    <a href="<?= BASE_URL('Page/KhoaHoc/' . $row['MaKhoaHoc'] . '') ?>">
                                        <?= $row["TenKhoaHoc"] ?>
                                    </a>
                                </div>
                            </div>
                            <div class="my-course__plan-heading-sub"><span class="my-course__plan-percent">
                                    <?= $tienTrinhHoc >= 100 ? 100 : $tienTrinhHoc  ?>%
                                </span><span class="my-course__planned">Đã học
                                    <?= $soTuDaHoc ?>/<?= $tongSoTuVung ?>
                                </span></div>
                            <div class="my-course__plan-bar">
                                <div class="my-course__plan-bar-value-english <?= $row['MaKhoaHoc'] ?>" style="width: <?= $tienTrinhHoc >= 100 ? 100 : $tienTrinhHoc ?>%" title="<?= $tienTrinhHoc >= 100 ? 100 : $tienTrinhHoc ?>%"></div>
                            </div>
                            <div class="my-course__plan-tick">
                                <div class="my-course__plan-tick-box" title="Số học viên: <?= $soHocVien ?>">
                                    <img src="<?= BASE_URL("/") ?>/assets/img/practice.svg" alt="" class="my-course__plan-tick-img">
                                    <span class="my-course__plan-tick-number">
                                        <?= $soHocVien ?>
                                    </span>
                                </div>
                                <div class="my-course__plan-tick-box" title="Đã đánh dấu: <?= $tongSoTuVungKho ?> từ khó">

                                    <img src="<?= BASE_URL("/") ?>/assets/img/license.svg" alt="" class="my-course__plan-tick-img">
                                    <span class="my-course__plan-tick-number">
                                        <?= $tongSoTuVungKho ?>
                                    </span>
                                </div>

                                <a href="<?= BASE_URL('Page/KhoaHoc/' . $row['MaKhoaHoc'] . '') ?>" style="margin-left: auto;">
                                    <div class="my-course__plan-tick-btn btn">Học tập</div>
                                </a>

                            </div>
                        </div>
                    </div>

                <?php
                }
                ?>
            </div>
        </div>
        <?php
        include_once(__DIR__ . "/../../public/client/menu_right.php");
        include_once(__DIR__ . "/../../public/client/navigation_mobile.php");

        ?>
        <?php
        require_once(__DIR__ . "/../../public/client/footer.php"); ?>