let allProducts = [];
let filteredProducts = [];
let currentPage = 1;
const productsPerPage = 9;

document.addEventListener('DOMContentLoaded', async () => {
    await loadProductsFromDatabase();
    loadShopProducts();
    initializePriceSlider();
    initializeFilters();
});

function loadShopProducts() {
    const urlParams = new URLSearchParams(window.location.search);
    const category = urlParams.get('category') || window.categoryFilter || 'all';

    if (category === 'all') {
        allProducts = products;
    } else {
        allProducts = products.filter(p => p.category === category);
    }

    filteredProducts = [...allProducts];
    displayProducts();
    updateResultsCount();
}

function displayProducts() {
    const container = document.getElementById('productsGrid');
    if (!container) return;

    const startIndex = (currentPage - 1) * productsPerPage;
    const endIndex = startIndex + productsPerPage;
    const productsToShow = filteredProducts.slice(startIndex, endIndex);

    if (productsToShow.length === 0) {
        container.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                <i class="fas fa-search" style="font-size: 60px; color: #ccc; margin-bottom: 20px;"></i>
                <h3>No products found</h3>
                <p style="color: #999;">Try adjusting your filters</p>
            </div>
        `;
        return;
    }

    container.innerHTML = productsToShow.map(product => createProductCard(product)).join('');
}

function initializePriceSlider() {
    const minPriceInput = document.getElementById('minPrice');
    const maxPriceInput = document.getElementById('maxPrice');
    const minPriceDisplay = document.getElementById('minPriceDisplay');
    const maxPriceDisplay = document.getElementById('maxPriceDisplay');

    if (!minPriceInput || !maxPriceInput) return;

    minPriceInput.addEventListener('input', () => {
        const minValue = parseInt(minPriceInput.value);
        const maxValue = parseInt(maxPriceInput.value);

        if (minValue > maxValue - 10000) {
            minPriceInput.value = maxValue - 10000;
        }

        minPriceDisplay.textContent = formatPrice(parseInt(minPriceInput.value));
    });

    maxPriceInput.addEventListener('input', () => {
        const minValue = parseInt(minPriceInput.value);
        const maxValue = parseInt(maxPriceInput.value);

        if (maxValue < minValue + 10000) {
            maxPriceInput.value = minValue + 10000;
        }

        maxPriceDisplay.textContent = formatPrice(parseInt(maxPriceInput.value));
    });
}

function applyPriceFilter() {
    const minPrice = parseInt(document.getElementById('minPrice').value);
    const maxPrice = parseInt(document.getElementById('maxPrice').value);

    filteredProducts = allProducts.filter(product => {
        return product.price >= minPrice && product.price <= maxPrice;
    });

    applyOtherFilters();
    currentPage = 1;
    displayProducts();
    updateResultsCount();
}

function initializeFilters() {
    const brandCheckboxes = document.querySelectorAll('input[name="brand"]');
    brandCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', applyFilters);
    });
}

function applyFilters() {
    const minPrice = parseInt(document.getElementById('minPrice').value);
    const maxPrice = parseInt(document.getElementById('maxPrice').value);

    filteredProducts = allProducts.filter(product => {
        return product.price >= minPrice && product.price <= maxPrice;
    });

    applyOtherFilters();
    currentPage = 1;
    displayProducts();
    updateResultsCount();
}

function applyOtherFilters() {
    const selectedBrands = [];
    document.querySelectorAll('input[name="brand"]:checked').forEach(checkbox => {
        selectedBrands.push(checkbox.value);
    });

    if (selectedBrands.length > 0) {
        filteredProducts = filteredProducts.filter(product => {
            return selectedBrands.includes(product.brand);
        });
    }
}

function sortProducts() {
    const sortValue = document.getElementById('sortProducts').value;

    switch(sortValue) {
        case 'price-low':
            filteredProducts.sort((a, b) => a.price - b.price);
            break;
        case 'price-high':
            filteredProducts.sort((a, b) => b.price - a.price);
            break;
        case 'latest':
            filteredProducts.sort((a, b) => b.id - a.id);
            break;
        case 'popularity':
            filteredProducts.sort((a, b) => (b.bestseller ? 1 : 0) - (a.bestseller ? 1 : 0));
            break;
        default:
            break;
    }

    displayProducts();
}

function searchProducts() {
    const searchInput = document.getElementById('sidebarSearch');
    const searchTerm = searchInput.value.toLowerCase().trim();

    if (searchTerm === '') {
        filteredProducts = [...allProducts];
    } else {
        filteredProducts = allProducts.filter(product => {
            return product.name.toLowerCase().includes(searchTerm) ||
                   product.brand.toLowerCase().includes(searchTerm) ||
                   product.category.toLowerCase().includes(searchTerm) ||
                   (product.subcategory && product.subcategory.toLowerCase().includes(searchTerm)) ||
                   (product.description && product.description.toLowerCase().includes(searchTerm));
        });
    }

    applyOtherFilters();
    currentPage = 1;
    displayProducts();
    updateResultsCount();
}

function updateResultsCount() {
    const resultsCount = document.getElementById('resultsCount');
    const totalCount = document.getElementById('totalCount');

    if (!resultsCount || !totalCount) return;

    const startIndex = (currentPage - 1) * productsPerPage + 1;
    const endIndex = Math.min(currentPage * productsPerPage, filteredProducts.length);

    resultsCount.textContent = `${startIndex}–${endIndex}`;
    totalCount.textContent = filteredProducts.length;
}

function changePage(page) {
    currentPage = page;
    displayProducts();
    updateResultsCount();
    window.scrollTo({ top: 0, behavior: 'smooth' });

    document.querySelectorAll('.page-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

function nextPage() {
    const totalPages = Math.ceil(filteredProducts.length / productsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        displayProducts();
        updateResultsCount();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function formatPrice(price) {
    return parseFloat(price).toLocaleString('fr-TN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }) + 'TND';
}

function createProductCard(product) {
    const badge = product.badge ? `<span class="product-badge">${escapeHTML(product.badge)}</span>` : '';
    const oldPrice = product.oldPrice ? `<span class="old-price">${product.oldPrice.toLocaleString('fr-TN', { minimumFractionDigits: 2 })}</span>` : '';

    return `
        <div class="product-card">
            ${badge}
            <div class="product-image">
                <img src="${escapeHTML(safeImageUrl(product.image))}" alt="${escapeHTML(product.name)}">
                <div class="product-icons">
                    <button class="product-icon" onclick="toggleWishlist(${product.id})">
                        <i class="far fa-heart"></i>
                    </button>
                    <button class="product-icon" onclick="viewProduct(${product.id})">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="product-info">
                <h3 class="product-title">
                    <a href="product-single.html?id=${product.id}">${escapeHTML(product.name)}</a>
                </h3>
                <div class="product-price">
                    ${product.price.toLocaleString('fr-TN', { minimumFractionDigits: 2 })}TND
                    ${oldPrice}
                </div>
                <button class="add-to-cart" onclick="addProductToCart(${Number(product.id)})">
                    ADD TO BASKET
                </button>
            </div>
        </div>
    `;
}

function viewProduct(productId) {
    window.location.href = `product-single.html?id=${productId}`;
}
