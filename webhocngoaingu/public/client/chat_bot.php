<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'Trợ giúp từ ChatBot | ' . $Database->site('TenWeb') . '';
$locationPage = 'chatbot_page';
require_once(__DIR__ . "/../../public/client/header.php");

checkLogin();
$checkChatBotRoom = $Database->get_row("select * from chatbot_room where TaiKhoan = '" . $_SESSION["account"] . "' ");
$getTaiKhoan = $Database->get_row("select * from nguoidung where TaiKhoan = '" . $_SESSION["account"] . "'");
?>
<style>
    <?= include_once(__DIR__ . "/../../assets/css/bxh.css"); ?>
    
    .chatbot-messages {
        max-height: 500px;
        overflow-y: auto;
        padding: 20px;
        margin-bottom: 20px;
        background: #f5f5f5;
        border-radius: 8px;
    }
    
    .chatbot-message {
        display: flex;
        margin-bottom: 15px;
        align-items: flex-start;
    }
    
    .chatbot-message--user {
        flex-direction: row-reverse;
    }
    
    .chatbot-message-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin: 0 10px;
    }
    
    .chatbot-message-content {
        max-width: 70%;
        background: white;
        padding: 10px 15px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .chatbot-message--user .chatbot-message-content {
        background: #007bff;
        color: white;
    }
    
    .chatbot-message-name {
        font-weight: bold;
        font-size: 12px;
        margin-bottom: 5px;
        opacity: 0.8;
    }
    
    .chatbot-message-text {
        word-wrap: break-word;
    }
    
    .chatbot-message-time {
        font-size: 10px;
        margin-top: 5px;
        opacity: 0.6;
    }
    
    .chatbot-input-container {
        display: flex;
        gap: 10px;
        align-items: flex-end;
    }
    
    .chatbot-input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        resize: vertical;
        font-family: inherit;
    }
    
    .chatbot-input:focus {
        outline: none;
        border-color: #007bff;
    }
    
    .chatbot-content-text {
        text-align: center;
        padding: 20px;
        color: #666;
    }
