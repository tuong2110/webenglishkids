<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'Chơi Game | ' . $Database->site("TenWeb") . '';
$locationPage = 'game';
require_once(__DIR__ . "/../../public/client/header.php");

checkLogin();

if (empty($_GET['maGame'])) {
    header("Location: " . BASE_URL("Page/Game"));
    exit;
}

$maGame = check_string($_GET['maGame']);
$maGameEscaped = $Database->escape_string($maGame);
$game = $Database->get_row("SELECT * FROM game WHERE MaGame = '$maGameEscaped' AND TrangThai = 1");

if (!$game) {
    header("Location: " . BASE_URL("Page/Game"));
    exit;
}

// Kiểm tra tim
$taiKhoanEscaped = $Database->escape_string($_SESSION["account"]);
$userTim = $Database->get_row("SELECT * FROM nguoidung_tim WHERE TaiKhoan = '$taiKhoanEscaped'");
if (!$userTim || ($userTim['SoTim'] ?? 0) < $game['SoTimCanThiet']) {
    echo '<script>alert("Bạn không đủ tim để chơi game này!"); window.location.href = "' . BASE_URL("Page/Game") . '";</script>';
    exit;
}

// Lấy từ vựng để chơi game
$listTuVung = $Database->get_list("SELECT MaTuVung, NoiDungTuVung as TuVung, DichNghia as Nghia FROM tuvung WHERE TrangThaiTuVung = 1 ORDER BY RAND() LIMIT 10");

