
let currentProduct = null;
let selectedColor = null;

document.addEventListener('DOMContentLoaded', async () => {
    await loadProductsFromDatabase();
    const urlParams = new URLSearchParams(window.location.search);
    const productId = parseInt(urlParams.get('id'));

    if (productId) {
        loadProduct(productId);
    }

    initializeTabs();
    initializeThumbnails();
    initializeReviewStars();
});

function loadProduct(productId) {
    currentProduct = products.find(p => p.id === productId);

    if (!currentProduct) {
        window.location.href = 'informatique.html';
        return;
    }

    displayProduct();
}

function displayProduct() {
    document.title = `${currentProduct.name} - tech store`;

    document.getElementById('productName').textContent = currentProduct.name;

    const defaultColor = currentProduct.colors[0]; 
    const defaultImages = currentProduct.imagesByColor?.[defaultColor] || (currentProduct.images?.length ? currentProduct.images : [currentProduct.image]);
    initializeThumbnails(defaultImages);
    document.getElementById('productTitle').textContent = currentProduct.name;
    document.getElementById('currentPrice').textContent = formatPrice(currentProduct.price);

    if (currentProduct.oldPrice) {
        document.getElementById('oldPrice').textContent = formatPrice(currentProduct.oldPrice);
        document.getElementById('oldPrice').style.display = 'inline';
    } else {
        document.getElementById('oldPrice').style.display = 'none';
    }

    if (currentProduct.colors && currentProduct.colors.length > 0) {
        displayColors();
    }

    
    document.getElementById('productSKU').textContent = currentProduct.sku;

    // Update categories
    const categories = [currentProduct.category, currentProduct.subcategory].filter(Boolean).join(', ');
    document.getElementById('productCategories').textContent = categories;

    // Update brand
    document.getElementById('productBrand').textContent = currentProduct.brand;

    // Update description
    document.getElementById('descriptionContent').innerHTML = `<p>${escapeHTML(currentProduct.description)}</p>`;

    // Update additional information
    displayAdditionalInfo();

    // Load related products
    loadRelatedProducts();
}

// Display colors as CIRCLES!
function displayColors() {
    const colorSection = document.getElementById('colorSection');
    const colorOptions = document.getElementById('colorOptions');

    colorSection.style.display = 'block';

    const colorMap = {
        'white': '#FFFFFF',
        'black': '#000000',
        'beige': '#F5F5DC',
        'purple': '#9e85ceff',
        'pink': '#FFB6C1',
        'blue': '#4f629bff',
        'red': '#DC143C',
        'green': '#228B22',
        'yellow': '#FFD700',
        'orange': '#df9e58ff',
        'gray': '#808080',
        'brown': '#8B4513'
    };

    colorOptions.replaceChildren();
    for (const color of currentProduct.colors) {
        const code = colorMap[color.toLowerCase()] || '#CCCCCC';
        const swatch = document.createElement('div'); swatch.className='color-circle'; swatch.dataset.color=color; swatch.style.backgroundColor=code;
        if (color.toLowerCase()==='white') swatch.style.border='2px solid #ddd';
        swatch.addEventListener('click', () => selectColor(color, code, true)); colorOptions.append(swatch);
    }

    // Select first color by default
    if (!selectedColor && currentProduct.colors.length > 0) {
        selectColor(currentProduct.colors[0], colorMap[currentProduct.colors[0].toLowerCase()],false);
    }
}

// Select color
function selectColor(colorName, colorCode, clicked = false) {
    selectedColor = colorName;

    // Update selected class
    document.querySelectorAll('.color-circle').forEach(circle => {
        circle.classList.remove('selected');
    });

    document.querySelectorAll('.color-circle').forEach(circle => circle.classList.toggle('selected', circle.dataset.color === colorName));

    // Update selected color text
    document.getElementById('selectedColor').textContent = `Couleur: ${colorName}`;

    // Update thumbnails
    const colorImages = currentProduct.imagesByColor?.[colorName] || [currentProduct.image];
    initializeThumbnails(colorImages);
}

// Display additional information
function displayAdditionalInfo() {
    const table = document.getElementById('additionalInfoTable');

    if (!currentProduct.specifications) return;

    table.innerHTML = Object.entries(currentProduct.specifications).map(([key, value]) => `
        <tr>
            <td>${escapeHTML(key)}</td>
            <td>${escapeHTML(value)}</td>
        </tr>
    `).join('');
}

