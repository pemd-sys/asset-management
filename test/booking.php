<?php
require_once 'config/database.php';
require_once 'classes/Product.php';
require_once 'includes/auth_check.php';
require_once 'includes/csrf.php';
require_once 'includes/validation.php';

// Check if user is authenticated
if (!$currentUser) {
    header('Location: login.php?message=session_expired');
    exit();
}

// Get and validate product ID from URL
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if ($product_id <= 0) {
    header('Location: index.php');
    exit;
}

// Use central database connection
$database = new Database();
$db = $database->getConnection();

// Get product details
$stmt = $db->prepare("
    SELECT p.*, b.name as brand_name 
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        // Validate and sanitize input
        $start_date = validateDate($_POST['start_date'] ?? '');
        $end_date = validateDate($_POST['end_date'] ?? '');
        $notes = sanitizeString($_POST['notes'] ?? '', 500);
        
        if (!$start_date || !$end_date) {
            $error = "Please enter valid dates.";
        } elseif (strtotime($start_date) >= strtotime($end_date)) {
            $error = "End date must be after start date.";
        } elseif (strtotime($start_date) < strtotime(date('Y-m-d'))) {
            $error = "Start date cannot be in the past.";
        } else {
            // Check if product is available for these dates
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM bookings 
                WHERE product_id = ? 
                AND status = 'active' 
                AND (
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date >= ? AND end_date <= ?)
                )
            ");
            $stmt->execute([$product_id, $start_date, $start_date, $end_date, $end_date, $start_date, $end_date]);

            if ($stmt->fetchColumn() > 0) {
                $error = "Product is not available for the selected dates.";
            } else {
                // Create booking
                $stmt = $db->prepare("
                    INSERT INTO bookings (user_id, product_id, start_date, end_date, notes) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$currentUser['id'], $product_id, $start_date, $end_date, $notes])) {
                    $success = "Booking created successfully!";
                } else {
                    $error = "Failed to create booking. Please try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Product - <?php echo htmlspecialchars($product['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">Book Product</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">Welcome, <?php echo htmlspecialchars($currentUser['username']); ?></span>
                        <a href="logout.php" class="text-sm text-red-600 hover:text-red-800">Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-4xl mx-auto py-8 px-4">
            <!-- Product Info -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex items-start space-x-6">
                    <img src="<?php echo htmlspecialchars($product['image_url'] ?: '/public/placeholder.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="w-32 h-32 object-cover rounded-lg">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h2>
                        <p class="text-lg text-gray-600"><?php echo htmlspecialchars($product['brand_name']); ?> - <?php echo htmlspecialchars($product['model']); ?></p>
                        <p class="text-sm text-gray-500 mt-2"><?php echo htmlspecialchars($product['description']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Select Booking Dates</h3>
                
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($success); ?>
                        <div class="mt-2">
                            <a href="index.php" class="text-blue-600 hover:text-blue-800">Return to catalog</a>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" 
                                   id="start_date" 
                                   name="start_date" 
                                   min="<?php echo date('Y-m-d'); ?>"
                                   required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" 
                                   id="end_date" 
                                   name="end_date" 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                   required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea id="notes" 
                                  name="notes" 
                                  rows="3" 
                                  maxlength="500"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Any special requirements or notes..."></textarea>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button type="submit" 
                                class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Book Product
                        </button>
                        <a href="product.php?id=<?php echo $product_id; ?>" 
                           class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Update end date minimum when start date changes
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = new Date(this.value);
            const endDate = document.getElementById('end_date');
            const nextDay = new Date(startDate);
            nextDay.setDate(nextDay.getDate() + 1);
            endDate.min = nextDay.toISOString().split('T')[0];
        });
    </script>
</body>
</html>
