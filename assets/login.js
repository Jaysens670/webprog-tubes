document.getElementById('loginForm').onsubmit = async function(e) {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    const res = await fetch('api/login.php', {
        method: 'POST',
        body: data,
        headers: { 'Accept': 'application/xml' }
    });
    const xmlText = await res.text();
    const parser = new DOMParser();
    const xml = parser.parseFromString(xmlText, 'application/xml');
    const status = xml.getElementsByTagName('status')[0]?.textContent;
    const message = xml.getElementsByTagName('message')[0]?.textContent || '';
    document.getElementById('loginMsg').innerText = message;
    if (status === 'success') location.href = 'home.php';
};
