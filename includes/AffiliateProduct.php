<?php
require_once dirname(__DIR__) . '/config/database.php';

class AffiliateProduct {
    private $conn;
    private $table = 'affiliate_products';

    public function __construct() {
        $this->conn = getDB();
    }

    // Get affiliate products for a post
    public function getProductsByPost($postId) {
        $query = "SELECT ap.*
                  FROM " . $this->table . " ap
                  INNER JOIN post_affiliate_products pap ON ap.id = pap.affiliate_product_id
                  WHERE pap.post_id = :post_id AND ap.is_active = TRUE
                  ORDER BY pap.position ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':post_id', $postId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get all active affiliate products
    public function getAllActiveProducts() {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = TRUE ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get products by category
    public function getProductsByCategory($category) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE category = :category AND is_active = TRUE 
                  ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Add product to post
    public function addProductToPost($postId, $productId, $position = 0) {
        $query = "INSERT INTO post_affiliate_products (post_id, affiliate_product_id, position) 
                  VALUES (:post_id, :product_id, :position)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':post_id', $postId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':position', $position);

        return $stmt->execute();
    }

    // Remove product from post
    public function removeProductFromPost($postId, $productId) {
        $query = "DELETE FROM post_affiliate_products 
                  WHERE post_id = :post_id AND affiliate_product_id = :product_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':post_id', $postId);
        $stmt->bindParam(':product_id', $productId);

        return $stmt->execute();
    }
}
?> 