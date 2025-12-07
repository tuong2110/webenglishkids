<?php
require_once(__DIR__ . "/configs/config.php");
require_once(__DIR__ . "/configs/function.php");
$title = 'Trang chủ nền tảng học ngoại ngữ online | ' . $Database->site("TenWeb");
$META_TITLE = "5Fs Group - Nền tảng học ngoại ngữ online";
$META_IMAGE = "https://i.imgur.com/Ldhl3hK.png";
$META_DESCRIPTION = "5Fs Group - Nền tảng học ngoại ngữ online";
$META_SITE = BASE_URL("");
require_once(__DIR__ . "/public/client/header.php");

$soLuongKhoaHoc = $Database->get_row("select count(*) as SoLuong from khoahoc ")["SoLuong"];
$soLuongHocVien = $Database->get_row("select count(*) as SoLuong from nguoidung ")["SoLuong"];

// Lấy top 3 học sinh xuất sắc
$topStudents = $Database->get_list("SELECT * FROM nguoidung ORDER BY CapDo DESC, TongKinhNghiem DESC LIMIT 3");

?>

<style>
    <?= include_once(__DIR__ . "/assets/css/main.css");
    ?>
</style>
<div class="header">
    <div class="grid wide">
        <div class="header_wrap">
            <a href="<?= BASE_URL("/") ?>" style="display: flex; align-items: center; gap: 12px;">
                <img src="https://i.imgur.com/HE3eJGR.png" alt="Owl" class="header__owl">
                <h2 class="header__name"><?= $Database->site("TenWeb") ?></h2>
            </a>
            <div class="nav">
                <a href="<?= BASE_URL("Page/KhoaHoc") ?>" class="nav__course">Các khóa học </a>
                <?php
                if (isset($_SESSION["account"])) {
                ?>
                    <a href="<?= BASE_URL("Page/Home") ?>" class="nav__statr btn">Bắt đầu học</a>
                <?php
                } else {
                ?>
                    <a href="<?= BASE_URL("Auth/DangNhap") ?>" class="nav__statr btn">Bắt đầu học</a>

                <?php
                }
                ?>

            </div>
        </div>
    </div>
</div>
<div class="slider">
    <div class="grid wide">
        <div class="slider-wrap">
            <div class="slider__content">
                <h1 class="slider__content-heading">Học

                    <div style="line-height:0" class="headline-icon"><img src="https://i.imgur.com/HE3eJGR.png" loading="lazy" class="LazyImage__Img-sc-12k26ab-0 eVDYkS"></div>
                    <div style="line-height:0;top: -40px;right: -40px;" class="headline-icon"><img src="https://i.imgur.com/GF5UgJs.png" loading="lazy" class="LazyImage__Img-sc-12k26ab-0 eVDYkS"></div>
                    <div style="line-height:0; top: -40px;right: -40px;" class="headline-icon"><img src="https://i.imgur.com/fmKnI8E.png" loading="lazy" class="LazyImage__Img-sc-12k26ab-0 eVDYkS"></div>

                    <span class="slider__content-heading--color">tiếng Anh</span>
                    cùng các bạn linh vật dễ thương
                </h1>
                <p class="slider__content-text">Học tiếng Anh thật vui và dễ dàng! Các bạn nhỏ sẽ học từ mới, 
                    chơi game và nhận phần thưởng xinh xắn mỗi ngày.</p>
                <a href="<?= BASE_URL("Auth/DangNhap") ?>" class="slider__content-start btn">Bắt đầu</a>
            </div>
            <div class="slider__img">
                <!-- 3 linh vật thay thế nhân vật -->
                <img src="<?= BASE_URL("/") ?>/assets/img/anhlinhvat/bachtuoc-cap1.png" loading="lazy" alt="Linh vật Bạch Tuộc" class="image_1">
                <img src="<?= BASE_URL("/") ?>/assets/img/anhlinhvat/khi-cap1.png" loading="lazy" alt="Linh vật Khỉ" class="image_2">
                <img src="<?= BASE_URL("/") ?>/assets/img/anhlinhvat/sutu-cap1.png" loading="lazy" alt="Linh vật Sư Tử" class="image_3">
            </div>
        </div>
    </div>
    <div class="course">
        <ul class="course__list">
            <li class="course__item course_khoahoc">
                <div class="course__item-number"><?= $soLuongKhoaHoc  ?></div>
                <div class="course__item-text">KHÓA HỌC</div>
            </li>
            <li class="course__item nation course__item--separate">
                <img src="<?= BASE_URL("/") ?>/assets/img/America.png" alt="<?= $Database->site("TenWeb") . ' - Khóa học tiếng Anh' ?>" class="course__item-img">
                <div class="course__item-text">TIẾNG ANH</div>
            </li>
            <li class="course__item course__item--separate course_hocvien">
                <div class="course__item-number"><?= $soLuongHocVien  ?></div>
                <div class="course__item-text">HỌC VIÊN</div>
            </li>
        </ul>
    </div>