?>
<style>
    .choi-game {
        max-width: 800px;
        margin: 40px auto;
        padding: 20px;
    }
    
    .choi-game__header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .choi-game__title {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    .choi-game__question {
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .choi-game__question-text {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .choi-game__answers {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .choi-game__answer {
        background: #f5f5f5;
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        font-size: 18px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .choi-game__answer:hover {
        background: #e0e0e0;
        border-color: #4CAF50;
    }
    
    .choi-game__answer.selected {
        background: #4CAF50;
        color: white;
        border-color: #4CAF50;
    }
    
    .choi-game__answer.correct {
        background: #4CAF50;
        color: white;
        border-color: #4CAF50;
    }
    
    .choi-game__answer.wrong {
        background: #f44336;
        color: white;
        border-color: #f44336;
    }
    
    .choi-game__controls {
        text-align: center;
        margin-top: 30px;
    }
    
    .btn-submit {
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
    
    .btn-submit:hover {
        background: #45a049;
    }
    
    .btn-submit:disabled {
        background: #ccc;
        cursor: not-allowed;
    }
    
    .choi-game__result {
        text-align: center;
        padding: 20px;
        border-radius: 12px;
        margin-top: 20px;
        font-size: 20px;
        font-weight: bold;
    }
    
    .choi-game__result.win {
        background: #4CAF50;
        color: white;
    }
    
    .choi-game__result.lose {
        background: #f44336;
        color: white;
    }
</style>

<div class="grid">
    <div class="row main-page">
        <div class="nav-container">
            <?php include_once(__DIR__ . "/../../public/client/navigation.php"); ?>
        </div>

        <div class="main_content-container">
            <div class="choi-game">
                <div class="choi-game__header">
                    <div class="choi-game__title"><?= htmlspecialchars($game['TenGame']) ?></div>
                    <div>Tim cần: <?= $game['SoTimCanThiet'] ?> | Điểm thắng: +<?= $game['DiemThang'] ?></div>
                </div>
                
                <div id="gameContent">
                    <div class="choi-game__question">
                        <div class="choi-game__question-text" id="questionText">Đang tải câu hỏi...</div>
                        <div class="choi-game__answers" id="answersList"></div>
                    </div>
                    <div class="choi-game__controls">
                        <button class="btn-submit" id="btnSubmit" disabled>Trả Lời</button>
                    </div>
                </div>
                
                <div id="gameResult" style="display: none; padding: 40px; text-align: center; background: #f5f5f5; border-radius: 12px; margin-top: 20px;">
                    <!-- Kết quả sẽ được hiển thị ở đây -->
                </div>
            </div>
        </div>

        <?php
        include_once(__DIR__ . "/../../public/client/navigation_mobile.php");
        ?>
    </div>
</div>

<script>
    let currentQuestion = 0;
    let questions = <?= json_encode(array_slice($listTuVung, 0, 5)) ?>;
    let selectedAnswer = null;
    let correctAnswers = 0;
    
    function loadQuestion() {
        console.log('Loading question:', currentQuestion + 1, 'of', questions.length);
        if (currentQuestion >= questions.length) {
            console.log('All questions answered, ending game...');
            endGame();
            return;
        }
        
        const question = questions[currentQuestion];
        selectedAnswer = null;
        
        // Tạo câu hỏi và đáp án
        document.getElementById('questionText').textContent = 'Nghĩa của từ "' + question.TuVung + '" là gì?';
        
        // Tạo đáp án (1 đúng, 3 sai từ các câu hỏi khác)
        const wrongAnswers = questions
            .filter((q, idx) => idx !== currentQuestion)
            .slice(0, 3)
            .map(q => q.Nghia);
        
        // Nếu không đủ 3 đáp án sai, thêm đáp án mặc định
        while (wrongAnswers.length < 3) {
            wrongAnswers.push('Đáp án sai ' + (wrongAnswers.length + 1));
        }
        
        const answers = [
            { text: question.Nghia, correct: true },
            { text: wrongAnswers[0], correct: false },
            { text: wrongAnswers[1], correct: false },
            { text: wrongAnswers[2], correct: false }
        ];
        
        // Shuffle answers
        answers.sort(() => Math.random() - 0.5);
        
        const answersHtml = answers.map((answer, index) => {
            return `<div class="choi-game__answer" data-index="${index}" data-correct="${answer.correct ? '1' : '0'}">${answer.text}</div>`;
        }).join('');
        
        document.getElementById('answersList').innerHTML = answersHtml;
        document.getElementById('btnSubmit').disabled = true;
        
        // Add click handlers
        document.querySelectorAll('.choi-game__answer').forEach(answer => {
            answer.addEventListener('click', function() {
                document.querySelectorAll('.choi-game__answer').forEach(a => a.classList.remove('selected'));
                this.classList.add('selected');
                selectedAnswer = this;
                document.getElementById('btnSubmit').disabled = false;
            });
        });
    }
    
    function submitAnswer() {
        if (!selectedAnswer) return;
        
        const isCorrect = selectedAnswer.dataset.correct === '1';
        
        // Disable all answers
        document.querySelectorAll('.choi-game__answer').forEach(a => {
            a.style.pointerEvents = 'none';
            if (a.dataset.correct === '1') {
                a.classList.add('correct');
            } else if (a === selectedAnswer && !isCorrect) {
                a.classList.add('wrong');
            }
        });
        
        if (isCorrect) {
            correctAnswers++;
        }
        
        document.getElementById('btnSubmit').disabled = true;
        
        console.log('Answer submitted. Correct:', isCorrect, 'Total correct:', correctAnswers);
        
        setTimeout(() => {
            currentQuestion++;
            console.log('Moving to next question:', currentQuestion + 1);
            loadQuestion();
        }, 2000);
    }
    
    function endGame() {
        const isWin = correctAnswers >= Math.ceil(questions.length / 2);
        
        console.log('Ending game. Correct:', correctAnswers, 'Total:', questions.length, 'Win:', isWin);
        
        // Ẩn game content ngay lập tức
        const gameContent = document.getElementById('gameContent');
        const gameResult = document.getElementById('gameResult');
        
        if (gameContent) {
            gameContent.style.display = 'none';
        }
        
        // Hiển thị kết quả tạm thời
        let resultHtml = '';
        if (isWin) {
            resultHtml = `<div class="choi-game__result win">
                <div style="font-size: 32px; margin-bottom: 20px;">🎉</div>
                <div style="font-size: 24px; font-weight: bold; margin-bottom: 15px;">Chúc mừng! Bạn đã thắng!</div>
                <div style="font-size: 18px; margin-bottom: 10px;">Bạn trả lời đúng <strong>${correctAnswers}/${questions.length}</strong> câu</div>
                <div style="font-size: 18px; color: #4CAF50; font-weight: bold;">Nhận được +<?= $game['DiemThang'] ?> điểm thưởng</div>
                <div style="margin-top: 20px; font-size: 14px; color: #666;">Đang lưu kết quả...</div>
            </div>`;
        } else {
            resultHtml = `<div class="choi-game__result lose">
                <div style="font-size: 32px; margin-bottom: 20px;">😢</div>
                <div style="font-size: 24px; font-weight: bold; margin-bottom: 15px;">Bạn đã thua!</div>
                <div style="font-size: 18px; margin-bottom: 10px;">Bạn trả lời đúng <strong>${correctAnswers}/${questions.length}</strong> câu</div>
                <div style="font-size: 18px; color: #f44336; font-weight: bold;">Đã mất <?= $game['SoTimCanThiet'] ?> tim</div>
                <div style="margin-top: 20px; font-size: 14px; color: #666;">Đang lưu kết quả...</div>
            </div>`;
        }
        
        if (gameResult) {
            gameResult.innerHTML = resultHtml;
            gameResult.style.display = 'block';
        }
        
        // Gửi AJAX để lưu kết quả
        $.ajax({
            url: "<?= BASE_URL("assets/ajaxs/Game.php"); ?>",
            method: "POST",
            data: {
                type: 'KetThucGame',
                maGame: <?= $game['MaGame'] ?>,
                ketQua: isWin ? 'thang' : 'thua',
                soCauDung: correctAnswers,
                tongSoCau: questions.length
            },
            success: function(response) {
                console.log('Game result response:', response);
                try {
                    const json = typeof response === 'string' ? JSON.parse(response) : response;
                    console.log('Parsed JSON:', json);
                    
                    // Cập nhật kết quả với thông tin từ server
                    if (json.status === 'success') {
                        if (isWin) {
                            resultHtml = `<div class="choi-game__result win">
                                <div style="font-size: 32px; margin-bottom: 20px;">🎉</div>
                                <div style="font-size: 24px; font-weight: bold; margin-bottom: 15px;">Chúc mừng! Bạn đã thắng!</div>
                                <div style="font-size: 18px; margin-bottom: 10px;">Bạn trả lời đúng <strong>${correctAnswers}/${questions.length}</strong> câu</div>
                                <div style="font-size: 18px; color: #4CAF50; font-weight: bold;">Nhận được +<?= $game['DiemThang'] ?> điểm thưởng</div>
                                <div style="margin-top: 20px; font-size: 14px; color: #4CAF50;">✅ Đã lưu kết quả thành công!</div>
                            </div>`;
                        } else {
                            resultHtml = `<div class="choi-game__result lose">
                                <div style="font-size: 32px; margin-bottom: 20px;">😢</div>
                                <div style="font-size: 24px; font-weight: bold; margin-bottom: 15px;">Bạn đã thua!</div>
                                <div style="font-size: 18px; margin-bottom: 10px;">Bạn trả lời đúng <strong>${correctAnswers}/${questions.length}</strong> câu</div>
                                <div style="font-size: 18px; color: #f44336; font-weight: bold;">Đã mất <?= $game['SoTimCanThiet'] ?> tim</div>
                                <div style="margin-top: 20px; font-size: 14px; color: #4CAF50;">✅ Đã lưu kết quả thành công!</div>
                            </div>`;
                        }
                        
                        if (gameResult) {
                            gameResult.innerHTML = resultHtml;
                        }
                        
                        // Redirect sau 3 giây
                        setTimeout(() => {
                            window.location.href = "<?= BASE_URL("Page/Game") ?>";
                        }, 3000);
                    } else {
                        console.error('Error saving game result:', json.message);
                        toastr.error(json.message || 'Không thể lưu kết quả', 'Lỗi!');
                        setTimeout(() => {
                            window.location.href = "<?= BASE_URL("Page/Game") ?>";
                        }, 2000);
                    }
                } catch (e) {
                    console.error('Parse error:', e, response);
                    toastr.error('Lỗi xử lý kết quả', 'Lỗi!');
                    setTimeout(() => {
                        window.location.href = "<?= BASE_URL("Page/Game") ?>";
                    }, 2000);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error, xhr.responseText);
                toastr.error('Không thể lưu kết quả', 'Lỗi!');
                setTimeout(() => {
                    window.location.href = "<?= BASE_URL("Page/Game") ?>";
                }, 2000);
            }
        });
    }
    
    document.getElementById('btnSubmit').addEventListener('click', submitAnswer);
    
    // Load first question
    loadQuestion();
</script>

<?php require_once(__DIR__ . "/../../public/client/footer.php"); ?>

