// register.js - Register AJAX logic
document.getElementById('registerForm').onsubmit = async function(e) {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    const res = await fetch('api/register.php', {
        method: 'POST',
        body: data
    });
    const msg = await res.text();
    document.getElementById('registerMsg').innerText = msg;
    if (msg.includes('success')) location.href = 'login.php';
};
//test reyerggrgre