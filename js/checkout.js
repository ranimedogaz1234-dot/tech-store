document.addEventListener('DOMContentLoaded', () => {
    loadOrderSummary();
    initializeCheckoutForm();
});

function loadOrderSummary() {
    const cart = readItems('cart');
    const summaryItems = document.getElementById('summaryItems');

    if (cart.length === 0) {
        window.location.href = 'cart.html';
        return;
    }

    summaryItems.innerHTML = cart.map(item => createSummaryItem(item)).join('');

    updateCheckoutTotals();
}

function createSummaryItem(item) {
    const colorInfo = item.color ? `<div class="summary-item-meta">Couleur: ${escapeHTML(item.color)}</div>` : '';

    return `
        <div class="summary-item">
            <div class="summary-item-image">
                <img src="${escapeHTML(safeImageUrl(item.image))}" alt="${escapeHTML(item.name)}">
            </div>
            <div class="summary-item-info">
                <div class="summary-item-title">${escapeHTML(item.name)}</div>
                ${colorInfo}
                <div class="summary-item-quantity">Quantity: ${item.quantity}</div>
            </div>
            <div class="summary-item-price">${formatPrice(item.price * item.quantity)}</div>
        </div>
    `;
}

function initializeCheckoutForm() {
    const form = document.getElementById('checkoutForm');
    const button = form.querySelector('button[type="submit"]');
    const error = document.getElementById('checkoutError');
    let submitting = false;
    let checkoutToken;
    const showError = message => { error.textContent = message; error.hidden = false; };
    button.disabled = true;
    const ready = getCsrfToken().then(() => fetch('php/checkout_token.php', {cache:'no-store', credentials:'same-origin'}))
        .then(async response => {
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error('Checkout could not be initialized. Reload this page to retry.');
            checkoutToken = result.checkoutToken;
            button.disabled = false;
        }).catch(e => { showError(e.message); throw e; });
    // The error is shown immediately; a failed initialization never submits an order.
    ready.catch(() => {});

    const orderNoteCheckbox = document.getElementById('orderNote');
    const orderNoteSection = document.getElementById('orderNoteSection');

    if (orderNoteCheckbox) {
        orderNoteCheckbox.addEventListener('change', () => {
            orderNoteSection.style.display = orderNoteCheckbox.checked ? 'block' : 'none';
        });
    }

    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        if (submitting) return;
        const cart = readItems('cart');
        if (cart.length === 0) {
            showError('Your cart is empty. Add a product before placing an order.');
            return;
        }
        submitting = true;
        button.disabled = true;
        button.textContent = 'Placing order…';
        error.hidden = true;
        const cartSnapshot = localStorage.getItem('cart');
        try {
            await ready;
            button.disabled = true;
            const data = new URLSearchParams(new FormData(form));
            data.set('items', JSON.stringify(cart));
            data.set('payment', 'cod');
            data.set('checkoutToken', checkoutToken);
            data.set('coupon', checkoutCouponDiscount > 0 ? 'DEMO20' : '');
            const response = await fetch(form.action, {method:'POST', headers:{Accept:'application/json'}, body:data});
            const result = await response.json();
            if (!response.ok || !result.success || !/^ORD[A-Z0-9]+$/.test(result.orderNumber)) {
                throw new Error(result.message || 'Your order could not be placed. Your cart has been kept.');
            }
            // Clear only the submitted cart AFTER the server confirms a committed order.
            // Preserve items added in another tab while the request was in flight.
            if (localStorage.getItem('cart') === cartSnapshot) {
                cartInstance.clear();
                localStorage.removeItem('activeCoupon');
            }
            goToOrderConfirmation(result.orderNumber);
        } catch (e) {
            showError(e instanceof SyntaxError || e instanceof TypeError
                ? 'Checkout could not be completed. Your cart has been kept; please retry.' : e.message);
            submitting = false;
            button.disabled = !checkoutToken;
            button.textContent = 'Place Order';
        }
    });
}

function goToOrderConfirmation(orderNumber) {
    window.location.assign('thankyoupage.php?order=' + encodeURIComponent(orderNumber));
}

let checkoutCouponDiscount = localStorage.getItem('activeCoupon') === 'DEMO20' ? 0.20 : 0;

function toggleCoupon() {
    const couponInput = document.getElementById('couponInput');
    couponInput.style.display = couponInput.style.display === 'none' ? 'flex' : 'none';
}

function applyCoupon() {
    const couponValue = document.getElementById('couponField').value.trim().toUpperCase();

    if (couponValue === "DEMO20") {
        checkoutCouponDiscount = 0.20;
        showNotification("Coupon applied! 20% OFF");
    } else {
        checkoutCouponDiscount = 0;
        showNotification("Invalid coupon!");
    }

    updateCheckoutTotals();
}

function updateCheckoutTotals() {
    const cart = readItems('cart');

    let subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    let discountAmount = subtotal * checkoutCouponDiscount;
    let subtotalAfterDiscount = subtotal - discountAmount;

    const shipping = 7.00;
    const total = subtotalAfterDiscount + shipping;

    document.getElementById('subtotalAmount').textContent = formatPrice(subtotalAfterDiscount);
    document.getElementById('totalAmount').textContent = formatPrice(total);
}

function calculateSubtotal() {
    const cart = readItems('cart');
    let subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    return subtotal - (subtotal * checkoutCouponDiscount);
}

function calculateTotal() {
    const shipping = 7.00;
    return calculateSubtotal() + shipping;
}

function formatPrice(price) {
    return price.toLocaleString('fr-TN', { minimumFractionDigits: 2 }) + 'TND';
}

