<?php
require_once 'config/database.php';
require_once 'classes/Product.php';
require_once 'includes/auth_check.php';

include 'Calendar.php';

// Check if user is authenticated
if (!$currentUser) {
    header('Location: login.php?message=session_expired');
    exit();
}

// Get and validate product ID from URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId <= 0) {
    header('Location: index.php');
    exit();
}

// Initialize database connection (uses central Database class)
$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

// Get product details
$productDetails = $product->getById($productId);

if (!$productDetails) {
    header('Location: index.php?error=product_not_found');
    exit();
}

// Create calendar for booking visualization
$currentDateTime = new DateTime();
$calendar = new Calendar($currentDateTime->format('Y-m-d'));

// Get current booking using the same database connection
$stmt = $db->prepare("
    SELECT u.first_name, u.last_name, b.start_date, b.end_date
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.product_id = ? 
    AND b.status = 'active' 
");
$stmt->execute([$productId]);
$currentBooking = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($productDetails['name']); ?> - Electronics Catalog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    		<link href="calendar.css" rel="stylesheet" type="text/css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-orange-600">ElectroStore</h1>
                    <nav class="hidden md:flex space-x-6">
                        <a href="index.php" class="text-gray-600 hover:text-orange-600">Products</a>
                        <a href="#" class="text-gray-600 hover:text-orange-600">Solutions</a>
                        <a href="#" class="text-gray-600 hover:text-orange-600">Support</a>
                    </nav>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2 text-gray-600">
                        <i class="fas fa-user-circle text-xl"></i>
                        <span><?php echo htmlspecialchars($currentUser['username']); ?></span>
                        <a href="logout.php" class="text-red-600 hover:text-red-700 ml-4">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="max-w-7xl mx-auto px-4 py-3">
        <nav class="text-sm text-gray-600">
            <a href="index.php" class="hover:text-orange-600">Oscilloscopes</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900"><?php echo htmlspecialchars($productDetails['name']); ?></span>
        </nav>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="bg-white rounded-lg shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-6">
                <!-- Product Image -->
                <div class="space-y-4">
                    <div class="aspect-square bg-gray-100 rounded-lg flex items-center justify-center">
                        <img src="<?php echo htmlspecialchars($productDetails['image_url'] ?: '/public/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($productDetails['name']); ?>" 
                             class="max-w-full max-h-full object-contain">
                    </div>
                </div>

                <!-- Product Information -->
                <div class="space-y-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($productDetails['name']); ?></h1>
                        <p class="text-gray-600"><?php echo htmlspecialchars($productDetails['description']); ?></p>
                    </div>

                    <!-- Stock Status -->
                    <div class="flex items-center space-x-4">
                        <?php
                        $stockClass = '';
                        $stockText = '';
                        switch($productDetails['stock_status']) {
                            case 'in_stock':
                                $stockClass = 'bg-green-100 text-green-800';
                                $stockText = 'In Stock';
                                break;
                            case 'low_stock':
                                $stockClass = 'bg-yellow-100 text-yellow-800';
                                $stockText = 'Low Stock';
                                break;
                            case 'out_of_stock':
                                $stockClass = 'bg-red-100 text-red-800';
                                $stockText = 'Out of Stock';
                                break;
                        }
                        ?>
                        <span class="<?php echo $stockClass; ?> px-3 py-1 rounded-full text-sm font-medium"><?php echo $stockText; ?></span>
                        <div class="flex items-center">
                            <i class="fas fa-star text-yellow-400"></i>
                            <span class="ml-1 text-gray-600"><?php echo number_format($productDetails['rating'], 1); ?> (<?php echo $productDetails['review_count']; ?> reviews)</span>
                        </div>
                    </div>

                    <!-- Added current holder information section -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-900 mb-2">Current Booking Status</h3>
                        <?php if ($currentBooking): ?>
                            <div class="text-sm text-gray-700">
                                <p><strong>Currently held by:</strong> <?php echo htmlspecialchars($currentBooking['first_name'] . ' ' . $currentBooking['last_name']); ?></p>
                                <p><strong>Booking period:</strong> <?php echo date('M j, Y', strtotime($currentBooking['start_date'])); ?> - <?php echo date('M j, Y', strtotime($currentBooking['end_date'])); ?></p>
                                <p class="text-red-600 mt-1"><i class="fas fa-clock mr-1"></i>Available from <?php echo date('M j, Y', strtotime($currentBooking['end_date'] . ' +1 day')); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="text-sm text-green-700">
                                <p><i class="fas fa-check-circle mr-1"></i><strong>Available for booking</strong></p>
                                <p>This product is currently available and can be booked immediately.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Price -->
                    <div class="border-t border-b py-4">
                        <div class="flex items-center space-x-4">
                            <span class="text-3xl font-bold text-gray-900">£<?php echo number_format($productDetails['price'], 2); ?></span>
                            <?php if ($productDetails['original_price'] && $productDetails['original_price'] > $productDetails['price']): ?>
                                <span class="text-xl text-gray-500 line-through">£<?php echo number_format($productDetails['original_price'], 2); ?></span>
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm font-medium">Sale</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">Excl. VAT</p>
                    </div>

                    <!-- Add to Cart -->
                    <div class="space-y-4">
                        <?php if ($productDetails['stock_status'] === 'out_of_stock'): ?>
                            <button class="w-full bg-gray-300 text-gray-500 py-3 px-6 rounded-lg cursor-not-allowed" disabled>
                                <i class="fas fa-clock mr-2"></i>
                                Out of Stock - Notify When Available
                            </button>
                        <?php else: ?>
                            <!-- Updated Add to Cart button to link to booking page -->
                            <a href="booking.php?product_id=<?php echo $productId; ?>" class="w-full bg-orange-600 text-white py-3 px-6 rounded-lg hover:bg-orange-700 transition-colors font-medium inline-block text-center">
                                <i class="fas fa-calendar-plus mr-2"></i>
                                Book This Product
                            </a>
                        <?php endif; ?>
                        <button class="w-full border border-gray-300 text-gray-700 py-3 px-6 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-heart mr-2"></i>
                            Add to Wishlist
                        </button>
                    </div>
                </div>
            </div>

            <!-- Technical Specifications -->
            <div class="border-t p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Technical Specifications</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="font-medium text-gray-700">Bandwidth:</span>
                            <span class="text-gray-900"><?php echo htmlspecialchars($productDetails['bandwidth']); ?></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="font-medium text-gray-700">Channels:</span>
                            <span class="text-gray-900"><?php echo $productDetails['channels']; ?></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="font-medium text-gray-700">Sample Rate:</span>
                            <span class="text-gray-900"><?php echo htmlspecialchars($productDetails['sample_rate']); ?></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="font-medium text-gray-700">Brand:</span>
                            <span class="text-gray-900"><?php echo htmlspecialchars($productDetails['brand_name']); ?></span>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="font-medium text-gray-700">Model:</span>
                            <span class="text-gray-900"><?php echo htmlspecialchars($productDetails['model']); ?></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="font-medium text-gray-700">SKU:</span>
                            <span class="text-gray-900"><?php echo htmlspecialchars($productDetails['sku']); ?></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="font-medium text-gray-700">Weight:</span>
                            <span class="text-gray-900">2.5 kg</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="font-medium text-gray-700">Warranty:</span>
                            <span class="text-gray-900">3 Years</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documentation -->
            <div class="border-t p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Documentation & Support</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="#" class="flex items-center p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-file-pdf text-red-600 text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-medium text-gray-900">Datasheet</h3>
                            <p class="text-sm text-gray-600">Technical specifications</p>
                        </div>
                    </a>
                    <a href="#" class="flex items-center p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-book text-blue-600 text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-medium text-gray-900">User Manual</h3>
                            <p class="text-sm text-gray-600">Operating instructions</p>
                        </div>
                    </a>
                    <a href="#" class="flex items-center p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-tools text-green-600 text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-medium text-gray-900">Software</h3>
                            <p class="text-sm text-gray-600">Drivers & utilities</p>
                        </div>
                    </a>
                </div>
            </div>
            
            <!-- Calender details -->
            <div class="border-t p-6">
            	<?=$calendar?>
	    </div>
	    
        </div>

        <!-- Back to Products -->
        <div class="mt-6">
            <a href="index.php" class="inline-flex items-center text-orange-600 hover:text-orange-700">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Oscilloscopes
            </a>
        </div>
    </div>
</body>
</html>
