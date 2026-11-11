document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', handleSignup);
    }
});

async function handleLogin(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const loginData = {
        username: formData.get('email'),
        password: formData.get('password')
    };

    try {
        const response = await fetch('php/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(loginData)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Login successful! Redirecting...');
            setTimeout(() => {
                window.location.href = result.user?.role === 'admin' ? 'admin-panel.php' : 'dashboard.html';
            }, 1000);
        } else {
            showNotification('Login failed: ' + result.message);
        }
    } catch (error) {
        console.error('Login error:', error);
        showNotification('An error occurred during login');
    }
}

async function handleSignup(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const fullname = formData.get('fullname').trim().split(' ');

    const firstName = fullname[0] || '';
    const lastName = fullname.slice(1).join(' ') || '';

    const signupData = {
        username: formData.get('email'),
        email: formData.get('email'),
        password: formData.get('password'),
        firstName: firstName,
        lastName: lastName
    };

    try {
        const response = await fetch('php/signup.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(signupData)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Account created successfully! You can now login.');
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000);
        } else {
            showNotification('Signup failed: ' + result.message);
        }
    } catch (error) {
        console.error('Signup error:', error);
        showNotification('An error occurred during signup');
    }
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: #5C2A2F;
        color: white;
        padding: 15px 25px;
        border-radius: 5px;
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

