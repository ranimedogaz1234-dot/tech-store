document.addEventListener('DOMContentLoaded', async () => {
    await loadProductsFromDatabase();
    initializeSearch();
    initializeMobileMenu();
    initializeScrollEffects();
});

function loadOrderedItems(cart) {
    const orderedItems = document.getElementById('orderedItems');

    if (!orderedItems) return;

    if (cart.length === 0) {
        orderedItems.innerHTML = '<p>No items in order</p>';
        return;
    }

    orderedItems.innerHTML = cart.map(item => `
        <div class="order-item">
            <img src="${escapeHTML(safeImageUrl(item.image))}" alt="${escapeHTML(item.name)}" class="order-item-image">
            <div class="order-item-info">
                <div class="order-item-name">${escapeHTML(item.name)}</div>
                <div class="order-item-quantity">Quantity: ${item.quantity}</div>
            </div>
            <div class="order-item-price">${(parseFloat(item.price) * item.quantity).toFixed(2)} د.ت</div>
        </div>
    `).join('');
}

function saveOrder(orderNumber, orderDate, total, items) {
    const orders = JSON.parse(localStorage.getItem('orders') || '[]');

    const order = {
        id: orderNumber,
        date: orderDate,
        status: 'Processing',
        total: total.toFixed(2),
        items: items,
        timestamp: new Date().toISOString()
    };

    orders.push(order);
    localStorage.setItem('orders', JSON.stringify(orders));
}

function updateCartCount() {
    const badges = document.querySelectorAll('#cartCount, .icon-link .badge');
    badges.forEach(badge => {
        if (badge.id === 'cartCount' || badge.closest('.icon-link').href.includes('cart')) {
            badge.textContent = readItems('cart').reduce((sum,item)=>sum+item.quantity,0);
        }
    });
}

function initializeSearch() {
    const searchIcon = document.querySelector('.search-icon');
    const closeSearch = document.getElementById('closeSearch');
    const mainHeader = document.querySelector('.main-header');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const searchPage = document.getElementById('searchPage');
    const searchResults = document.getElementById('searchResults');
    const noResults = document.getElementById('noResults');
    const resultsHeader = document.getElementById('resultsHeader');
    const resultsCount = document.getElementById('resultsCount');

    const heroSection = document.querySelector('.hero-section');
    const featuresSection = document.querySelector('.features-section');
    const categoriesSection = document.querySelector('.categories-section');
    const productsSections = document.querySelectorAll('.products-section');
    const promoBanner = document.querySelector('.promo-banner');

    let currentResults = [];

    if (searchIcon) {
        searchIcon.addEventListener('click', function(e) {
            e.preventDefault();
            mainHeader.classList.add('search-mode');
            searchInput.focus();
        });
    }

    if (closeSearch) {
        closeSearch.addEventListener('click', function(e) {
            e.preventDefault();
            mainHeader.classList.remove('search-mode');
            searchPage.classList.remove('active');

            if (heroSection) heroSection.classList.remove('hidden');
            if (featuresSection) featuresSection.classList.remove('hidden');
            if (categoriesSection) categoriesSection.classList.remove('hidden');
            productsSections.forEach(section => section.classList.remove('hidden'));
            if (promoBanner) promoBanner.classList.remove('hidden');

            searchInput.value = '';
        });
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }

    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }

    function performSearch() {
        const query = searchInput.value.toLowerCase().trim();

        if (!query) return;

        if (heroSection) heroSection.classList.add('hidden');
        if (featuresSection) featuresSection.classList.add('hidden');
        if (categoriesSection) categoriesSection.classList.add('hidden');
        productsSections.forEach(section => section.classList.add('hidden'));
        if (promoBanner) promoBanner.classList.add('hidden');

        searchPage.classList.add('active');

        currentResults = products.filter(product =>
            product.name.toLowerCase().includes(query) ||
            product.brand.toLowerCase().includes(query) ||
            (product.category && product.category.toLowerCase().includes(query)) ||
            (product.subcategory && product.subcategory.toLowerCase().includes(query)) ||
            (product.description && product.description.toLowerCase().includes(query))
        );

        displaySearchResults(currentResults);
    }

    function displaySearchResults(results) {
        if (results.length === 0) {
            noResults.classList.add('active');
            resultsHeader.style.display = 'none';
            searchResults.innerHTML = '';
        } else {
            noResults.classList.remove('active');
            resultsHeader.style.display = 'flex';
            resultsCount.textContent = `Showing all ${results.length} results`;

            searchResults.innerHTML = results.map(product => createProductCard(product)).join('');
        }
    }

    const applyFilterBtn = document.getElementById('applyFilter');
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            const selectedBrands = Array.from(document.querySelectorAll('.brand-checkbox input:checked'))
                .map(cb => cb.value.toLowerCase());

            if (selectedBrands.length === 0) {
                displaySearchResults(currentResults);
            } else {
                const filtered = currentResults.filter(product =>
                    selectedBrands.includes(product.brand.toLowerCase())
                );
                displaySearchResults(filtered);
            }
        });
    }
}

function initializeMobileMenu() {
}

function initializeScrollEffects() {
}

if (typeof loadOrderDetails === 'function') loadOrderDetails();
