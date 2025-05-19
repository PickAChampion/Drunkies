<?php
require_once 'functions.php';
require_once 'upload_handler.php';

class ImageHandler {
    private $db;
    private $imageUploader;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->imageUploader = new ImageUploader();
    }
    
    /**
     * Handle product image upload and database update
     */
    public function handleProductImage($file, $product_id) {
        try {
            // Upload image
            $image_path = $this->imageUploader->uploadProductImage($file);
            
            // Update product in database
            $stmt = $this->db->prepare("UPDATE products SET image = ? WHERE id = ?");
            $stmt->bind_param("si", $image_path, $product_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update product image in database");
            }
            
            // Get old image path
            $stmt = $this->db->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $old_image = $result->fetch_assoc()['image'];
            
            // Delete old image if it exists and is not the default
            if ($old_image && $old_image !== 'assets/images/no-image.svg') {
                $this->imageUploader->deleteImage($old_image);
            }
            
            return $image_path;
        } catch (Exception $e) {
            // If something goes wrong, try to delete the uploaded image
            if (isset($image_path)) {
                $this->imageUploader->deleteImage($image_path);
            }
            throw $e;
        }
    }
    
    /**
     * Get product image details
     */
    public function getProductImage($product_id) {
        $stmt = $this->db->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['image'];
    }
} 