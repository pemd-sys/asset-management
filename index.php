<?php
require_once 'config/database.php';
require_once 'classes/Product.php';
require_once 'classes/Brand.php';
require_once 'includes/auth_check.php'; // Add authentication check to protect catalog access

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize classes
$product = new Product($db);
$brand = new Brand($db);

// Get filters from URL parameters
$filters = [];
if (isset($_GET['brand']) && is_array($_GET['brand'])) {
    $filters['brand'] = $_GET['brand'];
}
if (isset($_GET['bandwidth']) && is_array($_GET['bandwidth'])) {
    $filters['bandwidth'] = $_GET['bandwidth'];
}
if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $filters['min_price'] = floatval($_GET['min_price']);
}
if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $filters['max_price'] = floatval($_GET['max_price']);
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
if (isset($_GET['sort'])) {
    $filters['sort'] = $_GET['sort'];
}

// Get products and brands
$products = $product->getAll($filters);
$totalProducts = $product->getCount($filters);
$brands = $brand->getBrandCounts();

// Get unique bandwidth values for filter
$bandwidthOptions = ['50 MHz', '100 MHz', '200 MHz', '500 MHz', '1 GHz'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oscilloscopes - Test & Measurement | Electronics Catalog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-orange-600">ElectroStore</h1>
                    <nav class="hidden md:flex space-x-6">
                        <a href="#" class="text-gray-600 hover:text-orange-600">Products</a>
                        <a href="#" class="text-gray-600 hover:text-orange-600">Solutions</a>
                        <a href="#" class="text-gray-600 hover:text-orange-600">Support</a>
                    </nav>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <form method="GET" class="flex">
                            <input type="search" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" 
                                   placeholder="Search products..." class="pl-10 pr-4 py-2 border rounded-lg w-64">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <!-- Preserve existing filters when searching -->
                            <?php foreach($_GET as $key => $value): ?>
                                <?php if ($key !== 'search' && !is_array($value)): ?>
                                    <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                                <?php elseif ($key !== 'search' && is_array($value)): ?>
                                    <?php foreach($value as $item): ?>
                                        <input type="hidden" name="<?php echo htmlspecialchars($key); ?>[]" value="<?php echo htmlspecialchars($item); ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </form>
                    </div>
                    <!-- Add user menu with logout functionality -->
                    <div class="flex items-center space-x-4">
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-gray-600 hover:text-orange-600">
                                <i class="fas fa-user-circle text-xl"></i>
                                <span class="hidden md:inline"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                                <i class="fas fa-chevron-down text-sm"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <div class="py-2">
                                    <div class="px-4 py-2 text-sm text-gray-500 border-b">
                                        Signed in as <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong>
                                        <?php if ($currentUser['role'] === 'admin'): ?>
                                            <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded ml-2">Admin</span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-user mr-2"></i>Profile
                                    </a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-cog mr-2"></i>Settings
                                    </a>
                                    <?php if ($currentUser['role'] === 'admin'): ?>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-tools mr-2"></i>Admin Panel
                                        </a>
                                    <?php endif; ?>
                                    <div class="border-t my-1"></div>
                                    <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Sign Out
                                    </a>
                                </div>
                            </div>
                        </div>
                        <i class="fas fa-shopping-cart text-gray-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="max-w-7xl mx-auto px-4 py-3">
        <nav class="text-sm text-gray-600">
            <a href="#" class="hover:text-orange-600">Home</a>
            <span class="mx-2">/</span>
            <a href="#" class="hover:text-orange-600">Test & Measurement</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900">Oscilloscopes</span>
        </nav>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex gap-6">
            <!-- Sidebar Filters -->
            <aside class="w-64 bg-white rounded-lg shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Filter by</h3>
                
                <form method="GET" id="filterForm">
                    <!-- Preserve search term in filters -->
                    <?php if (isset($_GET['search'])): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                    <?php endif; ?>
                    
                    <div class="space-y-6">
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Brand</h4>
                            <div class="space-y-2">
                                <?php foreach($brands as $brandItem): ?>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="brand[]" value="<?php echo htmlspecialchars($brandItem['name']); ?>" 
                                               class="rounded text-orange-600"
                                               <?php echo (isset($_GET['brand']) && in_array($brandItem['name'], $_GET['brand'])) ? 'checked' : ''; ?>
                                               onchange="document.getElementById('filterForm').submit();">
                                        <span class="ml-2 text-sm"><?php echo htmlspecialchars($brandItem['name']); ?> (<?php echo $brandItem['product_count']; ?>)</span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Bandwidth</h4>
                            <div class="space-y-2">
                                <?php foreach($bandwidthOptions as $bw): ?>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="bandwidth[]" value="<?php echo htmlspecialchars($bw); ?>" 
                                               class="rounded text-orange-600"
                                               <?php echo (isset($_GET['bandwidth']) && in_array($bw, $_GET['bandwidth'])) ? 'checked' : ''; ?>
                                               onchange="document.getElementById('filterForm').submit();">
                                        <span class="ml-2 text-sm"><?php echo htmlspecialchars($bw); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Price Range</h4>
                            <div class="flex space-x-2">
                                <input type="number" name="min_price" placeholder="Min" 
                                       value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>"
                                       class="w-20 px-2 py-1 border rounded text-sm"
                                       onchange="document.getElementById('filterForm').submit();">
                                <span class="text-gray-500">-</span>
                                <input type="number" name="max_price" placeholder="Max" 
                                       value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>"
                                       class="w-20 px-2 py-1 border rounded text-sm"
                                       onchange="document.getElementById('filterForm').submit();">
                            </div>
                        </div>
                    </div>
                </form>
            </aside>

            <!-- Main Content -->
            <main class="flex-1">
                <!-- Results Header -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Oscilloscopes</h2>
                            <p class="text-gray-600"><?php echo $totalProducts; ?> products found</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-600">Sort by:</span>
                            <form method="GET" class="inline">
                                <!-- Preserve all existing filters when sorting -->
                                <?php foreach($_GET as $key => $value): ?>
                                    <?php if ($key !== 'sort' && !is_array($value)): ?>
                                        <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                                    <?php elseif ($key !== 'sort' && is_array($value)): ?>
                                        <?php foreach($value as $item): ?>
                                            <input type="hidden" name="<?php echo htmlspecialchars($key); ?>[]" value="<?php echo htmlspecialchars($item); ?>">
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <select name="sort" class="border rounded px-3 py-1" onchange="this.form.submit();">
                                    <option value="price_low" <?php echo ($_GET['sort'] ?? '') === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo ($_GET['sort'] ?? '') === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="brand" <?php echo ($_GET['sort'] ?? '') === 'brand' ? 'selected' : ''; ?>>Brand A-Z</option>
                                    <option value="popular" <?php echo ($_GET['sort'] ?? '') === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                                </select>
                            </form>
                            <div class="flex border rounded">
                                <button class="px-3 py-1 bg-orange-100 text-orange-600">
                                    <i class="fas fa-th-large"></i>
                                </button>
                                <button class="px-3 py-1 text-gray-600">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach($products as $productItem): ?>
                        <!-- Dynamic product cards from database -->
                        <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                            <div class="p-4">
                                <div class="aspect-square bg-gray-100 rounded-lg mb-4 flex items-center justify-center">
                                    <img src="<?php echo htmlspecialchars($productItem['image_url'] ?: '/placeholder.svg?height=200&width=200'); ?>" 
                                         alt="<?php echo htmlspecialchars($productItem['name']); ?>" 
                                         class="max-w-full max-h-full object-contain">
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <?php
                                        $stockClass = '';
                                        $stockText = '';
                                        switch($productItem['stock_status']) {
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
                                        <span class="<?php echo $stockClass; ?> text-xs px-2 py-1 rounded"><?php echo $stockText; ?></span>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <span class="text-sm text-gray-600"><?php echo number_format($productItem['rating'], 1); ?> (<?php echo $productItem['review_count']; ?>)</span>
                                    </div>
                                    <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($productItem['name']); ?></h3>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($productItem['description']); ?></p>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-lg font-bold text-gray-900">£<?php echo number_format($productItem['price'], 2); ?></span>
                                            <?php if ($productItem['original_price'] && $productItem['original_price'] > $productItem['price']): ?>
                                                <span class="text-sm text-gray-500 line-through ml-2">£<?php echo number_format($productItem['original_price'], 2); ?></span>
                                            <?php endif; ?>
                                            <?php if ($productItem['is_on_sale']): ?>
                                                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded ml-2">Sale</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($productItem['stock_status'] === 'out_of_stock'): ?>
                                            <button class="bg-gray-300 text-gray-500 px-4 py-2 rounded cursor-not-allowed" disabled>
                                                <i class="fas fa-clock mr-1"></i>
                                                Notify
                                            </button>
                                        <?php else: ?>
                                            <button class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 transition-colors">
                                                <i class="fas fa-cart-plus mr-1"></i>
                                                Add
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs text-gray-500 space-y-1">
                                        <div class="flex justify-between">
                                            <span>Bandwidth:</span>
                                            <span><?php echo htmlspecialchars($productItem['bandwidth']); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Channels:</span>
                                            <span><?php echo $productItem['channels']; ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Sample Rate:</span>
                                            <span><?php echo htmlspecialchars($productItem['sample_rate']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div class="mt-8 flex items-center justify-center space-x-2">
                    <button class="px-3 py-2 border rounded text-gray-600 hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="px-3 py-2 bg-orange-600 text-white rounded">1</button>
                    <button class="px-3 py-2 border rounded text-gray-600 hover:bg-gray-50">2</button>
                    <button class="px-3 py-2 border rounded text-gray-600 hover:bg-gray-50">3</button>
                    <span class="px-3 py-2 text-gray-500">...</span>
                    <button class="px-3 py-2 border rounded text-gray-600 hover:bg-gray-50">12</button>
                    <button class="px-3 py-2 border rounded text-gray-600 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </main>
        </div>
    </div>

    <!-- Add JavaScript for dynamic filtering -->
    <script src="js/filter.js"></script>
</body>
</html>
