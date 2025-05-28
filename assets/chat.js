document.addEventListener("DOMContentLoaded", async () => {
    const channelSelect = document.getElementById("channelSelect");
    const chatBox = document.getElementById("chatBox");
    const chatForm = document.getElementById("chatForm");
    const messageInput = document.getElementById("messageInput");
    const imageInput = document.getElementById("imageInput");

    async function loadChannels() {
        try {
            const res = await fetch("../api/get_channels.php");
            const channels = await res.json();

            channels.forEach(c => {
                const opt = document.createElement("option");
                opt.value = c.channel_id;
                opt.textContent = c.name;
                channelSelect.appendChild(opt);
            });

            if (channels.length > 0) {
                channelSelect.value = channels[0].channel_id;
                loadMessages();
            }
        } catch (err) {
            console.error("Gagal memuat channel:", err);
        }
    }

    async function loadMessages() {
        const channelId = channelSelect.value;
        try {
            const res = await fetch(`../api/get_messages.php?channel_id=${channelId}`);
            const messages = await res.json();

            chatBox.innerHTML = '';
            messages.forEach(msg => {
                const profilePic = msg.pic_profile ? `../pages/uploads/${msg.pic_profile}` : '../assets/default-profile.png';

                const div = document.createElement("div");
                div.className = "chat-message";
                div.innerHTML = `
                    <div class="chat-user">
                        <img src="${profilePic}" class="chat-avatar" alt="pfp">
                        <div>
                            <strong>${msg.username}</strong><br>
                            ${msg.content}
                            ${msg.image_url ? `<br><img src="../${msg.image_url}" width="150">` : ''}
                        </div>
                    </div>
                `;
                chatBox.appendChild(div);
            });

            chatBox.scrollTop = chatBox.scrollHeight;
        } catch (err) {
            console.error("Gagal memuat pesan:", err);
        }
    }

    chatForm.onsubmit = async e => {
        e.preventDefault();

        const content = messageInput.value.trim();
        if (!content && !imageInput.files.length) {
            alert("Isi pesan atau pilih gambar terlebih dahulu.");
            return;
        }

        const data = new FormData();
        data.append("channel_id", channelSelect.value);
        data.append("content", content);
        if (imageInput.files[0]) {
            data.append("image", imageInput.files[0]);
        }

        try {
            const res = await fetch("../api/send_message.php", {
                method: "POST",
                body: data
            });

            const text = await res.text();
            if (!res.ok) {
                alert("Gagal mengirim pesan: " + text);
                return;
            }

            messageInput.value = '';
            imageInput.value = '';
            loadMessages();
        } catch (err) {
            console.error("Gagal mengirim:", err);
            alert("Terjadi kesalahan saat mengirim pesan.");
        }
    };

    channelSelect.addEventListener("change", loadMessages);
    setInterval(loadMessages, 5000);
    loadChannels();
});
