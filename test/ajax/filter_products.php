<?php
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../classes/Product.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

// Get filters from POST data
$filters = [];
if (isset($_POST['brand']) && is_array($_POST['brand'])) {
    $filters['brand'] = $_POST['brand'];
}
if (isset($_POST['bandwidth']) && is_array($_POST['bandwidth'])) {
    $filters['bandwidth'] = $_POST['bandwidth'];
}
if (isset($_POST['min_price']) && !empty($_POST['min_price'])) {
    $filters['min_price'] = floatval($_POST['min_price']);
}
if (isset($_POST['max_price']) && !empty($_POST['max_price'])) {
    $filters['max_price'] = floatval($_POST['max_price']);
}
if (isset($_POST['search']) && !empty($_POST['search'])) {
    $filters['search'] = $_POST['search'];
}
if (isset($_POST['sort'])) {
    $filters['sort'] = $_POST['sort'];
}

// Get filtered products
$products = $product->getAll($filters);
$totalProducts = $product->getCount($filters);

// Return JSON response
echo json_encode([
    'products' => $products,
    'total' => $totalProducts,
    'success' => true
]);
?>