</style>
<div class="grid">
    <div class="row main-page">
        <div class="nav-container">
            <?php include_once(__DIR__ . "/../../public/client/navigation.php"); ?>
        </div>

        <div class="main_content-container">
            <div class="table-rating">
                <div class="page__title">Trợ giúp từ ChatBot</div>
                <div class="table-rating__header">
                    <img src="https://i.imgur.com/0J4kSSu.png" alt="" class="table-rating__header-img">
                </div>
                <div class="table-rating__content">
                    <?php if ($checkChatBotRoom <= 0): ?>
                        <div class="chatbot-content-text">
                            Bạn chưa có phòng chat nào với ChatBot 5FsGroup
                        </div>
                        <div class="btn btn--primary" onclick="createNewRoom()">Tạo room mới</div>
                    <?php else: ?>
                        <div id="chat_content" class="chatbot-messages"></div>
                        <div class="chatbot-input-container">
                            <textarea id="contentInput" class="chatbot-input" placeholder="Nhập câu hỏi của bạn..." rows="2"></textarea>
                            <button id="btnSend" class="btn btn--primary" onclick="sendMessage()">Gửi</button>
                            <button class="btn btn--secondary" onclick="deleteMessages()">Xóa tin nhắn</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Tạo room mới
    function createNewRoom() {
        $.ajax({
            url: "<?= BASE_URL("assets/ajaxs/ChatBot.php"); ?>",
            type: "POST",
            data: {
                type: "TaoRoom"
            },
            success: function(response) {
                try {
                    var json = typeof response === 'string' ? JSON.parse(response) : response;
                    if (json.status === "success") {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(json.message || "Tạo room thành công!", "Thành công!");
                        }
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(json.message || "Có lỗi xảy ra", "Lỗi!");
                        }
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                    if (typeof toastr !== 'undefined') {
                        toastr.error("Lỗi xử lý phản hồi", "Lỗi!");
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                if (typeof toastr !== 'undefined') {
                    toastr.error("Không thể kết nối đến server", "Lỗi!");
                }
            }
        });
    }

    // Gửi tin nhắn
    function sendMessage() {
        var content = $('#contentInput').val().trim();
        if (!content) {
            if (typeof toastr !== 'undefined') {
                toastr.warning("Vui lòng nhập câu hỏi", "Thông báo!");
            }
            return;
        }

        var room = "<?= $checkChatBotRoom > 0 ? $checkChatBotRoom['MaRoom'] : '' ?>";
        if (!room) {
            if (typeof toastr !== 'undefined') {
                toastr.warning("Vui lòng tạo room trước", "Thông báo!");
            }
            return;
        }

        $('#btnSend').prop('disabled', true).html('Đang gửi...');
        $('#contentInput').prop('disabled', true);

        $.ajax({
            url: "<?= BASE_URL("assets/ajaxs/ChatBot.php"); ?>",
            type: "POST",
            data: {
                type: "SendMessage",
                content: content,
                room: room
            },
            success: function(response) {
                try {
                    var json = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (json.status === "error") {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(json.message || "Có lỗi xảy ra", "Lỗi!");
                        }
                        $('#btnSend').prop('disabled', false).html('Gửi');
                        $('#contentInput').prop('disabled', false);
                        return;
                    }

                    if (json.status === "success") {
                        // Hiển thị tin nhắn user
                        appendMessage("user", "<?= $getTaiKhoan['AnhDaiDien'] ?>", "<?= $getTaiKhoan['TenHienThi'] ?>", content, json.data.createdAt);
                        $('#contentInput').val('');

                        // Bắt đầu nhận phản hồi từ chatbot
                        startChatBotStream(room);
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                    if (typeof toastr !== 'undefined') {
                        toastr.error("Lỗi xử lý phản hồi", "Lỗi!");
                    }
                    $('#btnSend').prop('disabled', false).html('Gửi');
                    $('#contentInput').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                if (typeof toastr !== 'undefined') {
                    toastr.error("Không thể kết nối đến server", "Lỗi!");
                }
                $('#btnSend').prop('disabled', false).html('Gửi');
                $('#contentInput').prop('disabled', false);
            }
        });
    }

    // Bắt đầu stream từ chatbot
    function startChatBotStream(room) {
        var timeResponse = new Date().toISOString();
        var uuid = 'msg-' + Date.now();
        
        appendMessage("assistant", "<?= BASE_URL("assets/img/logo.png") ?>", "5Fs Group", "", timeResponse, uuid);
        var div = document.getElementById(uuid);
        var txtDatabase = '';

        var eventSource = new EventSource("<?= BASE_URL("Page/ChatBotStream") ?>?chat_room_id=" + room);

        eventSource.onmessage = function(e) {
            if (e.data === "[DONE]") {
                eventSource.close();
                updateChatBotResponse(txtDatabase, timeResponse, room);
                $('#btnSend').prop('disabled', false).html('Gửi');
                $('#contentInput').prop('disabled', false);
                return;
            }

            try {
                var parsed = JSON.parse(e.data);
                if (parsed.error) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(parsed.error, "Lỗi ChatBot!");
                    }
                    eventSource.close();
                    $('#btnSend').prop('disabled', false).html('Gửi');
                    $('#contentInput').prop('disabled', false);
                    return;
                }

                if (parsed.choices && parsed.choices[0] && parsed.choices[0].delta && parsed.choices[0].delta.content) {
                    var txt = parsed.choices[0].delta.content;
                    txtDatabase += txt;
                    if (div) {
                        div.innerHTML += txt.replace(/(?:\r\n|\r|\n)/g, '<br>');
                        scrollToBottom();
                    }
                }
            } catch (parseError) {
                // Nếu không phải JSON, xử lý như plain text
                if (e.data && e.data !== "[DONE]") {
                    txtDatabase += e.data;
                    if (div) {
                        div.innerHTML += e.data.replace(/(?:\r\n|\r|\n)/g, '<br>');
                        scrollToBottom();
                    }
                }
            }
        };

        eventSource.onerror = function(e) {
            eventSource.close();
            if (typeof toastr !== 'undefined') {
                toastr.error("Chatbot đang gặp vấn đề, vui lòng thử lại", "Lỗi!");
            }
            $('#btnSend').prop('disabled', false).html('Gửi');
            $('#contentInput').prop('disabled', false);
        };
    }

    // Cập nhật phản hồi chatbot vào database
    function updateChatBotResponse(content, timeResponse, room) {
        $.ajax({
            url: "<?= BASE_URL("assets/ajaxs/ChatBot.php"); ?>",
            type: "POST",
            data: {
                type: "UpdateChatBotResponse",
                content: content,
                thoiGian: timeResponse,
                room: room
            },
            success: function(response) {
                // Không cần xử lý gì
            },
            error: function(xhr, status, error) {
                console.error('Update response error:', error);
            }
        });
    }

    // Xóa tin nhắn
    function deleteMessages() {
        if (!confirm("Bạn có chắc muốn xóa tất cả tin nhắn?")) {
            return;
        }

        var room = "<?= $checkChatBotRoom > 0 ? $checkChatBotRoom['MaRoom'] : '' ?>";
        if (!room) {
            return;
        }

        $.ajax({
            url: "<?= BASE_URL("assets/ajaxs/ChatBot.php"); ?>",
            type: "POST",
            data: {
                type: "DeleteMessages",
                room: room
            },
            success: function(response) {
                try {
                    var json = typeof response === 'string' ? JSON.parse(response) : response;
                    if (json.status === "success") {
                        $('#chat_content').empty();
                        if (typeof toastr !== 'undefined') {
                            toastr.success(json.message || "Xóa thành công!", "Thành công!");
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(json.message || "Có lỗi xảy ra", "Lỗi!");
                        }
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                if (typeof toastr !== 'undefined') {
                    toastr.error("Không thể kết nối đến server", "Lỗi!");
                }
            }
        });
    }

    // Thêm tin nhắn vào UI
    function appendMessage(role, avatar, name, content, time, uuid) {
        var html = '<div class="chatbot-message chatbot-message--' + role + '" id="' + (uuid || '') + '">';
        html += '<img src="' + avatar + '" alt="" class="chatbot-message-avatar">';
        html += '<div class="chatbot-message-content">';
        html += '<div class="chatbot-message-name">' + name + '</div>';
        html += '<div class="chatbot-message-text">' + (content || '') + '</div>';
        html += '<div class="chatbot-message-time">' + time + '</div>';
        html += '</div></div>';
        $('#chat_content').append(html);
        scrollToBottom();
    }

    // Cuộn xuống cuối
    function scrollToBottom() {
        var chatContent = document.getElementById('chat_content');
        if (chatContent) {
            chatContent.scrollTop = chatContent.scrollHeight;
        }
    }

    // Tải lịch sử tin nhắn
    function getHistoryMessages() {
        var room = "<?= $checkChatBotRoom > 0 ? $checkChatBotRoom['MaRoom'] : '' ?>";
        if (!room) {
            return;
        }

        $.ajax({
            url: "<?= BASE_URL("assets/ajaxs/ChatBot.php"); ?>",
            type: "POST",
            data: {
                type: "GetHistoryMessages",
                room: room
            },
            success: function(response) {
                try {
                    var json = typeof response === 'string' ? JSON.parse(response) : response;
                    if (json.status === "success" && json.data && json.data.length > 0) {
                        $('#chat_content').empty();
                        json.data.forEach(function(msg) {
                            var avatar = msg.Role === 'user' ? "<?= $getTaiKhoan['AnhDaiDien'] ?>" : "<?= BASE_URL("assets/img/logo.png") ?>";
                            var name = msg.Role === 'user' ? "<?= $getTaiKhoan['TenHienThi'] ?>" : "5Fs Group";
                            appendMessage(msg.Role, avatar, name, msg.NoiDung, msg.ThoiGian);
                        });
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }

    // Gửi khi nhấn Enter
    $(document).ready(function() {
        $('#contentInput').on('keypress', function(e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Tải lịch sử khi trang load
        <?php if ($checkChatBotRoom > 0): ?>
            getHistoryMessages();
        <?php endif; ?>
    });
</script>

<?php require_once(__DIR__ . "/../../public/client/footer.php"); ?>

