<?php
require_once 'functions.php';

class ImageUploader {
    private $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    private $max_size = 5242880; // 5MB
    
    /**
     * Generic image upload function
     */
    private function uploadImage($file, $target_dir) {
        // Validate file
        $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $this->allowed_types)) {
            throw new Exception("Invalid file type. Allowed types: " . implode(', ', $this->allowed_types));
        }
        
        if ($file["size"] > $this->max_size) {
            throw new Exception("File is too large. Maximum size is 5MB.");
        }
        
        // Generate unique filename
        $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        // Upload file
        if (move_uploaded_file($file["tmp_name"], "../" . $target_file)) {
            return $target_file;
        }
        
        throw new Exception("Failed to upload file.");
    }
    
    /**
     * Upload product image
     */
    public function uploadProductImage($file) {
        return $this->uploadImage($file, "assets/images/products/");
    }
    
    /**
     * Upload brand image
     */
    public function uploadBrandImage($file) {
        return $this->uploadImage($file, "assets/images/brands/");
    }
    
    /**
     * Upload category image
     */
    public function uploadCategoryImage($file) {
        return $this->uploadImage($file, "assets/images/categories/");
    }
    
    /**
     * Delete image file
     */
    public function deleteImage($file_path) {
        if (file_exists("../" . $file_path) && $file_path !== 'assets/images/no-image.svg') {
            return unlink("../" . $file_path);
        }
        return false;
    }
} 