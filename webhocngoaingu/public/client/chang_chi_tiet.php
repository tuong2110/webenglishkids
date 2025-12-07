<?php
/**
 * Trang hiển thị bài học trong một chặng
 */
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'Chặng chi tiết | ' . $Database->site("TenWeb") . '';
$locationPage = 'khoahoc';
$META_TITLE = "5Fs Group - Chặng chi tiết";
require_once(__DIR__ . "/../../public/client/header.php");

checkLogin();

if (isset($_GET['maKhoaHoc']) && isset($_GET['maChang'])) {
    $maKhoaHoc = check_string($_GET['maKhoaHoc']);
    $maChang = intval($_GET['maChang']);
    
    $khoaHoc = $Database->get_row("SELECT * FROM `khoahoc` WHERE `MaKhoaHoc` = '$maKhoaHoc' and TrangThaiKhoaHoc = 1");
    $chang = $Database->get_row("SELECT * FROM `chang` WHERE `MaChang` = $maChang AND `MaKhoaHoc` = '$maKhoaHoc' AND TrangThaiChang = 1");
    
    if (!$khoaHoc || !$chang) {
        return die('<script type="text/javascript">
            setTimeout(function(){ location.href = "' . BASE_URL('Page/KhoaHoc') . '" }, 0);
        </script>');
    }
    
    $checkDangKy = $Database->get_row("SELECT * FROM dangkykhoahoc WHERE `TaiKhoan` = '" . $_SESSION["account"] . "' AND `MaKhoaHoc` = '" . $khoaHoc["MaKhoaHoc"] . "' ") > 0;
    
    // Kiểm tra chặng trước đã hoàn thành chưa
    $changTruoc = $Database->get_row("SELECT * FROM `chang` WHERE `MaKhoaHoc` = '$maKhoaHoc' AND `ThuTuChang` = " . ($chang['ThuTuChang'] - 1) . " AND TrangThaiChang = 1");
    $changTruocHoanThanh = false;
    if ($changTruoc) {
        $changTruocHoanThanh = $Database->get_row("SELECT * FROM `hoanthanhchang` WHERE `TaiKhoan` = '" . $_SESSION["account"] . "' AND `MaChang` = " . $changTruoc['MaChang'] . " AND `TiLeHoanThanh` >= 100");
    }
    
    // Chặng 1 luôn mở, các chặng khác cần hoàn thành chặng trước
    $changMoKhoa = ($chang['ThuTuChang'] == 1) || $changTruocHoanThanh;
    
    // Lấy danh sách bài học trong chặng
    $danhSachBaiHoc = $Database->get_list("SELECT * FROM baihoc WHERE MaKhoaHoc = '$maKhoaHoc' AND MaChang = $maChang AND TrangThaiBaiHoc = 1 ORDER BY MaBaiHoc ASC");
} else {
    return die('<script type="text/javascript">
        setTimeout(function(){ location.href = "' . BASE_URL('Page/KhoaHoc') . '" }, 0);
    </script>');
}
?>
<style>
    <?= include_once(__DIR__ . "/../../assets/css/course_page.css"); ?>
</style>

<div class="grid">
    <div class="row main-page">
        <div class="nav-container">
            <?php include_once(__DIR__ . "/../../public/client/navigation.php"); ?>
        </div>
        <div class="main_content-container">
            <div class="list-course">
                <div class="list-course__detail" style="display: block;">
                    <nav class="breadcrumb has-succeeds-separator page__title" aria-label="breadcrumbs">
                        <ul>
                            <li><a href="<?= BASE_URL("Page/KhoaHoc") ?>">Khóa học</a></li>
                            <li><a href="<?= BASE_URL("Page/KhoaHoc/" . $khoaHoc["MaKhoaHoc"]) ?>"><?= $khoaHoc["TenKhoaHoc"] ?></a></li>
                            <li class="is-active"><a href="#"><?= $chang["TenChang"] ?></a></li>
                        </ul>
                    </nav>

                    <div class="course-detail__wrap-content">
                        <div class="course-detail__plan card">
                            <div class="course-detail__plan-header">
                                <?php if ($chang["HinhAnhChang"]): ?>
                                    <img src="<?= $chang["HinhAnhChang"] ?>" alt="" class="course-detail__plan-header-img">
                                <?php else: ?>
                                    <img src="<?= BASE_URL("/") ?>/assets/img/book_list.svg" alt="" class="course-detail__plan-header-img">
                                <?php endif; ?>
                                <div class="course-detail__plan-header-content">
                                    <div class="course-detail__plan-header-title"><?= $chang["TenChang"] ?></div>
                                    <div class="course-detail__plan-header-text"><?= $chang["MoTaChang"] ?: "Chặng " . $chang["ThuTuChang"] ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="course-detail__footer">
                            <div class="grid">
                                <?php if (!$changMoKhoa): ?>
                                    <div style="text-align: center; padding: 40px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 10px; margin: 20px 0;">
                                        <h2 style="color: #856404; margin-bottom: 20px;">🔒 Chặng này chưa được mở khóa</h2>
                                        <p style="color: #856404; font-size: 1.6rem;">Bạn cần hoàn thành chặng trước để mở khóa chặng này!</p>
                                        <a href="<?= BASE_URL("Page/KhoaHoc/" . $khoaHoc["MaKhoaHoc"]) ?>" class="btn btn--primary" style="margin-top: 20px; display: inline-block;">Quay lại</a>
                                    </div>
                                <?php elseif (empty($danhSachBaiHoc)): ?>
                                    <div style="text-align: center; padding: 40px;">
                                        <p style="font-size: 1.8rem; color: #666;">Chưa có bài học nào trong chặng này.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="course-detail__content-container">
                                        <?php foreach ($danhSachBaiHoc as $baihoc): 
                                            $danhSachTuVungTheoBaiHoc = $Database->get_row("SELECT COUNT(*) AS SoLuongTuVungBaiHoc FROM tuvung WHERE MaBaiHoc = '" . $baihoc["MaBaiHoc"] . "' AND MaKhoaHoc = '" . $baihoc["MaKhoaHoc"] . "' and TrangThaiTuVung = 1 ")["SoLuongTuVungBaiHoc"];
                                            $danhSachTuVungDaHocTheoBaiHoc = $Database->get_row("SELECT COUNT(*) AS SoLuongTuVungDaHoc FROM hoctuvung A inner join tuvung B on A.MaTuVung = B.MaTuVung and A.MaKhoaHoc = B.MaKhoaHoc and A.MaBaiHoc = B.MaBaiHoc and B.TrangThaiTuVung = 1 and A.MaBaiHoc = '" . $baihoc["MaBaiHoc"] . "' AND A.MaKhoaHoc = '" . $baihoc["MaKhoaHoc"] . "' AND A.TaiKhoan = '" . $_SESSION["account"] . "' ")["SoLuongTuVungDaHoc"];
                                            $baiHocHoanThanh = $checkDangKy && $danhSachTuVungTheoBaiHoc > 0 && $danhSachTuVungTheoBaiHoc == $danhSachTuVungDaHocTheoBaiHoc;
                                        ?>
                                            <div class="stage card" title="Đã hoàn thành: <?= $danhSachTuVungDaHocTheoBaiHoc . '/' . $danhSachTuVungTheoBaiHoc ?>">
                                                <div class="stage__background-img">
                                                    <img src="<?= BASE_URL("/") ?>/assets/img/book_list.svg" alt="" class="stage__img">
                                                    <div class="stage__index-background"><span class="stage__index"><?= $baihoc["MaBaiHoc"] ?></span></div>
                                                    <div class="stage__learned <?= $baiHocHoanThanh ? "" : "stage__learned--no-active" ?>">
                                                        <img src="<?= BASE_URL("/") ?>/assets/img/learned-list.svg" alt="" class="stage__learned-img">
                                                    </div>
                                                </div>
                                                <div class="stage__title">
                                                    <a href="<?= BASE_URL("Page/BaiHoc/" . $khoaHoc["MaKhoaHoc"] . "/" . $baihoc["MaBaiHoc"]) ?>"><?= $baihoc["TenBaiHoc"] ?></a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once(__DIR__ . "/../../public/client/footer.php"); ?>


