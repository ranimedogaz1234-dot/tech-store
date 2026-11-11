document.addEventListener('DOMContentLoaded', () => {
    checkSession();
    loadOrders();
});

async function checkSession() {
    try {
        const response = await fetch('php/check_session.php');
        const data = await response.json();

        if (!data.loggedIn) {
            window.location.href = 'login.html?redirect=dashboard.html';
            return;
        }

        document.getElementById('userName').textContent = data.user.firstName + ' ' + data.user.lastName;

        document.getElementById('firstName').value = data.user.firstName;
        document.getElementById('lastName').value = data.user.lastName;
        document.getElementById('email').value = data.user.email;

    } catch (error) {
        console.error('Session check error:', error);
        window.location.href = 'login.html';
    }
}

function showSection(section) {
    document.querySelectorAll('.dashboard-nav button').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelectorAll('.dashboard-section').forEach(sec => {
        sec.classList.remove('active');
    });

    event.target.closest('button').classList.add('active');

    if (section === 'orders') {
        document.getElementById('ordersSection').classList.add('active');
        loadOrders();
    } else if (section === 'profile') {
        document.getElementById('profileSection').classList.add('active');
    } else if (section === 'wishlist') {
        document.getElementById('wishlistSection').classList.add('active');
        loadWishlist();
    }
}

