<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotAdmin();

$admin_id = $_SESSION['user_id'];

// API Endpoints
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Get list of users who have chatted
    if ($action === 'get_users') {
        $stmt = $pdo->query("
            SELECT DISTINCT u.id, u.username, 
                   (SELECT COUNT(*) FROM messages 
                    WHERE sender_id = u.id AND receiver_id = $admin_id AND is_read = 0) as unread_count
            FROM users u
            JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
            WHERE u.role = 'user'
            ORDER BY u.username ASC
        ");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($users);
        exit;
    }

    // Get messages with specific user
    if ($action === 'get_messages' && isset($_GET['user_id'])) {
        $user_id = intval($_GET['user_id']);

        // Mark messages as read
        $updateStmt = $pdo->prepare("
            UPDATE messages SET is_read = 1 
            WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
        ");
        $updateStmt->execute([$user_id, $admin_id]);

        // Get all messages
        $stmt = $pdo->prepare("
            SELECT m.*, u.username as sender_name
            FROM messages m
            LEFT JOIN users u ON m.sender_id = u.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?)
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.timestamp ASC
        ");
        $stmt->execute([$admin_id, $user_id, $user_id, $admin_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($messages);
        exit;
    }

    // Send message to user
    if ($action === 'send_message' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        $receiver_id = intval($data['receiver_id'] ?? 0);
        $message = trim($data['message'] ?? '');

        header('Content-Type: application/json');

        if ($receiver_id > 0 && !empty($message)) {
            $stmt = $pdo->prepare("
                INSERT INTO messages (sender_id, receiver_id, message, timestamp, is_read)
                VALUES (?, ?, ?, NOW(), 0)
            ");
            $stmt->execute([$admin_id, $receiver_id, $message]);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Pesan atau penerima kosong']);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Admin - Wisata Sulsel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }

        .chat-wrapper {
            display: flex;
            height: 100vh;
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.3);
        }

        /* Sidebar */
        .sidebar {
            width: 320px;
            background: linear-gradient(180deg, #2a6ef5 0%, #1d5ee5 100%);
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            background: rgba(0, 0, 0, 0.2);
            color: white;
            padding: 25px 20px;
            text-align: center;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .sidebar-header p {
            margin: 5px 0 0 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .user-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px 0;
        }

        .user-list::-webkit-scrollbar {
            width: 6px;
        }

        .user-list::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .user-item {
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 4px solid transparent;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-item:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: white;
        }

        .user-item.active {
            background: rgba(255, 255, 255, 0.2);
            border-left-color: #fbbf24;
            font-weight: 600;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.9);
            color: #2a6ef5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .user-status {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .unread-badge {
            background: #fbbf24;
            color: #333;
            border-radius: 12px;
            padding: 3px 10px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        /* Chat Container */
        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f8f9fa;
        }

        .chat-header {
            background: white;
            padding: 20px 25px;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .chat-header-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #2a6ef5, #1d5ee5);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.3rem;
        }

        .chat-header-info h4 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
        }

        .chat-header-info p {
            margin: 0;
            font-size: 0.9rem;
            color: #11998e;
        }

        .chat-messages {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
            background: linear-gradient(to bottom, #f8f9fa, #ffffff);
        }

        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }

        .message {
            margin: 12px 0;
            display: flex;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.admin {
            justify-content: flex-end;
        }

        .message.user {
            justify-content: flex-start;
        }

        .message-bubble {
            max-width: 70%;
            padding: 12px 18px;
            border-radius: 18px;
            word-wrap: break-word;
            position: relative;
        }

        .message.admin .message-bubble {
            background: linear-gradient(135deg, #2a6ef5, #1d5ee5);
            color: white;
            border-bottom-right-radius: 4px;
            box-shadow: 0 4px 15px rgba(42, 110, 245, 0.3);
        }

        .message.user .message-bubble {
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
        }

        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 5px;
        }

        .message.admin .message-time {
            text-align: right;
        }

        .chat-input {
            display: flex;
            padding: 20px 25px;
            background: white;
            border-top: 2px solid #e9ecef;
            gap: 15px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        }

        .chat-input input {
            flex: 1;
            padding: 14px 20px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            outline: none;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .chat-input input:focus {
            border-color: #2a6ef5;
            box-shadow: 0 0 0 3px rgba(42, 110, 245, 0.1);
        }

        .chat-input button {
            background: linear-gradient(135deg, #2a6ef5, #1d5ee5);
            color: white;
            border: none;
            padding: 14px 35px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chat-input button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(42, 110, 245, 0.4);
        }

        .chat-input button:disabled {
            background: #cbd5e0;
            cursor: not-allowed;
            transform: none;
        }

        .no-chat {
            text-align: center;
            margin-top: 100px;
        }

        .no-chat i {
            font-size: 5rem;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .no-chat h4 {
            color: #718096;
            font-weight: 600;
        }

        .no-chat p {
            color: #a0aec0;
        }

        .back-button {
            background: white;
            color: #2a6ef5;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            margin: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateX(-5px);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                max-width: 280px;
                position: absolute;
                left: -280px;
                transition: left 0.3s;
                z-index: 100;
            }

            .sidebar.show {
                left: 0;
            }

            .message-bubble {
                max-width: 85%;
            }
        }
    </style>
</head>
<body>
    <div class="chat-wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <a href="index.php" class="back-button text-decoration-none">
                <i class="bi bi-arrow-left-circle"></i> Dashboard
            </a>
            <div class="sidebar-header">
                <h3><i class="bi bi-chat-dots-fill"></i> Admin Chat</h3>
                <p>Kelola percakapan dengan pengguna</p>
            </div>
            <div class="user-list" id="userList">
                <div class="text-center text-white py-4">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                    <p class="mt-2 mb-0">Memuat...</p>
                </div>
            </div>
        </div>

        <!-- Chat Container -->
        <div class="chat-container">
            <div class="chat-header" id="chatHeader" style="display: none;">
                <div class="chat-header-avatar" id="chatAvatar">U</div>
                <div class="chat-header-info">
                    <h4 id="chatUsername">Username</h4>
                    <p><i class="bi bi-circle-fill text-success"></i> Online</p>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                <div class="no-chat">
                    <i class="bi bi-chat-left-text"></i>
                    <h4>Pilih Pengguna</h4>
                    <p>Pilih pengguna di sebelah kiri untuk memulai percakapan</p>
                </div>
            </div>

            <div class="chat-input">
                <input type="text" id="messageInput" placeholder="Ketik pesan..." disabled>
                <button id="sendBtn" disabled>
                    <i class="bi bi-send-fill"></i> Kirim
                </button>
            </div>
        </div>
    </div>

    <script>
        const userList = document.getElementById('userList');
        const chatMessages = document.getElementById('chatMessages');
        const chatHeader = document.getElementById('chatHeader');
        const chatUsername = document.getElementById('chatUsername');
        const chatAvatar = document.getElementById('chatAvatar');
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        
        let selectedUserId = null;
        let selectedUsername = '';
        const adminId = <?= $_SESSION['user_id'] ?>;

        // Load user list
        async function loadUsers() {
            try {
                const response = await fetch('chat.php?action=get_users');
                const users = await response.json();

                userList.innerHTML = '';

                if (users.length === 0) {
                    userList.innerHTML = '<p class="text-center text-white py-4">Belum ada percakapan</p>';
                    return;
                }

                users.forEach(user => {
                    const div = document.createElement('div');
                    div.className = 'user-item';
                    div.dataset.userId = user.id;
                    div.dataset.username = user.username;
                    
                    div.innerHTML = `
                        <div class="user-avatar">${user.username.charAt(0).toUpperCase()}</div>
                        <div class="user-info">
                            <div class="user-name">${user.username}</div>
                            <div class="user-status">
                                <i class="bi bi-circle-fill" style="font-size: 0.6rem;"></i> Online
                            </div>
                        </div>
                        ${user.unread_count > 0 ? `<span class="unread-badge">${user.unread_count}</span>` : ''}
                    `;
                    
                    div.addEventListener('click', () => selectUser(user.id, user.username));
                    userList.appendChild(div);
                });
            } catch (err) {
                console.error('Error loading users:', err);
                userList.innerHTML = '<p class="text-center text-white py-4">Error memuat data</p>';
            }
        }

        // Select user
        function selectUser(userId, username) {
            selectedUserId = userId;
            selectedUsername = username;

            // Update active state
            document.querySelectorAll('.user-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelector(`[data-user-id="${userId}"]`).classList.add('active');

            // Update header
            chatHeader.style.display = 'flex';
            chatUsername.textContent = username;
            chatAvatar.textContent = username.charAt(0).toUpperCase();

            // Enable input
            messageInput.disabled = false;
            sendBtn.disabled = false;

            // Load messages
            loadMessages();
        }

        // Load messages
        async function loadMessages() {
            if (!selectedUserId) return;

            try {
                const response = await fetch(`chat.php?action=get_messages&user_id=${selectedUserId}`);
                const messages = await response.json();

                chatMessages.innerHTML = '';

                if (messages.length === 0) {
                    chatMessages.innerHTML = '<div class="no-chat"><p>Belum ada pesan</p></div>';
                    return;
                }

                messages.forEach(msg => {
                    const isAdmin = msg.sender_id == adminId;
                    const div = document.createElement('div');
                    div.className = `message ${isAdmin ? 'admin' : 'user'}`;
                    
                    const time = new Date(msg.timestamp).toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    div.innerHTML = `
                        <div class="message-bubble">
                            ${msg.message}
                            <div class="message-time">${time}</div>
                        </div>
                    `;
                    
                    chatMessages.appendChild(div);
                });

                chatMessages.scrollTop = chatMessages.scrollHeight;
            } catch (err) {
                console.error('Error loading messages:', err);
            }
        }

        // Send message
        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message || !selectedUserId) return;

            try {
                const response = await fetch('chat.php?action=send_message', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        receiver_id: selectedUserId,
                        message: message
                    })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    messageInput.value = '';
                    await loadMessages();
                } else {
                    alert('Gagal mengirim pesan: ' + (data.message || 'Error'));
                }
            } catch (err) {
                console.error('Error sending message:', err);
                alert('Gagal mengirim pesan');
            }
        }

        // Event listeners
        sendBtn.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

        // Auto refresh
        setInterval(() => {
            loadUsers();
            if (selectedUserId) {
                loadMessages();
            }
        }, 3000);

        // Initial load
        loadUsers();
    </script>
</body>
</html>