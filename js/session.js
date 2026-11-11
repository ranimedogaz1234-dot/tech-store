document.addEventListener('DOMContentLoaded', async () => {
    await checkSession();
});

async function checkSession() {
    try {
        const response = await fetch('php/check_session.php');
        const data = await response.json();

        if (data.success && data.loggedIn) {
            // User is logged in
            updateHeaderForLoggedInUser(data.user);
        } else {
            // User is not logged in
            updateHeaderForLoggedOutUser();
        }
    } catch (error) {
        console.error('Session check error:', error);
        updateHeaderForLoggedOutUser();
    }
}

function updateHeaderForLoggedInUser(user) {
    const loginLink = document.getElementById('loginLink');
    if (loginLink) {
        loginLink.href = 'dashboard.html';
        loginLink.innerHTML = '<i class="fas fa-user-circle"></i>';
        loginLink.title = 'Dashboard';
    }

    // Add logout link if not exists
    const headerIcons = document.querySelector('.header-icons');
    if (headerIcons && !document.getElementById('logoutLink')) {
        const logoutLink = document.createElement('a');
        logoutLink.href = '#';
        logoutLink.className = 'icon-link';
        logoutLink.id = 'logoutLink';
        logoutLink.innerHTML = '<i class="fas fa-sign-out-alt"></i>';
        logoutLink.title = 'Logout';
        logoutLink.onclick = logout;
        headerIcons.appendChild(logoutLink);
    }
}

function updateHeaderForLoggedOutUser() {
    const loginLink = document.getElementById('loginLink');
    if (loginLink) {
        loginLink.href = 'login.html';
        loginLink.innerHTML = '<i class="far fa-user"></i>';
        loginLink.title = 'Login';
    }

    // Remove logout link if exists
    const logoutLink = document.getElementById('logoutLink');
    if (logoutLink) {
        logoutLink.remove();
    }
}

async function logout() {
    try {
        const response = await fetch('php/logout.php', {method:'POST'});
        const data = await response.json();

        if (data.success) {
            window.location.href = 'index.html';
        } else {
            alert('Logout failed');
        }
    } catch (error) {
        console.error('Logout error:', error);
        alert('Logout failed');
    }
}
