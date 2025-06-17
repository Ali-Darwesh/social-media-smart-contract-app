<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>محادثة بسيطة1</title>
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

<h2>محادثة مع المستخدم رقم: <span id="receiverId">2</span></h2>

<div class="chat-box" id="chatBox"></div>

<input type="text" id="messageInput" placeholder="اكتب رسالة..." style="width: 80%;">
<button onclick="sendMessage()">إرسال</button>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.1/echo.iife.js"></script>
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script>
    const token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2F1dGgvbG9naW4iLCJpYXQiOjE3NDUyMzU4MjIsImV4cCI6MTc0NTIzOTQyMiwibmJmIjoxNzQ1MjM1ODIyLCJqdGkiOiJsVHF1cW14SXRPWGpMMEg3Iiwic3ViIjoiMSIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.AteGdCMzmHPrA2c8n2yY-l9wfyh871Xw9fE50yMX96s'; // حط التوكن الحقيقي
    const receiverId = 2;
    const userId = 1;
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

        // أضفها للواجهة
        const div = document.createElement('div');
        div.className = 'message other';
        div.textContent = e.content;
        chatBox.appendChild(div);
        chatBox.scrollTop = chatBox.scrollHeight;

        //  إشعار مثل واتساب
        showNotification(e.sender_name, e.content);
    });
    function showNotification(senderName, messageContent) {
    const preview = messageContent.length > 30 ? messageContent.slice(0, 30) + "..." : messageContent;

    const notification = document.createElement('div');
    notification.style.position = 'fixed';
    notification.style.bottom = '20px';
    notification.style.right = '20px';
    notification.style.background = '#333';
    notification.style.color = '#fff';
    notification.style.padding = '10px 15px';
    notification.style.borderRadius = '10px';
    notification.style.boxShadow = '0 0 10px rgba(0,0,0,0.3)';
    notification.innerHTML = `<strong>${senderName}</strong><br>${preview}`;

    document.body.appendChild(notification);
  //  const audio = new Audio('/sounds/notify.mp3'); // حط ملف الصوت بمجلد public/sounds
  //  audio.play();
    // يشيل الإشعار بعد 5 ثواني
    setTimeout(() => {
        notification.remove();
    }, 5000);
}


</script>