async function loadOrders() {
    try {
        const response = await fetch('php/get_user_orders.php');
        const data = await response.json();

        const ordersContent = document.getElementById('ordersContent');

        if (!data.success || data.orders.length === 0) {
            ordersContent.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>No orders yet</h3>
                    <p>Start shopping to see your orders here</p>
                    <a href="informatique.html" class="btn-primary" style="margin-top: 20px; display: inline-block;">
                        Start Shopping
                    </a>
                </div>
            `;
            return;
        }

        let html = '<table class="orders-table"><thead><tr>';
        html += '<th>Order #</th>';
        html += '<th>Date</th>';
        html += '<th>Status</th>';
        html += '<th>Total</th>';
        html += '<th>Items</th>';
        html += '<th>Actions</th>';
        html += '</tr></thead><tbody>';

        data.orders.forEach(order => {
            const orderDate = new Date(order.created_at).toLocaleDateString('fr-FR');
            const itemsList = order.items.map(item =>
                `${escapeHTML(item.product_name)} (x${item.quantity})`
            ).join('<br>');

            html += `<tr>
                <td><strong>${escapeHTML(order.order_number)}</strong></td>
                <td>${orderDate}</td>
                <td><span class="status-badge status-${order.status}">${order.status}</span></td>
                <td><strong>${parseFloat(order.total).toFixed(2)} د.ت</strong></td>
                <td style="font-size: 13px;">${itemsList}</td>
                <td>
                    <button onclick="viewOrderDetails(${order.id})" style="padding: 8px 15px; background: #5C2A2F; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        <i class="fas fa-eye"></i> View
                    </button>
                </td>
            </tr>`;
        });

        html += '</tbody></table>';
        ordersContent.innerHTML = html;

    } catch (error) {
        console.error('Load orders error:', error);
        document.getElementById('ordersContent').innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-circle"></i>
                <p>Error loading orders</p>
            </div>
        `;
    }
}

async function viewOrderDetails(orderId) {
    try {
        const response = await fetch(`php/get_order.php?id=${orderId}`);
        const data = await response.json();

        if (data.success) {
            showOrderModal(data.order, data.items);
        }
    } catch (error) {
        console.error('View order error:', error);
        alert('Error loading order details');
    }
}

function showOrderModal(order, items) {
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
    `;

    const orderDate = new Date(order.created_at).toLocaleString('fr-FR');

    let itemsHtml = items.map(item => `
        <tr>
            <td>${escapeHTML(item.product_name)}</td>
            <td style="text-align: center;">${item.quantity}</td>
            <td style="text-align: right;">${parseFloat(item.product_price).toFixed(2)} د.ت</td>
            <td style="text-align: right;"><strong>${parseFloat(item.subtotal).toFixed(2)} د.ت</strong></td>
        </tr>
    `).join('');

    modal.innerHTML = `
        <div style="background: white; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto; border-radius: 10px; padding: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Order Details - ${escapeHTML(order.order_number)}</h2>
                <button onclick="this.closest('div[style*=fixed]').remove()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px;">
                <div>
                    <h4 style="color: #666; margin-bottom: 8px;">Order Date</h4>
                    <p>${orderDate}</p>
                </div>
                <div>
                    <h4 style="color: #666; margin-bottom: 8px;">Status</h4>
                    <p><span class="status-badge status-${order.status}">${order.status}</span></p>
                </div>
                <div>
                    <h4 style="color: #666; margin-bottom: 8px;">Customer</h4>
                    <p>${escapeHTML(order.customer_first_name)} ${escapeHTML(order.customer_last_name)}<br>${escapeHTML(order.customer_email)}</p>
                </div>
                <div>
                    <h4 style="color: #666; margin-bottom: 8px;">Shipping Address</h4>
                    <p>${escapeHTML(order.shipping_address)}<br>${escapeHTML(order.shipping_city)}, ${escapeHTML(order.shipping_state)} ${escapeHTML(order.shipping_zipcode)}</p>
                </div>
            </div>

            <h3 style="margin: 30px 0 15px 0;">Order Items</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f8f8;">
                        <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Product</th>
                        <th style="padding: 15px; text-align: center; border-bottom: 2px solid #ddd;">Quantity</th>
                        <th style="padding: 15px; text-align: right; border-bottom: 2px solid #ddd;">Price</th>
                        <th style="padding: 15px; text-align: right; border-bottom: 2px solid #ddd;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml}
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="padding: 15px; text-align: right; border-top: 2px solid #ddd;"><strong>Subtotal:</strong></td>
                        <td style="padding: 15px; text-align: right; border-top: 2px solid #ddd;">${parseFloat(order.subtotal).toFixed(2)} د.ت</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="padding: 15px; text-align: right;"><strong>Shipping:</strong></td>
                        <td style="padding: 15px; text-align: right;">${parseFloat(order.shipping_cost).toFixed(2)} د.ت</td>
                    </tr>
                    <tr style="font-size: 18px; color: #5C2A2F;">
                        <td colspan="3" style="padding: 15px; text-align: right;"><strong>Total:</strong></td>
                        <td style="padding: 15px; text-align: right;"><strong>${parseFloat(order.total).toFixed(2)} د.ت</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;

    document.body.appendChild(modal);
    modal.onclick = (e) => {
        if (e.target === modal) modal.remove();
    };
}

async function updateProfile(event) {
    event.preventDefault();

    const profileData = {
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value
    };

    try {
        const response = await fetch('php/update_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(profileData)
        });

        const data = await response.json();

        if (data.success) {
            alert('Profile updated successfully!');
            checkSession();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Update profile error:', error);
        alert('Error updating profile');
    }
}

function loadWishlist() {
    const wishlist = readItems('wishlist');
    const wishlistContent = document.getElementById('wishlistContent');

    if (wishlist.length === 0) {
        wishlistContent.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-heart"></i>
                <h3>Your wishlist is empty</h3>
                <p>Add products to your wishlist to see them here</p>
            </div>
        `;
        return;
    }

    let html = '<div class="products-grid">';
    wishlist.forEach(item => {
        html += `
            <div class="product-card">
                <div class="product-image">
                    <img src="${escapeHTML(safeImageUrl(item.image))}" alt="${escapeHTML(item.name)}">
                </div>
                <div class="product-info">
                    <h3>${escapeHTML(item.name)}</h3>
                    <div class="product-price">${item.price} د.ت</div>
                    <button onclick="removeFromWishlist(${item.id}); loadWishlist();" class="btn-primary">
                        Remove
                    </button>
                </div>
            </div>
        `;
    });
    html += '</div>';
    wishlistContent.innerHTML = html;
}

async function logout() {
    try {
        await fetch('php/logout.php', {method:'POST'});
        window.location.href = 'index.html';
    } catch (error) {
        console.error('Logout error:', error);
        window.location.href = 'index.html';
    }
}
