<?php
session_start();
require_once '../config/database.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$sender_id = $_SESSION['user_id'];

// Cari admin pertama di database
$adminStmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
$admin = $adminStmt->fetch();
$admin_id = $admin['id'] ?? 1;

// ==== ACTION DARI FRONTEND ====
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Ambil pesan antara user dan admin
    if ($action === 'get_messages') {
        $stmt = $pdo->prepare("
            SELECT m.*, u.username as sender_name
            FROM messages m
            LEFT JOIN users u ON m.sender_id = u.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?)
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.timestamp ASC
        ");
        $stmt->execute([$sender_id, $admin_id, $admin_id, $sender_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Mark admin messages as read
        $updateStmt = $pdo->prepare("
            UPDATE messages SET is_read = 1 
            WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
        ");
        $updateStmt->execute([$admin_id, $sender_id]);

        header('Content-Type: application/json');
        echo json_encode($messages);
        exit;
    }

    // Simpan pesan baru
    if ($action === 'send_message' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        $message = trim($data['message'] ?? '');

        header('Content-Type: application/json');

        if (!empty($message)) {
            $stmt = $pdo->prepare("
                INSERT INTO messages (sender_id, receiver_id, message, timestamp, is_read)
                VALUES (?, ?, ?, NOW(), 0)
            ");
            $stmt->execute([$sender_id, $admin_id, $message]);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Pesan kosong']);
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
    <title>Chat dengan Admin - Wisata Sulsel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920') center/cover fixed;
            z-index: -1;
            opacity: 0.2;
        }

        .chat-container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 30px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 600px;
            animation: slideUp 0.5s;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chat-header {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 25px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .admin-avatar {
            width: 55px;
            height: 55px;
            background: white;
            color: #11998e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .chat-header-info h4 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .chat-header-info p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.95;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: linear-gradient(to bottom, #f8f9fa, #ffffff);
        }

        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }

        .message {
            margin: 15px 0;
            display: flex;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.user {
            justify-content: flex-end;
        }

        .message.admin {
            justify-content: flex-start;
        }

        .message-bubble {
            max-width: 75%;
            padding: 12px 18px;
            border-radius: 18px;
            word-wrap: break-word;
            position: relative;
        }

        .message.user .message-bubble {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            border-bottom-right-radius: 4px;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
        }

        .message.admin .message-bubble {
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
        }

        .message-time {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 5px;
        }

        .message.user .message-time {
            text-align: right;
        }

        .typing-indicator {
            display: none;
            padding: 10px;
            color: #999;
            font-size: 0.9rem;
            font-style: italic;
        }

        .typing-indicator.show {
            display: block;
        }

        .chat-input {
            display: flex;
            padding: 20px;
            background: white;
            border-top: 2px solid #e9ecef;
            gap: 12px;
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
            border-color: #11998e;
            box-shadow: 0 0 0 3px rgba(17, 153, 142, 0.1);
        }

        .chat-input button {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-input button:hover {
            transform: scale(1.1) rotate(15deg);
            box-shadow: 0 8px 20px rgba(17, 153, 142, 0.4);
        }

        .chat-input button:active {
            transform: scale(0.95);
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        .empty-state p {
            font-size: 1rem;
            margin: 0;
        }

        .floating-icon {
            position: fixed;
            font-size: 2.5rem;
            opacity: 0.08;
            color: white;
            animation: float 8s ease-in-out infinite;
            z-index: 0;
        }

        .icon-1 { top: 10%; left: 10%; animation-delay: 0s; }
        .icon-2 { top: 20%; right: 15%; animation-delay: 2s; }
        .icon-3 { bottom: 15%; left: 12%; animation-delay: 4s; }
        .icon-4 { bottom: 25%; right: 10%; animation-delay: 3s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-25px) rotate(5deg); }
        }

        @media (max-width: 576px) {
            .chat-container {
                height: 100vh;
                max-height: 100vh;
                border-radius: 0;
            }

            .message-bubble {
                max-width: 85%;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Icons -->
    <i class="bi bi-chat-dots floating-icon icon-1"></i>
    <i class="bi bi-envelope-heart floating-icon icon-2"></i>
    <i class="bi bi-chat-heart floating-icon icon-3"></i>
    <i class="bi bi-chat-square-text floating-icon icon-4"></i>

    <div class="chat-container">
        <!-- Header -->
        <div class="chat-header">
            <a href="dashboard.php" class="back-btn">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="admin-avatar">
                <i class="bi bi-headset"></i>
            </div>
            <div class="chat-header-info">
                <h4>Admin Support</h4>
                <p><i class="bi bi-circle-fill text-white"></i> Siap Membantu</p>
            </div>
        </div>

        <!-- Messages -->
        <div class="chat-messages" id="chatMessages">
            <div class="empty-state">
                <i class="bi bi-chat-left-dots"></i>
                <p>Mulai percakapan dengan admin</p>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div class="typing-indicator" id="typingIndicator">
            <i class="bi bi-three-dots"></i> Admin sedang mengetik...
        </div>

        <!-- Input -->
        <div class="chat-input">
            <input type="text" 
                   id="messageInput" 
                   placeholder="Ketik pesan Anda..."
                   autocomplete="off">
            <button id="sendBtn">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
    </div>

    <script>
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const typingIndicator = document.getElementById('typingIndicator');
        const adminId = <?= $admin_id ?>;
        const userId = <?= $sender_id ?>;

        let lastMessageCount = 0;

        // Send message
        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            try {
                const response = await fetch('chat.php?action=send_message', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message })
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

        // Load messages
        async function loadMessages() {
            try {
                const response = await fetch('chat.php?action=get_messages');
                const messages = await response.json();

                // Show typing indicator if new message from admin
                if (messages.length > lastMessageCount && messages[messages.length - 1].sender_id != userId) {
                    typingIndicator.classList.add('show');
                    setTimeout(() => {
                        typingIndicator.classList.remove('show');
                    }, 1000);
                }

                lastMessageCount = messages.length;

                if (messages.length === 0) {
                    chatMessages.innerHTML = `
                        <div class="empty-state">
                            <i class="bi bi-chat-left-dots"></i>
                            <p>Mulai percakapan dengan admin</p>
                        </div>
                    `;
                    return;
                }

                chatMessages.innerHTML = '';

                messages.forEach(msg => {
                    const isUser = msg.sender_id == userId;
                    const div = document.createElement('div');
                    div.className = `message ${isUser ? 'user' : 'admin'}`;
                    
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

        // Event listeners
        sendBtn.addEventListener('click', sendMessage);
        
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Auto refresh every 2 seconds
        setInterval(loadMessages, 2000);

        // Initial load
        loadMessages();

        // Focus on input
        messageInput.focus();
    </script>
</body>
</html>