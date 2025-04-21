<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>2محادثة بسيطة</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial; direction: rtl; padding: 20px; background: #f9f9f9; }
        .chat-box { border: 1px solid #ccc; padding: 15px; background: white; height: 400px; overflow-y: scroll; margin-bottom: 10px; }
        .message { margin-bottom: 8px; }
        .me { color: blue; }
        .other { color: green; }
    </style>
</head>
<body>

<h2>محادثة مع المستخدم رقم: <span id="receiverId">1</span></h2>

<div class="chat-box" id="chatBox"></div>

<input type="text" id="messageInput" placeholder="اكتب رسالة..." style="width: 80%;">
<button onclick="sendMessage()">إرسال</button>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.1/echo.iife.js"></script>
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script>
    const token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2F1dGgvbG9naW4iLCJpYXQiOjE3NDUyMzU5MDYsImV4cCI6MTc0NTIzOTUwNiwibmJmIjoxNzQ1MjM1OTA2LCJqdGkiOiJpMzZJQ2NrVEhybXFqR2h3Iiwic3ViIjoiMiIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.f7lThhdVJn55TNe_xOmJuJ3W7nAXkxz2g-yplgQABJs'; // حط التوكن الحقيقي
    const receiverId = 1;
    const userId = 2;
    const chatId = 1;

    const chatBox = document.getElementById('chatBox');
    const input = document.getElementById('messageInput');

    // تحميل الرسائل
    axios.get('/api/messages/1', {
        headers: {
            Authorization: `Bearer ${token}`
        }
    }).then(response => {
        const messages = response.data;
        messages.forEach(msg => {
            const div = document.createElement('div');
            div.className = 'message ' + (msg.sender_id === userId ? 'me' : 'other');
            div.textContent = msg.content;
            chatBox.appendChild(div);
        });
        chatBox.scrollTop = chatBox.scrollHeight;
    });

    // إرسال رسالة
    function sendMessage() {
        const content = input.value.trim();
        if (!content) return;

        axios.post('/api/messages', {
            receiver_id: receiverId,
            content: content
        }, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        }).then(response => {
            // 💥 الرسالة تنضاف يدوياً للمحادثة فوراً
            const div = document.createElement('div');
            div.className = 'message me';
            div.textContent = content;
            chatBox.appendChild(div);
            chatBox.scrollTop = chatBox.scrollHeight;

            // 💥 تمسح حقل الإدخال
            input.value = '';
        });
    }

    // استقبال الرسائل فورياً من الطرف الآخر
    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: 'local',
        wsHost: window.location.hostname,
        wsPort: 6001,
        forceTLS: false,
        disableStats: true,
        enabledTransports: ['ws'],
        auth: {
            headers: {
                Authorization: `Bearer ${token}`
            }
        }
    });

    Echo.private(`chat.${userId}`)
    .listen('MessageSent', (e) => {
        if (e.sender_id === userId) return;

        const div = document.createElement('div');
        div.className = 'message other';
        div.textContent = e.content;
        chatBox.appendChild(div);
        chatBox.scrollTop = chatBox.scrollHeight;
    });

</script>
