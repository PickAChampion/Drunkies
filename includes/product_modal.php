<?php
/**
 * Product Modal
 * 
 * This file contains the modal dialog for showing product details
 * It is included in the header to be accessible throughout the site
 */
?>
<!-- Product Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalTitle">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="product-image">
                            <img src="" id="productModalImage" class="img-fluid" alt="Product Image">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4 id="productModalName"></h4>
                        <p class="text-muted" id="productModalBrand"></p>
                        <div class="price mb-3">
                            <h5 id="productModalPrice"></h5>
                        </div>
                        <div class="mb-3" id="productModalDescription"></div>
                        <div class="product-details mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <strong>Alcohol Content:</strong>
                                    <span id="productModalAlcohol"></span>
                                </div>
                                <div class="col-6">
                                    <strong>Volume:</strong>
                                    <span id="productModalVolume"></span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="input-group me-3" style="width: 130px;">
                                <button class="btn btn-outline-secondary qty-btn" id="decreaseQty" type="button">-</button>
                                <input type="number" class="form-control text-center" id="productModalQty" value="1" min="1">
                                <button class="btn btn-outline-secondary qty-btn" id="increaseQty" type="button">+</button>
                            </div>
                            <div class="stock-info" id="productModalStock"></div>
                        </div>
                        <div class="d-grid">
                            <button type="button" class="btn btn-primary" id="addToCartBtn">
                                <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize product modal functionality
document.addEventListener('DOMContentLoaded', function() {
    // This script will be implemented when needed
    // It will handle loading product data into the modal
    const quickViewModal = document.getElementById('quickViewModal');
    if (quickViewModal) {
        // Modal functionality will be added later
    }
});
</script> 