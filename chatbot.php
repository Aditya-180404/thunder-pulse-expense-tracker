<?php if(!isset($_SESSION)) session_start(); ?>

<!-- Floating Chatbot Icon -->
<div id="chatbot-icon">🤖</div>

<!-- Chatbot Popup -->
<div id="chatbot-box">
    <div class="chat-header">
        <span>AI Expense Assistant</span>
        <button onclick="toggleChat()">✕</button>
    </div>

    <div id="chat-body"></div>

    <div class="chat-input">
        <input type="text" id="chat-text" placeholder="Ask about spending, savings, tips..." />
        <button onclick="sendMessage()">➤</button>
    </div>
</div>

<style>
#chatbot-icon{
    position:fixed;
    right:25px;
    bottom:25px;
    width:60px;
    height:60px;
    background:linear-gradient(135deg,#6C63FF,#8577FF);
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:26px;
    color:#fff;
    cursor:pointer;
    box-shadow:0 10px 30px rgba(0,0,0,.3);
    z-index:9999;
}

#chatbot-box{
    position:fixed;
    right:25px;
    bottom:100px;
    width:330px;
    height:420px;
    background:rgba(255,255,255,.25);
    backdrop-filter:blur(16px);
    border-radius:18px;
    box-shadow:0 15px 40px rgba(0,0,0,.25);
    display:none;
    flex-direction:column;
    z-index:9999;
}

.chat-header{
    padding:14px;
    display:flex;
    justify-content:space-between;
    font-weight:600;
    background:rgba(255,255,255,.4);
    border-radius:18px 18px 0 0;
}

#chat-body{
    flex:1;
    padding:12px;
    overflow-y:auto;
    font-size:.9rem;
}

.user-msg{ text-align:right; margin:8px 0; }
.ai-msg{ text-align:left; margin:8px 0; color:#222; }

.chat-input{
    display:flex;
    gap:8px;
    padding:12px;
}

.chat-input input{
    flex:1;
    padding:12px 16px;
    border-radius:30px;
    border:none;
    outline:none;
    background:rgba(255,255,255,.8);
}

.chat-input button{
    width:44px;
    height:44px;
    border:none;
    border-radius:50%;
    background:#6C63FF;
    color:#fff;
    cursor:pointer;
}
</style>

<script>
const chatBox = document.getElementById("chatbot-box");

document.getElementById("chatbot-icon").onclick = () => {
    chatBox.style.display = chatBox.style.display === "flex" ? "none" : "flex";
};

function toggleChat(){
    chatBox.style.display = "none";
}

function sendMessage(){
    const input = document.getElementById("chat-text");
    const msg = input.value.trim();
    if(!msg) return;

    const body = document.getElementById("chat-body");
    body.innerHTML += `<div class="user-msg"><b>You:</b> ${msg}</div>`;
    input.value="";
    body.scrollTop = body.scrollHeight;

    fetch("chatbot_response.php",{
        method:"POST",
        headers:{ "Content-Type":"application/json" },
        body:JSON.stringify({ message: msg })
    })
    .then(res=>res.json())
    .then(data=>{
        body.innerHTML += `<div class="ai-msg"><b>AI:</b> ${data.reply}</div>`;
        body.scrollTop = body.scrollHeight;
    });
}
</script>