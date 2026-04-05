class ProductFilter {
  constructor() {
    this.initializeEventListeners()
  }

  initializeEventListeners() {
    // Filter checkboxes
    document.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
      checkbox.addEventListener("change", () => this.applyFilters())
    })

    // Price range inputs
    document.querySelectorAll('input[name="min_price"], input[name="max_price"]').forEach((input) => {
      input.addEventListener("change", () => this.applyFilters())
    })

    // Sort dropdown
    const sortSelect = document.querySelector('select[name="sort"]')
    if (sortSelect) {
      sortSelect.addEventListener("change", () => this.applyFilters())
    }

    // Search form
    const searchForm = document.querySelector("form")
    if (searchForm) {
      searchForm.addEventListener("submit", (e) => {
        e.preventDefault()
        this.applyFilters()
      })
    }
  }

  collectFilters() {
    const filters = {}

    // Collect brand filters
    const brandCheckboxes = document.querySelectorAll('input[name="brand[]"]:checked')
    if (brandCheckboxes.length > 0) {
      filters.brand = Array.from(brandCheckboxes).map((cb) => cb.value)
    }

    // Collect bandwidth filters
    const bandwidthCheckboxes = document.querySelectorAll('input[name="bandwidth[]"]:checked')
    if (bandwidthCheckboxes.length > 0) {
      filters.bandwidth = Array.from(bandwidthCheckboxes).map((cb) => cb.value)
    }

    // Collect price range
    const minPrice = document.querySelector('input[name="min_price"]').value
    const maxPrice = document.querySelector('input[name="max_price"]').value
    if (minPrice) filters.min_price = minPrice
    if (maxPrice) filters.max_price = maxPrice

    // Collect search term
    const searchInput = document.querySelector('input[name="search"]')
    if (searchInput && searchInput.value) {
      filters.search = searchInput.value
    }

    // Collect sort option
    const sortSelect = document.querySelector('select[name="sort"]')
    if (sortSelect && sortSelect.value) {
      filters.sort = sortSelect.value
    }

    return filters
  }

  async applyFilters() {
    const filters = this.collectFilters()

    // Show loading state
    this.showLoading()

    try {
      const formData = new FormData()
      Object.keys(filters).forEach((key) => {
        if (Array.isArray(filters[key])) {
          filters[key].forEach((value) => formData.append(key + "[]", value))
        } else {
          formData.append(key, filters[key])
        }
      })

      const response = await fetch("ajax/filter_products.php", {
        method: "POST",
        body: formData,
      })

      const data = await response.json()

      if (data.success) {
        this.updateProductGrid(data.products)
        this.updateProductCount(data.total)
        this.updateURL(filters)
      }
    } catch (error) {
      console.error("Filter error:", error)
    } finally {
      this.hideLoading()
    }
  }

  updateProductGrid(products) {
    const grid = document.querySelector(".grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-3")

    if (products.length === 0) {
      grid.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                    <p class="text-gray-500">Try adjusting your filters or search terms</p>
                </div>
            `
      return
    }

    grid.innerHTML = products.map((product) => this.createProductCard(product)).join("")
  }

  createProductCard(product) {
    const stockClass = {
      in_stock: "bg-green-100 text-green-800",
      low_stock: "bg-yellow-100 text-yellow-800",
      out_of_stock: "bg-red-100 text-red-800",
    }[product.stock_status]

    const stockText = {
      in_stock: "In Stock",
      low_stock: "Low Stock",
      out_of_stock: "Out of Stock",
    }[product.stock_status]

    const addToCartButton =
      product.stock_status === "out_of_stock"
        ? `<button class="bg-gray-300 text-gray-500 px-4 py-2 rounded cursor-not-allowed" disabled>
                 <i class="fas fa-clock mr-1"></i>Notify
               </button>`
        : `<button class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 transition-colors">
                 <i class="fas fa-cart-plus mr-1"></i>Add
               </button>`

    const originalPriceHtml =
      product.original_price && product.original_price > product.price
        ? `<span class="text-sm text-gray-500 line-through ml-2">£${Number.parseFloat(product.original_price).toFixed(2)}</span>`
        : ""

    const saleTag =
      product.is_on_sale == 1 ? `<span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded ml-2">Sale</span>` : ""

    return `
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="p-4">
                    <div class="aspect-square bg-gray-100 rounded-lg mb-4 flex items-center justify-center">
                        <img src="${product.image_url || "/placeholder.svg?height=200&width=200"}" 
                             alt="${product.name}" 
                             class="max-w-full max-h-full object-contain">
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2">
                            <span class="${stockClass} text-xs px-2 py-1 rounded">${stockText}</span>
                            <i class="fas fa-star text-yellow-400"></i>
                            <span class="text-sm text-gray-600">${Number.parseFloat(product.rating).toFixed(1)} (${product.review_count})</span>
                        </div>
                        <h3 class="font-semibold text-gray-900">${product.name}</h3>
                        <p class="text-sm text-gray-600">${product.description}</p>
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-lg font-bold text-gray-900">£${Number.parseFloat(product.price).toFixed(2)}</span>
                                ${originalPriceHtml}
                                ${saleTag}
                            </div>
                            ${addToCartButton}
                        </div>
                        <div class="text-xs text-gray-500 space-y-1">
                            <div class="flex justify-between">
                                <span>Bandwidth:</span>
                                <span>${product.bandwidth}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Channels:</span>
                                <span>${product.channels}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Sample Rate:</span>
                                <span>${product.sample_rate}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `
  }

  updateProductCount(total) {
    const countElement = document.querySelector(".text-gray-600")
    if (countElement && countElement.textContent.includes("products found")) {
      countElement.textContent = `${total} products found`
    }
  }

  updateURL(filters) {
    const url = new URL(window.location)

    // Clear existing filter parameters
    url.searchParams.delete("brand")
    url.searchParams.delete("bandwidth")
    url.searchParams.delete("min_price")
    url.searchParams.delete("max_price")
    url.searchParams.delete("search")
    url.searchParams.delete("sort")

    // Add new filter parameters
    Object.keys(filters).forEach((key) => {
      if (Array.isArray(filters[key])) {
        filters[key].forEach((value) => url.searchParams.append(key + "[]", value))
      } else {
        url.searchParams.set(key, filters[key])
      }
    })

    window.history.pushState({}, "", url)
  }

  showLoading() {
    const grid = document.querySelector(".grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-3")
    grid.style.opacity = "0.5"
    grid.style.pointerEvents = "none"
  }

  hideLoading() {
    const grid = document.querySelector(".grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-3")
    grid.style.opacity = "1"
    grid.style.pointerEvents = "auto"
  }
}

// Initialize filter functionality when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  new ProductFilter()
})
