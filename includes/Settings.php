<?php
require_once dirname(__DIR__) . '/config/database.php';

class Settings {
    private $conn;
    private $table = 'settings';

    public function __construct() {
        try {
            $this->conn = getDB();
        } catch (Exception $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    // Get all settings
    public function getAllSettings() {
        try {
            $query = "SELECT * FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            $settings = [];
            foreach ($results as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            return $settings;
        } catch (Exception $e) {
            return [];
        }
    }

    // Get a specific setting
    public function getSetting($key, $default = null) {
        try {
            $query = "SELECT setting_value FROM " . $this->table . " WHERE setting_key = :key";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':key', $key);
            $stmt->execute();
            $result = $stmt->fetch();
            
            return $result ? $result['setting_value'] : $default;
        } catch (Exception $e) {
            return $default;
        }
    }

    // Set a setting
    public function setSetting($key, $value) {
        try {
            // Check if setting exists
            $query = "SELECT id FROM " . $this->table . " WHERE setting_key = :key";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':key', $key);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result) {
                // Update existing setting
                $query = "UPDATE " . $this->table . " SET setting_value = :value, updated_at = CURRENT_TIMESTAMP WHERE setting_key = :key";
            } else {
                // Insert new setting
                $query = "INSERT INTO " . $this->table . " (setting_key, setting_value, created_at, updated_at) VALUES (:key, :value, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':key', $key);
            $stmt->bindParam(':value', $value);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    // Update multiple settings
    public function updateSettings($settings) {
        try {
            $success = true;
            foreach ($settings as $key => $value) {
                if (!$this->setSetting($key, $value)) {
                    $success = false;
                }
            }
            return $success;
        } catch (Exception $e) {
            return false;
        }
    }

    // Get default settings
    public function getDefaultSettings() {
        return [
            'site_name' => 'TelieAcademy',
            'site_description' => 'Learn modern web development with comprehensive tutorials',
            'site_url' => 'http://localhost/TelieAcademy',
            'admin_email' => 'admin@telieacademy.com',
            'contact_email' => 'contact@telieacademy.com',
            'posts_per_page' => '10',
            'comments_enabled' => '1',
            'comments_moderation' => '1',
            'newsletter_enabled' => '1',
            'premium_content_enabled' => '1',
            'ads_enabled' => '0',
            'social_facebook' => '',
            'social_twitter' => '',
            'social_instagram' => '',
            'social_youtube' => '',
            'footer_text' => '© 2024 TelieAcademy. All rights reserved.',
            'theme_color' => '#007bff',
            'logo_text' => 'TelieAcademy',
            'meta_keywords' => 'web development, javascript, react, python, tutorials',
            'meta_description' => 'Learn modern web development with comprehensive tutorials on JavaScript, React, Python, and more.',
            'google_analytics' => '',
            'disqus_shortname' => '',
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => ''
        ];
    }

    // Initialize settings if table is empty
    public function initializeSettings() {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['count'] == 0) {
                $defaultSettings = $this->getDefaultSettings();
                return $this->updateSettings($defaultSettings);
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Get settings by category
    public function getSettingsByCategory($category) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE setting_key LIKE :category ORDER BY setting_key";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':category', $category . '%');
            $stmt->execute();
            
            $results = $stmt->fetchAll();
            $settings = [];
            foreach ($results as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            return $settings;
        } catch (Exception $e) {
            return [];
        }
    }

    // Delete a setting
    public function deleteSetting($key) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE setting_key = :key";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':key', $key);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    // Get settings statistics
    public function getSettingsStatistics() {
        try {
            $stats = [];
            
            // Total settings
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_settings'] = $result['total'];
            
            // Recently updated
            $query = "SELECT setting_key, updated_at FROM " . $this->table . " ORDER BY updated_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['recently_updated'] = $result ? $result['setting_key'] . ' (' . date('M j, Y', strtotime($result['updated_at'])) . ')' : 'No settings';
            
            return $stats;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
?> 