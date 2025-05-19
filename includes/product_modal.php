<?php
/**
 * Product Modal Component
 * Displays product details in a modal window
 */
?>
<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <img src="" alt="" class="img-fluid rounded product-image" id="modalProductImage">
                        <div class="mt-3">
                            <div class="rating">
                                <span class="stars"></span>
                                <span class="rating-count"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4 id="modalProductName"></h4>
                        <p class="text-muted" id="modalProductBrand"></p>
                        <p class="product-price h4 text-danger mb-3">₱<span id="modalProductPrice"></span></p>
                        <div class="mb-3">
                            <strong>Category:</strong> <span id="modalProductCategory"></span>
                        </div>
                        <div class="mb-3">
                            <strong>Alcohol Content:</strong> <span id="modalProductAlcohol"></span>%
                        </div>
                        <div class="mb-3">
                            <strong>Container:</strong> <span id="modalProductContainer"></span>
                        </div>
                        <div class="mb-3">
                            <strong>Volume:</strong> <span id="modalProductVolume"></span>
                        </div>
                        <p class="mb-4" id="modalProductDescription"></p>
                        
                        <?php if (isLoggedIn()): ?>
                        <form class="add-to-cart-form">
                            <input type="hidden" name="product_id" id="modalProductId">
                            <div class="input-group mb-3" style="max-width: 200px;">
                                <button class="btn btn-outline-secondary decrement" type="button">-</button>
                                <input type="number" class="form-control text-center quantity-input" name="quantity" value="1" min="1" max="99">
                                <button class="btn btn-outline-secondary increment" type="button">+</button>
                            </div>
                            <button type="submit" class="btn btn-primary">Add to Cart</button>
                        </form>
                        <?php else: ?>
                        <a href="login.php" class="btn btn-primary">Login to Purchase</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div class="reviews-section mt-5">
                    <h5>Reviews</h5>
                    <div id="reviewsList"></div>
                    
                    <?php if (isLoggedIn()): ?>
                    <div class="add-review-form mt-4">
                        <h6>Add Your Review</h6>
                        <form id="reviewForm">
                            <input type="hidden" name="product_id" id="reviewProductId">
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <div class="rating">
                                    <?php for($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" id="rating<?php echo $i; ?>">
                                    <label for="rating<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Comment</label>
                                <textarea class="form-control" name="comment" id="comment" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                    <?php else: ?>
                    <p class="mt-3"><a href="login.php">Login</a> to leave a review.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div> 