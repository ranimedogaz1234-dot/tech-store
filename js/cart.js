class Cart {
    constructor() {
        this.items = this.getCart();
    }

    getCart() {
        const cart = localStorage.getItem('cart');
        try {
            return readItems('cart');
        } catch {
            return [];
        }
    }

    saveCart() {
        localStorage.setItem('cart', JSON.stringify(this.items));
        this.updateCartCount();
    }

    addItem(product) {
        const productId = parseInt(product.id);
        const existingItem = this.items.find(item =>
            item.id === productId && (!product.color || item.color === product.color)
        );

        if (existingItem) {
            existingItem.quantity = Math.min(100, existingItem.quantity + (Number(product.quantity) || 1));
        } else {
            this.items.push({
                id: productId,
                name: product.name,
                price: parseFloat(product.price),
                image: product.image,
                quantity: product.quantity || 1,
                color: product.color || null
            });
        }

        this.saveCart();
        this.showNotification('Product added to cart!');
    }

    removeItem(productId, color = null) {
        this.items = this.items.filter(item => {
            if (color) {
                return !(item.id === productId && item.color === color);
            }
            return item.id !== productId;
        });
        this.saveCart();
    }

    updateQuantity(productId, quantity, color = null) {
        const item = this.items.find(i => {
            if (color) {
                return i.id === productId && i.color === color;
            }
            return i.id === productId;
        });

        if (item) {
            item.quantity = Math.min(100, parseInt(quantity) || 0);
            if (item.quantity <= 0) {
                this.removeItem(productId, color);
            } else {
                this.saveCart();
            }
        }
    }

    getTotal() {
        return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    getSubtotal() {
        return this.getTotal();
    }

    clear() {
        this.items = [];
        this.saveCart();
    }

    updateCartCount() {
        const count = this.items.reduce((sum, item) => sum + item.quantity, 0);
        const cartBadge = document.getElementById('cartCount');
        if (cartBadge) {
            cartBadge.textContent = count;
        }
        const badges = document.querySelectorAll('.icon-link .badge');
        badges.forEach(badge => {
            const parent = badge.closest('.icon-link');
            if (parent && parent.href && parent.href.includes('cart')) {
                badge.textContent = count;
            }
        });
    }

    showNotification(message) {

        const notification = document.createElement('div');
        notification.className = 'cart-notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 15px 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;

        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideIn 0.3s ease reverse';
            setTimeout(() => notification.remove(), 300);
        }, 2000);
    }
}
let activeCoupon = localStorage.getItem('activeCoupon') || null;
let couponDiscount = activeCoupon === "DEMO20" ? 0.20 : 0;

const cartInstance = new Cart();

function addToCart(product) {
    cartInstance.addItem(product);
}

function removeFromCart(productId, color = null) {
    cartInstance.removeItem(productId, color);
    loadCartItems();
}

function updateCartQuantity(productId, quantity, color = null) {
    if (color === '') color = null;
    cartInstance.updateQuantity(productId, quantity, color);
    loadCartItems();
}

function getCart() {
    return cartInstance.items;
}

function updateCartCount() {
    cartInstance.updateCartCount();
}

function loadCartItems() {
    const cartItems = document.getElementById('cartItems');
    const cartTable = document.querySelector('.cart-table');
    const cartActions = document.querySelector('.cart-actions');
    const cartTotalsSection = document.querySelector('.cart-totals-section');
    const cartEmpty = document.getElementById('cartEmpty');
    const cart = getCart();

    if (!cartItems) return;

    if (cart.length === 0) {
        if (cartTable) cartTable.style.display = 'none';
        if (cartActions) cartActions.style.display = 'none';
        if (cartTotalsSection) cartTotalsSection.style.display = 'none';
        if (cartEmpty) cartEmpty.style.display = 'block';
        updateCartSummary();
        return;
    }

    if (cartTable) cartTable.style.display = 'block';
    if (cartActions) cartActions.style.display = 'flex';
    if (cartTotalsSection) cartTotalsSection.style.display = 'grid';
    if (cartEmpty) cartEmpty.style.display = 'none';

    cartItems.innerHTML = cart.map(item => {
        const colorInfo = item.color ? `<br><small style="color: #999;">Color: ${escapeHTML(item.color)}</small>` : '';
        return `
            <tr data-product-id="${item.id}">
                <td><button class="remove-btn" onclick="removeFromCart(${item.id}, ${jsArg(item.color || "")})">✕</button></td>
                <td class="product-image-cell">
                    <img src="${escapeHTML(safeImageUrl(item.image))}" alt="${escapeHTML(item.name)}">
                </td>
                <td class="product-name-cell">
                    <a href="product-single.html?id=${item.id}">${escapeHTML(item.name)}</a>
                    ${colorInfo}
                </td>
                <td class="price-cell">${formatPrice(item.price)}</td>
                <td class="quantity-cell">
                    <div class="quantity-selector">
                        <button onclick="updateCartQuantity(${item.id}, parseInt(this.parentElement.querySelector('input').value) - 1, ${jsArg(item.color || "")})">-</button>
                        <input type="number" value="${item.quantity}" min="1"
                               onchange="updateCartQuantity(${item.id}, this.value, ${jsArg(item.color || "")})">
                        <button onclick="updateCartQuantity(${item.id}, parseInt(this.parentElement.querySelector('input').value) + 1, ${jsArg(item.color || "")})">+</button>
                    </div>
                </td>
                <td class="subtotal-cell">${formatPrice(item.price * item.quantity)}</td>
            </tr>
        `;
    }).join('');

    updateCartSummary();
}

function updateCartSummary() {
    const cart = getCart();
    let subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

    let discountAmount = subtotal * couponDiscount;
    let subtotalAfterDiscount = subtotal - discountAmount;

    const shipping = subtotal > 0 ? 7.00 : 0;

    const total = subtotalAfterDiscount + shipping;

    const subtotalEl = document.getElementById('cartSubtotal');
    const shippingEl = document.getElementById('cartShipping');
    const totalEl = document.getElementById('cartTotal');

    if (subtotalEl) subtotalEl.textContent = formatPrice(subtotalAfterDiscount);
    if (shippingEl) shippingEl.textContent = formatPrice(shipping);
    if (totalEl) totalEl.textContent = formatPrice(total);
}


function formatPrice(price) {
    return parseFloat(price).toLocaleString('fr-TN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }) + 'TND';
}

function applyCoupon() {
    const couponCode = document.getElementById('couponCode').value.trim().toUpperCase();

    if (!couponCode) {
        alert('Please enter a coupon code');
        return;
    }
    if (couponCode === "DEMO20") {
        couponDiscount = 0.20;
        activeCoupon = "DEMO20";
        localStorage.setItem('activeCoupon', "DEMO20");

        alert("Coupon applied! You received a 20% discount.");
    } else {
        couponDiscount = 0;
        activeCoupon = null;
        localStorage.removeItem('activeCoupon');

        alert("Invalid coupon code.");
    }

    updateCartSummary();
}


function proceedToCheckout() {
    const cart = getCart();
    if (cart.length === 0) {
        alert('Your cart is empty');
        return;
    }
    window.location.href = 'checkout.html';
}

function updateCartItems() {
    loadCartItems();
}

document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
    if (document.getElementById('cartItems')) {
        loadCartItems();
    }
});
