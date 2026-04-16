document.addEventListener('DOMContentLoaded', async function () {

    // 綁定登出按鈕
    const btnLogout = document.getElementById('btn-logout');
    if (btnLogout) btnLogout.addEventListener('click', handleLogout);

    // 檢查登入狀態
    const form = new FormData();
    form.append('action', 'check_member_login');
    form.append('nonce', MemberAuth.nonce);

    try {
        const res    = await fetch(MemberAuth.ajaxurl, { method: 'POST', body: form, credentials: 'include' });
        const result = await res.json();
        const data   = result.data;

        if (data.logged_in) {
            showLoggedIn(data);
        } else {
            showLoginForm();
        }
    } catch (e) {
        showLoginForm();
    }
});

function showLoggedIn(data) {
    document.getElementById('ui-login-form').style.display = 'none';
    document.getElementById('ui-logged-in').style.display  = 'block';
    // document.getElementById('user-name').textContent = data.name || data.email;
}

function showLoginForm() {
    document.getElementById('ui-login-form').style.display = 'block';
    document.getElementById('ui-logged-in').style.display  = 'none';
}

async function handleLogout() {
    const form = new FormData();
    form.append('action', 'member_logout');
    form.append('nonce', MemberAuth.nonce);

    try {
        const res    = await fetch(MemberAuth.ajaxurl, { method: 'POST', body: form, credentials: 'include' });
        const result = await res.json();

        if (result.data.logged_out) showLoginForm();
    } catch (e) {
        console.error('Logout failed:', e);
    }
}