// Update cart count
function updateCartCount() {
    $.ajax({
        url: 'ajax/cart_count.php',
        method: 'GET',
        success: function(response) {
            $('#cart-count').text(response.count);
        }
    });
}

// Add to cart
function addToCart(productId, quantity = 1) {
    $.ajax({
        url: 'ajax/add_to_cart.php',
        method: 'POST',
        data: {
            product_id: productId,
            quantity: quantity
        },
        success: function(response) {
            if (response.success) {
                showAlert('Product added to cart successfully!', 'success');
                updateCartCount();
            } else {
                showAlert(response.message || 'Error adding product to cart', 'danger');
            }
        }
    });
}

// Remove from cart
function removeFromCart(productId) {
    if (confirm('Are you sure you want to remove this item from cart?')) {
        $.ajax({
            url: 'ajax/remove_from_cart.php',
            method: 'POST',
            data: {
                product_id: productId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    showAlert(response.message || 'Error removing product from cart', 'danger');
                }
            }
        });
    }
}

// Update cart quantity
function updateCartQuantity(productId, quantity) {
    $.ajax({
        url: 'ajax/update_cart.php',
        method: 'POST',
        data: {
            product_id: productId,
            quantity: quantity
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                showAlert(response.message || 'Error updating cart', 'danger');
            }
        }
    });
}

// Show alert message
function showAlert(message, type = 'success') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const alertContainer = document.createElement('div');
    alertContainer.innerHTML = alertHtml;
    document.querySelector('.container').insertBefore(alertContainer.firstChild, document.querySelector('.container').firstChild);
    
    // Auto dismiss after 3 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 3000);
}

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Product Modal Functions
    function showProductModal(productId) {
        fetch(`ajax/get_product.php?id=${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const product = data.product;
                    document.getElementById('modalProductImage').src = product.image_url;
                    document.getElementById('modalProductName').textContent = product.name;
                    document.getElementById('modalProductBrand').textContent = product.brand;
                    document.getElementById('modalProductPrice').textContent = parseFloat(product.price).toFixed(2);
                    document.getElementById('modalProductCategory').textContent = product.category;
                    document.getElementById('modalProductAlcohol').textContent = product.alcohol_content;
                    document.getElementById('modalProductContainer').textContent = product.container_type;
                    document.getElementById('modalProductVolume').textContent = product.volume;
                    document.getElementById('modalProductDescription').textContent = product.description;
                    document.getElementById('modalProductId').value = product.id;
                    document.getElementById('reviewProductId').value = product.id;

                    // Update rating stars
                    const starsContainer = document.querySelector('.rating .stars');
                    starsContainer.innerHTML = '';
                    for (let i = 0; i < 5; i++) {
                        const star = document.createElement('i');
                        star.className = `fas fa-star ${i < product.rating ? 'text-warning' : 'text-muted'}`;
                        starsContainer.appendChild(star);
                    }

                    // Load reviews
                    loadProductReviews(productId);

                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('productModal'));
                    modal.show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to load product details',
                    icon: 'error'
                });
            });
    }

    // Load product reviews
    function loadProductReviews(productId) {
        fetch(`ajax/get_reviews.php?product_id=${productId}`)
            .then(response => response.json())
            .then(data => {
                const reviewsList = document.getElementById('reviewsList');
                reviewsList.innerHTML = '';

                if (data.success && data.reviews.length > 0) {
                    data.reviews.forEach(review => {
                        const reviewElement = document.createElement('div');
                        reviewElement.className = 'review-item mb-3 p-3 border rounded';
                        reviewElement.innerHTML = `
                            <div class="d-flex justify-content-between">
                                <strong>${review.username}</strong>
                                <div class="rating">
                                    ${Array(5).fill(0).map((_, i) => 
                                        `<i class="fas fa-star ${i < review.rating ? 'text-warning' : 'text-muted'}"></i>`
                                    ).join('')}
                                </div>
                            </div>
                            <p class="mb-1">${review.comment}</p>
                            <small class="text-muted">${review.created_at}</small>
                        `;
                        reviewsList.appendChild(reviewElement);
                    });
                } else {
                    reviewsList.innerHTML = '<p class="text-muted">No reviews yet.</p>';
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Handle view product buttons
    document.querySelectorAll('.view-product').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            showProductModal(productId);
        });
    });

    // Handle review form submission
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('ajax/add_review.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success',
                        text: 'Review added successfully',
                        icon: 'success'
                    });
                    loadProductReviews(formData.get('product_id'));
                    this.reset();
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Failed to add review',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to add review',
                    icon: 'error'
                });
            });
        });
    }

    // Handle login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('ajax/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Login successful',
                        icon: 'success'
                    }).then(() => {
                        window.location.href = data.redirect || 'index.php';
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Login failed',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Login failed',
                    icon: 'error'
                });
            });
        });
    }

    // Handle registration form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('ajax/register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Registration successful',
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Go to Login',
                        cancelButtonText: 'Stay on Page'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'login.php';
                        } else {
                            this.reset();
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Registration failed',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Registration failed',
                    icon: 'error'
                });
            });
        });
    }

    // Add to cart functionality
    const addToCartForms = document.querySelectorAll('.add-to-cart-form');
    addToCartForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('ajax/add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success',
                        text: 'Product added to cart!',
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'View Cart',
                        cancelButtonText: 'Continue Shopping'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'cart.php';
                        }
                    });
                    
                    // Update cart count
                    const cartCount = document.querySelector('#cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cartCount;
                    }
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Failed to add product to cart',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to add product to cart',
                    icon: 'error'
                });
            });
        });
    });

    // Quantity increment/decrement
    document.querySelectorAll('.quantity-input').forEach(input => {
        const decrementBtn = input.parentElement.querySelector('.decrement');
        const incrementBtn = input.parentElement.querySelector('.increment');

        if (decrementBtn) {
            decrementBtn.addEventListener('click', () => {
                let value = parseInt(input.value);
                if (value > 1) {
                    input.value = value - 1;
                }
            });
        }

        if (incrementBtn) {
            incrementBtn.addEventListener('click', () => {
                let value = parseInt(input.value);
                let max = parseInt(input.getAttribute('max') || 99);
                if (value < max) {
                    input.value = value + 1;
                }
            });
        }
    });

    // Update cart count on page load
    updateCartCount();
}); 