function getWishlist() {
    const wishlist = localStorage.getItem('wishlist');
    return readItems('wishlist');
}

function saveWishlist(wishlist) {
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    updateWishlistCount();
}

function updateWishlistCount() {
    const wishlist = getWishlist();
    const badges = document.querySelectorAll('#wishlistCount, .icon-link .badge');
    badges.forEach(badge => {
        if (badge.id === 'wishlistCount' || badge.closest('.icon-link').href.includes('wishlist')) {
            badge.textContent = wishlist.length;
        }
    });
}

function loadWishlistItems() {
    const wishlist = getWishlist();
    const wishlistItems = document.getElementById('wishlistItems');

    if (!wishlistItems) return;

    if (wishlist.length === 0) {
        wishlistItems.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px;">
                    Your wishlist is empty
                </td>
            </tr>
        `;
        return;
    }

    wishlistItems.innerHTML = wishlist.map(item => `
        <tr data-product-id="${item.id}">
            <td><input type="checkbox" class="wishlist-checkbox" data-id="${item.id}"></td>
            <td><button class="remove-btn" onclick="removeFromWishlist(${item.id})">✕</button></td>
            <td class="product-image-cell">
                <a href="product-single.html?id=${item.id}">
                    <img src="${escapeHTML(safeImageUrl(item.image))}" alt="${escapeHTML(item.name)}">
                </a>
            </td>
            <td class="product-name-cell">
                <a href="product-single.html?id=${item.id}">${escapeHTML(item.name)}</a>
            </td>
            <td class="price-cell">
                ${item.oldPrice ? `<span class="old-price">${item.oldPrice} د.ت</span>` : ''}
                <span class="current-price">${item.price} د.ت</span>
            </td>
            <td class="date-cell">${item.dateAdded}</td>
            <td><div class="stock-status"><i class="fas fa-check"></i> In stock</div></td>
            <td><button class="add-to-cart-btn" onclick="addToCartFromWishlist(${item.id})">Add to Cart</button></td>
        </tr>
    `).join('');
}

function removeFromWishlist(productId) {
    let wishlist = getWishlist();
    wishlist = wishlist.filter(item => item.id !== productId);
    saveWishlist(wishlist);
    loadWishlistItems();
}

function addToCartFromWishlist(productId) {
    const wishlist = getWishlist();
    const product = wishlist.find(item => item.id === productId);

    if (product) {
        addToCart(product);
        alert('Product added to cart!');
    }
}

function applyBulkAction() {
    const action = document.getElementById('bulkActions').value;
    const checkboxes = document.querySelectorAll('.wishlist-checkbox:checked');

    if (!action) {
        alert('Please select an action');
        return;
    }

    if (checkboxes.length === 0) {
        alert('Please select items');
        return;
    }

    checkboxes.forEach(checkbox => {
        const productId = parseInt(checkbox.dataset.id);

        if (action === 'addToCart') {
            addToCartFromWishlist(productId);
        } else if (action === 'remove') {
            removeFromWishlist(productId);
        }
    });

    if (action === 'addToCart') {
        alert('Selected items added to cart!');
    }
}

function addSelectedToCart() {
    const checkboxes = document.querySelectorAll('.wishlist-checkbox:checked');

    if (checkboxes.length === 0) {
        alert('Please select items');
        return;
    }

    checkboxes.forEach(checkbox => {
        const productId = parseInt(checkbox.dataset.id);
        addToCartFromWishlist(productId);
    });

    alert('Selected items added to cart!');
}

function addAllToCart() {
    const wishlist = getWishlist();

    if (wishlist.length === 0) {
        alert('Your wishlist is empty');
        return;
    }

    wishlist.forEach(product => {
        addToCart(product);
    });

    alert('All items added to cart!');
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Wishlist DOMContentLoaded fired');
    updateWishlistCount();
    loadWishlistItems();
    updateCartCount(); // Also update cart count on wishlist page
    console.log('Wishlist items loaded');
});