</div>

<div class="reason">
    <div class="grid wide">
        <h1 class="introduce__heading">Tại sao các bạn nhỏ thích <?= $Database->site("TenWeb") ?>?</h1>
        <div class="why_use_container">

            <div class="reason__content">
                <div class="reason__warp-img " style="background-color: #A2D6E5;">
                    <img class="reason__img" src="https://i.imgur.com/Q9GYNuV.png" alt="<?= $Database->site("TenWeb") . ' - Học dễ nhớ, nhớ lâu' ?>">
                </div>
                <p class="reason__text">Học dễ nhớ, nhớ lâu với hình ảnh và trò chơi vui nhộn</p>
            </div>


            <div class="reason__content">
                <div class="reason__warp-img " style="background-color: #D0C9E7; min-height: 136px; display: flex; justify-content: center; align-items: center;">
                    <div style="display: flex; justify-content: center; align-items: center; gap: 12px; flex-wrap: wrap; padding: 15px;">
                        <?php foreach ($topStudents as $index => $student): ?>
                            <div style="text-align: center;">
                                <img src="<?= $student["AnhDaiDien"] ?>" alt="<?= $student["TenHienThi"] ?>" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 4px solid <?= $index === 0 ? '#FFD700' : ($index === 1 ? '#C0C0C0' : '#CD7F32') ?>; box-shadow: 0 4px 12px rgba(0,0,0,0.3); transition: transform 0.3s ease;">
                                <div style="font-size: 16px; font-weight: bold; margin-top: 6px;">
                                    <?php if ($index === 0): ?>
                                        <span style="color: #FFD700;">🥇</span>
                                    <?php elseif ($index === 1): ?>
                                        <span style="color: #C0C0C0;">🥈</span>
                                    <?php else: ?>
                                        <span style="color: #CD7F32;">🥉</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 11px; color: #333; font-weight: 600; margin-top: 2px; max-width: 70px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= $student["TenHienThi"] ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <p class="reason__text">TOP BẠN NHỎ XUẤT SẮC</p>
            </div>


            <div class="reason__content">
                <div class="reason__warp-img " style="background-color: #8AD6C2;">
                    <img class="reason__img" src="https://i.imgur.com/9ifba7t.png" alt="<?= $Database->site("TenWeb") . ' - Học như đang chơi, chơi mà học được' ?>">
                </div>
                <p class="reason__text">Học như đang chơi, chơi mà học được! Vừa vui vừa giỏi tiếng Anh.</p>
            </div>


            <div class="reason__content">
                <div class="reason__warp-img " style="background-color: #F6C2C3;">
                    <img class="reason__img" src="https://i.imgur.com/lqi2N7I.png" alt="<?= $Database->site("TenWeb") . ' - Từ những từ đơn giản đến những câu hay' ?>">
                </div>
                <p class="reason__text">Từ những từ đơn giản đến những câu hay, phù hợp với mọi bạn nhỏ</p>

            </div>
        </div>
    </div>
