// login.js - Login AJAX logic
document.getElementById('loginForm').onsubmit = async function(e) {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    const res = await fetch('api/login.php', {
        method: 'POST',
        body: data
    });
    const msg = await res.text();
    document.getElementById('loginMsg').innerText = msg;
    if (msg.includes('success')) location.href = 'index.php';
};
