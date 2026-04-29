document.addEventListener('DOMContentLoaded', async function () {

    // Kiểm tra tất cả element tồn tại trước
    const btnLogout = document.getElementById('btn-logout');
    const uiLoginForm = document.getElementById('ui-login-form');
    const uiLoggedIn = document.getElementById('ui-logged-in');

    // Nếu thiếu element, dừng lại
    if (!uiLoginForm || !uiLoggedIn) {
        console.error('❌ Thiếu HTML elements: ui-login-form hoặc ui-logged-in');
        return;
    }

    if (btnLogout) {
        btnLogout.addEventListener('click', handleLogout);
    }

    // Kiểm tra đăng nhập
    const form = new FormData();
    form.append('action', 'check_member_login');
    form.append('nonce', MemberAuth.nonce);

    try {
        const res = await fetch(MemberAuth.ajaxurl, { 
            method: 'POST', 
            body: form, 
            credentials: 'include' 
        });
        const result = await res.json();
        const data = result.data;

        if (data.logged_in) {
            showLoggedIn(data);
        } else {
            showLoginForm();
        }
    } catch (e) {
        console.error('❌ Check login failed:', e);
        showLoginForm();
    }
});

function showLoggedIn(data) {
    const uiLoginForm = document.getElementById('ui-login-form');
    const uiLoggedIn = document.getElementById('ui-logged-in');

    if (uiLoginForm) uiLoginForm.style.display = 'none';
    if (uiLoggedIn) uiLoggedIn.style.display = 'block';
}

function showLoginForm() {
    const uiLoginForm = document.getElementById('ui-login-form');
    const uiLoggedIn = document.getElementById('ui-logged-in');

    if (uiLoginForm) uiLoginForm.style.display = 'block';
    if (uiLoggedIn) uiLoggedIn.style.display = 'none';
}

async function handleLogout() {
    const form = new FormData();
    form.append('action', 'member_logout');
    form.append('nonce', MemberAuth.nonce);

    try {
        const res = await fetch(MemberAuth.ajaxurl, { 
            method: 'POST', 
            body: form, 
            credentials: 'include' 
        });

        if (!res.ok) throw new Error('Network response was not ok');
        const result = await res.json();

        if (result.success && result.data.logged_out) {
            // Tùy chọn: Thêm hiệu ứng loading hoặc thông báo trước khi refresh
            console.log('✅ Logout thành công, đang làm mới trang...');
            
            // Refresh trang để cập nhật lại trạng thái Server-side (Menu, Header...)
            window.location.reload(); 
            showLoginForm();
        }
    } catch (e) {
        console.error('❌ Logout failed:', e);
    }
}