// Quantity controls
function increaseQty() {
    const qtyInput = document.getElementById('quantity');
    qtyInput.value = parseInt(qtyInput.value) + 1;
}

function decreaseQty() {
    const qtyInput = document.getElementById('quantity');
    const currentValue = parseInt(qtyInput.value);
    if (currentValue > 1) {
        qtyInput.value = currentValue - 1;
    }
}

// Add to basket
function addToBasket() {
    const quantity = parseInt(document.getElementById('quantity').value);

    const productToAdd = {
        id: currentProduct.id,
        name: currentProduct.name,
        price: currentProduct.price,
        image: currentProduct.image,
        quantity: quantity,
        color: selectedColor
    };

    addToCart(productToAdd);
}

// Add to wishlist
function addToWishlist() {
    toggleWishlist(currentProduct.id);
}

// Tabs functionality
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabName = button.getAttribute('data-tab');

            // Remove active class from all tabs and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Add active class to clicked tab and its content
            button.classList.add('active');
            document.getElementById(tabName).classList.add('active');
        });
    });
}

// Thumbnails functionality
function initializeThumbnails(images) {
    const thumbnailsContainer = document.querySelector('.thumbnail-images');
    const mainImage = document.getElementById('mainImage');

    if (!thumbnailsContainer || !images) return;

    // Clear thumbnails first
    thumbnailsContainer.innerHTML = '';

    // Create thumbnails dynamically
    images.forEach((imgSrc, index) => {
        const thumbDiv = document.createElement('div');
        thumbDiv.classList.add('thumbnail');
        if (index === 0) thumbDiv.classList.add('active');
        thumbDiv.dataset.index = index;

        const thumbImg = document.createElement('img');
        thumbImg.src = safeImageUrl(imgSrc);
        thumbImg.alt = `Thumbnail ${index + 1}`;

        thumbDiv.appendChild(thumbImg);
        thumbnailsContainer.appendChild(thumbDiv);

        // Click event
        thumbDiv.addEventListener('click', () => {
            // Remove active from all
            thumbnailsContainer.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            thumbDiv.classList.add('active');

            // Update main image
            mainImage.src = safeImageUrl(imgSrc);
        });
    });

    // Set main image initially
    mainImage.src = safeImageUrl(images[0]);
}

// Review stars
function initializeReviewStars() {
    const stars = document.querySelectorAll('.stars i');

    stars.forEach(star => {
        star.addEventListener('click', () => {
            const rating = parseInt(star.getAttribute('data-rating'));

            // Update stars
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.classList.remove('far');
                    s.classList.add('fas');
                } else {
                    s.classList.remove('fas');
                    s.classList.add('far');
                }
            });
        });

        star.addEventListener('mouseenter', () => {
            const rating = parseInt(star.getAttribute('data-rating'));
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.classList.add('active');
                }
            });
        });

        star.addEventListener('mouseleave', () => {
            stars.forEach(s => s.classList.remove('active'));
        });
    });
}

// Load related products
function loadRelatedProducts() {
    const container = document.getElementById('relatedProducts');
    if (!container) return;

    // Get products from same category, excluding current product
    const relatedProducts = products
        .filter(p => p.category === currentProduct.category && p.id !== currentProduct.id)
        .slice(0, 4);

    container.innerHTML = relatedProducts.map(product => createProductCard(product)).join('');
}

// Create product card
function createProductCard(product) {
    const badge = product.badge ? `<span class="product-badge">${escapeHTML(product.badge)}</span>` : '';
    const oldPrice = product.oldPrice ? `<span class="old-price">${formatPrice(product.oldPrice)}</span>` : '';

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
                    ${formatPrice(product.price)}
                    ${oldPrice}
                </div>
                <button class="add-to-cart" onclick="addProductToCart(${Number(product.id)})">
                    ADD TO BASKET
                </button>
            </div>
        </div>
    `;
}

function formatPrice(price) {
    return parseFloat(price).toLocaleString('fr-TN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }) + ' TND';
}

function viewProduct(productId) {
    window.location.href = `product-single.html?id=${productId}`;
}