</div>
<div class="introduce">
    <div class="grid wide">
        <h1 class="introduce__heading">Học miễn phí, mọi lúc mọi nơi</h1>
        <div class="introduce_platform">

            <div class="introduce__content">
                <div class="introduce__wrap-img">
                    <img src="<?= BASE_URL("/") ?>/assets/img/menu.png" alt="<?= $Database->site("TenWeb") . ' - Lộ trình học tập dành riêng cho bạn' ?>" class="introduce__content-img">
                </div>
                <h3 class="introduce__content-heading">Lộ trình học tập</h3>
                <p class="introduce__content-text">Lộ trình học tập dành riêng cho bạn, từ dễ đến khó</p>
            </div>


            <div class="introduce__content">
                <div class="introduce__wrap-img">
                    <img src="<?= BASE_URL("/") ?>/assets/img/book.png" alt="<?= $Database->site("TenWeb") . ' - Nhiều từ vựng hay và dễ học' ?>" class="introduce__content-img">
                </div>
                <h3 class="introduce__content-heading">Nhiều từ vựng hay</h3>
                <p class="introduce__content-text">Nhiều từ vựng hay và dễ học, phù hợp với các bạn nhỏ</p>
            </div>


            <div class="introduce__content">
                <div class="introduce__wrap-img">
                    <img src="<?= BASE_URL("/") ?>/assets/img/free.png" alt="<?= $Database->site("TenWeb") . ' - Hoàn toàn miễn phí cho tất cả các bạn nhỏ' ?>" class="introduce__content-img">
                </div>
                <h3 class="introduce__content-heading">Hoàn toàn miễn phí</h3>
                <p class="introduce__content-text">Hoàn toàn miễn phí cho tất cả các bạn nhỏ</p>

            </div>
        </div>
    </div>
</div>
<div style="
 
    margin-top: 90px;
    padding: 40px 0px;
">
    <div class="introduce_website">
        <div class="introduce_website-left">

            <h1 class="introduce__heading">Từ vựng được giải thích dễ hiểu</h1>
            <p class="support-browser__content">Mỗi từ tiếng Anh đều được giải thích bằng tiếng Việt dễ hiểu, giúp các bạn nhỏ học nhanh và nhớ lâu hơn.</p>
        </div>
        <div class="introduce_website-right">
            <img src="https://i.imgur.com/aDm5Pgc.png" alt="<?= $Database->site("TenWeb") . ' - Nhiều từ vựng được dịch nghĩa chính xác nhất' ?>" />
        </div>
    </div>
</div>
<div style="
 
    margin-top: 90px;
    padding: 40px 0px;
">
    <div class="introduce_website">
        <div class="introduce_website-right">
            <img src="https://i.imgur.com/RjfROrU.png" alt="<?= $Database->site("TenWeb") . ' - Ví dụ cụ thể cho từng từ' ?>" />
        </div>
        <div class="introduce_website-left">
            <h1 class="introduce__heading">Có ví dụ dễ hiểu cho mỗi từ</h1>
            <p class="support-browser__content">Mỗi từ tiếng Anh đều có ví dụ dễ hiểu, giúp các bạn nhỏ biết cách dùng từ trong câu.</p>
        </div>

    </div>
