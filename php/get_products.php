<?php
// get_products.php - API endpoint to get products for shop pages
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $db = getDB();

    // Get category filter if provided
    $category = isset($_GET['category']) ? $_GET['category'] : null;

    // Build query
    $query = "SELECT * FROM products WHERE 1=1";
    $params = [];

    if ($category && $category !== 'all') {
        $query .= " AND category = ?";
        $params[] = $category;
    }

    $query .= " ORDER BY created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $colors = $images = $specifications = [];
    foreach ($db->query('SELECT product_id,color_name FROM product_colors ORDER BY id')->fetchAll(PDO::FETCH_ASSOC) as $row) $colors[$row['product_id']][] = $row['color_name'];
    foreach ($db->query('SELECT product_id,image_url FROM product_images ORDER BY display_order,id')->fetchAll(PDO::FETCH_ASSOC) as $row) $images[$row['product_id']][] = $row['image_url'];
    foreach ($db->query('SELECT product_id,spec_key,spec_value FROM product_specifications ORDER BY id')->fetchAll(PDO::FETCH_ASSOC) as $row) $specifications[$row['product_id']][$row['spec_key']] = $row['spec_value'];
    $formattedProducts = array_map(function($product) use ($colors, $images, $specifications) {
        return [
            'id' => (int)$product['id'],
            'name' => $product['name'],
            'price' => (float)$product['price'],
            'oldPrice' => $product['old_price'] ? (float)$product['old_price'] : null,
            'category' => $product['category'],
            'subcategory' => $product['subcategory'],
            'brand' => $product['brand'],
            'badge' => $product['badge'],
            'bestseller' => (bool) $product['bestseller'],
            'featured' => (bool) $product['featured'],
            'colors' => $colors[$product['id']] ?? [],
            'images' => $images[$product['id']] ?? [],
            'specifications' => (object) ($specifications[$product['id']] ?? []),
            'image' => $product['image'] ?: 'img/placeholder.svg',
            'sku' => $product['sku'],
            'description' => $product['description'],
            'stock' => (int)$product['stock']
        ];
    }, $products);

    echo json_encode($formattedProducts);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch products: ' . 'Service temporarily unavailable']);
}