</div>
<div class="comment-slider">
        <h1 class="introduce__heading">Các bạn nhỏ nói gì về <?= $Database->site("TenWeb") ?></h1>
    <div class="grid wide">
        <div class="comment-slider__list">
            <?php

            foreach ($Database->get_list(" select * from danhgiakhoahoc A inner join nguoidung B on A.TaiKhoan = B.TaiKhoan order by A.ThoiGian desc limit 9") as $danhGiaKhoaHoc) {

            ?>
                <div class="comment-item">
                    <div class="comment-item__wrap">
                        <div class="comment-left">
                            <img src="<?= $danhGiaKhoaHoc["AnhDaiDien"] ?>" alt="<?= $danhGiaKhoaHoc["TenHienThi"] ?>" class="comment-left__img">
                            <p class="comment-left__text"><?= $danhGiaKhoaHoc["NoiDungDanhGia"] ?></p>
                            <div class="comment-left__name"><?= $danhGiaKhoaHoc["TenHienThi"] ?></div>
                            <div class="comment-item__balloon--left"></div>
                        </div>
                        <div class="comment-right">
                            <div class="comment-right-person">
                                <img src="<?= BASE_URL("/") ?>/assets/img/Trung.png" alt="Học viên của <?= $Database->site("TenWeb") ?>" class="comment-right-person__img">
                                <div class="comment-right-person__wrap-content">
                                    <p class="comment-right-person__text">Em thích học ở đây lắm! Có nhiều trò chơi vui và 
                                        các bạn linh vật dễ thương. Em học được nhiều từ mới mỗi ngày!</p>
                                    <div class="comment-right-person__name">Nguyễn Đức Trung</div>
                                </div>
                                <div class="comment-item__balloon--right"></div>
                            </div>
                            <div class="comment-right-person">
                                <img src="<?= BASE_URL("/") ?>/assets/img/Quynh.png" alt="Học viên của <?= $Database->site("TenWeb") ?>" class="comment-right-person__img">
                                <div class="comment-right-person__wrap-content">
                                    <p class="comment-right-person__text">Em học tiếng Anh ở đây mỗi ngày. Các từ vựng dễ nhớ 
                                        và có nhiều game hay. Em rất thích!</p>
                                    <div class="comment-right-person__name">Lê Thanh Quỳnh</div>
                                </div>
                                <div class="comment-item__balloon--right"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>



        </div>
        <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.0.min.js"></script>
        <script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js">
        </script>
        <script src="<?= BASE_URL("/") ?>/assets/javascript/comment-slider.js?t=<?= rand(0, 99999) ?>"></script>
    </div>
</div>

<div style="
    background-color: #FAFCFF;
    margin-top: 90px;
    padding: 40px 0px;
">
    <div class="support-browser">
        <h1 class="introduce__heading">Các trình duyệt hỗ trợ</h1>
        <p class="support-browser__content">Các bạn nhỏ có thể học tiếng Anh trên máy tính, máy tính bảng hoặc điện thoại. 
            Chỉ cần mở trình duyệt là có thể học ngay!</p>
        <div class="support-browser__list-browser">
            <div class="support-browser__item">
                <img src="https://i.imgur.com/Dp6UTr8.png" alt="<?= $Database->site("TenWeb") ?> - Học trên Firefox" class="support-browser__item-img">
                <div class="support-browser__item-name">Firefox</div>
            </div>
            <div class="support-browser__item">
                <img src="<?= BASE_URL("/") ?>/assets/img/chrome.svg" alt="<?= $Database->site("TenWeb") ?> - Học trên Chrome" class="support-browser__item-img">
                <div class="support-browser__item-name">Chrome</div>
            </div>
            <div class="support-browser__item">
                <img src="<?= BASE_URL("/") ?>/assets/img/safari.svg" alt="<?= $Database->site("TenWeb") ?> - Học trên Safari" class="support-browser__item-img">
                <div class="support-browser__item-name">Safari</div>
            </div>
            <div class="support-browser__item">
                <img src="<?= BASE_URL("/") ?>/assets/img/opera.svg" alt="<?= $Database->site("TenWeb") ?> - Học trên Opera" class="support-browser__item-img">
                <div class="support-browser__item-name">Opera</div>
            </div>
        </div>
    </div>
</div>
<div class="info">
    <div class="info-wrap">
        <h1 class="introduce__heading" style="color: #fff">Nhận thông tin mới nhất từ chúng tôi</h1>
        <div class="info-wrap-form">
            <input type="email" placeholder="Nhập email của bạn vào đây" class="info__input">
            <div class="btn">
                Đăng ký

            </div>
        </div>
    </div>
</div>

<script>
    anime({
        targets: '.headline-icon',
        scale: 1.2,
        direction: 'alternate',
        loop: true,
        easing: 'easeInOutSine'
    });

    // Animation cho 3 linh vật
    anime({
        targets: '.image_1',
        direction: 'alternate',
        loop: true,
        keyframes: [{
                translateY: -20,
                scale: 1.1,
                rotate: -5
            },
            {
                translateY: 20,
                scale: 1,
                rotate: 5
            },
        ],
        duration: 3000,
        easing: 'easeInOutSine'
    });
    anime({
        targets: '.image_2',
        keyframes: [{
                translateY: -15,
                scale: 1.1,
                rotate: 5
            },
            {
                translateY: 15,
                scale: 1,
                rotate: -5
            },
        ],
        duration: 3500,
        direction: 'alternate',
        loop: true,
        easing: 'easeInOutSine',
        delay: 200
    });
    anime({
        targets: '.image_3',
        keyframes: [{
                translateY: -25,
                scale: 1.1,
                rotate: -3
            },
            {
                translateY: 25,
                scale: 1,
                rotate: 3
            },
        ],
        duration: 3200,
        direction: 'alternate',
        loop: true,
        easing: 'easeInOutSine',
        delay: 400
    });
</script>



<?php
require_once(__DIR__ . "/public/client/footer_about.php");

require_once(__DIR__ . "/public/client/footer.php");

?>