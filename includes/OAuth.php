<?php
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/oauth.php';

class OAuth {
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = getDB();
        } catch (Exception $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    // LinkedIn OAuth Methods
    public function handleLinkedInCallback($code, $state) {
        try {
            // Verify state parameter
            if (!verifyOAuthState($state)) {
                return ['success' => false, 'message' => 'Invalid state parameter'];
            }
            
            // Exchange code for access token
            $tokenData = $this->getLinkedInAccessToken($code);
            if (!$tokenData['success']) {
                return $tokenData;
            }
            
            // Get user profile from LinkedIn
            $profileData = $this->getLinkedInProfile($tokenData['access_token']);
            if (!$profileData['success']) {
                return $profileData;
            }
            
            // Create or update user
            $userData = $this->createOrUpdateLinkedInUser($profileData['profile']);
            
            return $userData;
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'LinkedIn authentication failed: ' . $e->getMessage()];
        }
    }
    
    private function getLinkedInAccessToken($code) {
        $url = 'https://www.linkedin.com/oauth/v2/accessToken';
        $data = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => LINKEDIN_REDIRECT_URI,
            'client_id' => LINKEDIN_CLIENT_ID,
            'client_secret' => LINKEDIN_CLIENT_SECRET
        ];
        
        $response = $this->makeHttpRequest($url, 'POST', $data);
        
        if ($response['success']) {
            $tokenData = json_decode($response['data'], true);
            if (isset($tokenData['access_token'])) {
                return [
                    'success' => true,
                    'access_token' => $tokenData['access_token'],
                    'expires_in' => $tokenData['expires_in'] ?? null
                ];
            }
        }
        
        return ['success' => false, 'message' => 'Failed to get access token'];
    }
    
    private function getLinkedInProfile($accessToken) {
        // Get basic profile
        $profileUrl = 'https://api.linkedin.com/v2/me';
        $profileResponse = $this->makeHttpRequest($profileUrl, 'GET', [], [
            'Authorization: Bearer ' . $accessToken,
            'X-Restli-Protocol-Version: 2.0.0'
        ]);
        
        if (!$profileResponse['success']) {
            return ['success' => false, 'message' => 'Failed to get LinkedIn profile'];
        }
        
        $profile = json_decode($profileResponse['data'], true);
        
        // Get email address
        $emailUrl = 'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))';
        $emailResponse = $this->makeHttpRequest($emailUrl, 'GET', [], [
            'Authorization: Bearer ' . $accessToken,
            'X-Restli-Protocol-Version: 2.0.0'
        ]);
        
        $email = null;
        if ($emailResponse['success']) {
            $emailData = json_decode($emailResponse['data'], true);
            if (isset($emailData['elements'][0]['handle~']['emailAddress'])) {
                $email = $emailData['elements'][0]['handle~']['emailAddress'];
            }
        }
        
        return [
            'success' => true,
            'profile' => [
                'id' => $profile['id'],
                'firstName' => $profile['localizedFirstName'] ?? '',
                'lastName' => $profile['localizedLastName'] ?? '',
                'email' => $email,
                'profilePicture' => $profile['profilePicture'] ?? null
            ]
        ];
    }
    
    private function createOrUpdateLinkedInUser($profile) {
        try {
            // Check if user exists by LinkedIn ID
            $query = "SELECT * FROM users WHERE oauth_provider = 'linkedin' AND oauth_id = :oauth_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':oauth_id', $profile['id']);
            $stmt->execute();
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                // Update existing user
                return $this->updateLinkedInUser($existingUser['id'], $profile);
            } else {
                // Check if user exists by email
                if ($profile['email']) {
                    $query = "SELECT * FROM users WHERE email = :email";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(':email', $profile['email']);
                    $stmt->execute();
                    $emailUser = $stmt->fetch();
                    
                    if ($emailUser) {
                        // Link existing email user to LinkedIn
                        return $this->linkLinkedInToExistingUser($emailUser['id'], $profile);
                    }
                }
                
                // Create new user
                return $this->createLinkedInUser($profile);
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create/update user: ' . $e->getMessage()];
        }
    }
    
    private function createLinkedInUser($profile) {
        try {
            $username = $this->generateUniqueUsername($profile['firstName'], $profile['lastName']);
            $email = $profile['email'] ?? $this->generateLinkedInEmail($profile['id']);
            
            $query = "INSERT INTO users (username, email, oauth_provider, oauth_id, first_name, last_name, 
                     profile_picture, email_verified, is_active, created_at) 
                     VALUES (:username, :email, 'linkedin', :oauth_id, :first_name, :last_name, 
                     :profile_picture, TRUE, TRUE, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':oauth_id', $profile['id']);
            $stmt->bindParam(':first_name', $profile['firstName']);
            $stmt->bindParam(':last_name', $profile['lastName']);
            $stmt->bindParam(':profile_picture', $profile['profilePicture']);
            
            if ($stmt->execute()) {
                $userId = $this->conn->lastInsertId();
                return $this->loginLinkedInUser($userId);
            }
            
            return ['success' => false, 'message' => 'Failed to create user'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create user: ' . $e->getMessage()];
        }
    }
    
    private function updateLinkedInUser($userId, $profile) {
        try {
            $current = $this->getCurrentProfilePicture($userId);
            $shouldUpdatePicture = empty($current) || $this->isExternalProfilePicture($current);

            if ($shouldUpdatePicture) {
            $query = "UPDATE users SET 
                     first_name = :first_name, 
                     last_name = :last_name, 
                     profile_picture = :profile_picture,
                     last_login = NOW(),
                     login_count = login_count + 1
                     WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $profile['firstName']);
            $stmt->bindParam(':last_name', $profile['lastName']);
            $stmt->bindParam(':profile_picture', $profile['profilePicture']);
            $stmt->bindParam(':user_id', $userId);
            } else {
                $query = "UPDATE users SET 
                         first_name = :first_name, 
                         last_name = :last_name, 
                         last_login = NOW(),
                         login_count = login_count + 1
                         WHERE id = :user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':first_name', $profile['firstName']);
                $stmt->bindParam(':last_name', $profile['lastName']);
                $stmt->bindParam(':user_id', $userId);
            }
            
            if ($stmt->execute()) {
                return $this->loginLinkedInUser($userId);
            }
            
            return ['success' => false, 'message' => 'Failed to update user'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update user: ' . $e->getMessage()];
        }
    }
    
    private function linkLinkedInToExistingUser($userId, $profile) {
        try {
            $current = $this->getCurrentProfilePicture($userId);
            $shouldUpdatePicture = empty($current) || $this->isExternalProfilePicture($current);

            if ($shouldUpdatePicture) {
            $query = "UPDATE users SET 
                     oauth_provider = 'linkedin', 
                     oauth_id = :oauth_id,
                     first_name = :first_name, 
                     last_name = :last_name, 
                     profile_picture = :profile_picture,
                     last_login = NOW(),
                     login_count = login_count + 1
                     WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':oauth_id', $profile['id']);
            $stmt->bindParam(':first_name', $profile['firstName']);
            $stmt->bindParam(':last_name', $profile['lastName']);
            $stmt->bindParam(':profile_picture', $profile['profilePicture']);
            $stmt->bindParam(':user_id', $userId);
            } else {
                $query = "UPDATE users SET 
                         oauth_provider = 'linkedin', 
                         oauth_id = :oauth_id,
                         first_name = :first_name, 
                         last_name = :last_name, 
                         last_login = NOW(),
                         login_count = login_count + 1
                         WHERE id = :user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':oauth_id', $profile['id']);
                $stmt->bindParam(':first_name', $profile['firstName']);
                $stmt->bindParam(':last_name', $profile['lastName']);
                $stmt->bindParam(':user_id', $userId);
            }
            
            if ($stmt->execute()) {
                return $this->loginLinkedInUser($userId);
            }
            
            return ['success' => false, 'message' => 'Failed to link LinkedIn account'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to link LinkedIn account: ' . $e->getMessage()];
        }
    }
    
    private function loginLinkedInUser($userId) {
        try {
            // Get user data
            $query = "SELECT * FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $user = $stmt->fetch();
            
            if ($user) {
                // Start session if not already started
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                
                // Set session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_premium'] = $user['is_premium'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                return [
                    'success' => true,
                    'message' => 'LinkedIn login successful',
                    'user' => $user
                ];
            }
            
            return ['success' => false, 'message' => 'User not found'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }
    
    // Google OAuth Methods
    public function handleGoogleCallback($code, $state) {
        try {
            // Verify state parameter
            if (!verifyOAuthState($state)) {
                return ['success' => false, 'message' => 'Invalid state parameter'];
            }
            
            // Exchange code for access token
            $tokenData = $this->getGoogleAccessToken($code);
            if (!$tokenData['success']) {
                return $tokenData;
            }
            
            // Get user profile from Google
            $profileData = $this->getGoogleProfile($tokenData['access_token']);
            if (!$profileData['success']) {
                return $profileData;
            }
            
            // Create or update user
            $userData = $this->createOrUpdateGoogleUser($profileData['profile']);
            
            return $userData;
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Google authentication failed: ' . $e->getMessage()];
        }
    }
    
    private function getGoogleAccessToken($code) {
        $url = 'https://oauth2.googleapis.com/token';
        $data = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => GOOGLE_REDIRECT_URI,
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET
        ];
        
        $response = $this->makeHttpRequest($url, 'POST', $data);
        
        if ($response['success']) {
            $tokenData = json_decode($response['data'], true);
            if (isset($tokenData['access_token'])) {
                return [
                    'success' => true,
                    'access_token' => $tokenData['access_token'],
                    'expires_in' => $tokenData['expires_in'] ?? null
                ];
            }
        }
        
        return ['success' => false, 'message' => 'Failed to get access token'];
    }
    
    private function getGoogleProfile($accessToken) {
        // Use the Google+ API endpoint for user info (which includes profile picture)
        $profileUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
        $profileResponse = $this->makeHttpRequest($profileUrl, 'GET', [], [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json'
        ]);
        
        if (!$profileResponse['success']) {
            error_log('Google profile fetch failed: ' . $profileResponse['message']);
            return ['success' => false, 'message' => 'Failed to get Google profile'];
        }
        
        $profile = json_decode($profileResponse['data'], true);
        
        // Debug logging
        error_log('Google profile response: ' . print_r($profile, true));
        
        // Ensure picture URL is properly formatted
        if (isset($profile['picture'])) {
            // Remove any size parameters to get the full resolution image
            $pictureUrl = $profile['picture'];
            $pictureUrl = preg_replace('/\?sz=\d+/', '', $pictureUrl);
            $pictureUrl = preg_replace('/\?v=\d+/', '', $pictureUrl);
            $profile['picture'] = $pictureUrl;
            error_log('Google profile picture URL: ' . $pictureUrl);
        } else {
            error_log('Google profile picture not found in response');
        }
        
        return [
            'success' => true,
            'profile' => $profile
        ];
    }
    
    private function createOrUpdateGoogleUser($profile) {
        try {
            $googleId = $profile['id'];
            $email = $profile['email'];
            $firstName = $profile['given_name'] ?? '';
            $lastName = $profile['family_name'] ?? '';
            $name = $profile['name'] ?? '';
            $picture = $profile['picture'] ?? '';
            
            // Check if user exists by Google ID
            $query = "SELECT * FROM users WHERE oauth_provider = 'google' AND oauth_id = :oauth_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':oauth_id', $googleId);
            $stmt->execute();
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                // Update existing user
                return $this->updateGoogleUser($existingUser['id'], $profile);
            }
            
            // Check if user exists by email
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                // Link Google account to existing user
                return $this->linkGoogleToExistingUser($existingUser['id'], $profile);
            }
            
            // Create new user
            return $this->createGoogleUser($profile);
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create or update user: ' . $e->getMessage()];
        }
    }
    
    private function createGoogleUser($profile) {
        try {
            $googleId = $profile['id'];
            $email = $profile['email'];
            $firstName = $profile['given_name'] ?? '';
            $lastName = $profile['family_name'] ?? '';
            $name = $profile['name'] ?? '';
            $picture = $profile['picture'] ?? '';
            
            // Generate username
            $username = $this->generateUniqueUsername($firstName, $lastName);
            
            // Create user
            $query = "INSERT INTO users (username, email, first_name, last_name, oauth_provider, oauth_id, profile_picture, email_verified, is_active, created_at) 
                     VALUES (:username, :email, :first_name, :last_name, 'google', :oauth_id, :profile_picture, 1, 1, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':oauth_id', $googleId);
            $stmt->bindParam(':profile_picture', $picture);
            $stmt->execute();
            
            $userId = $this->conn->lastInsertId();
            
            // Login the user
            return $this->loginGoogleUser($userId);
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create user: ' . $e->getMessage()];
        }
    }
    
    private function updateGoogleUser($userId, $profile) {
        try {
            $firstName = $profile['given_name'] ?? '';
            $lastName = $profile['family_name'] ?? '';
            $picture = $profile['picture'] ?? '';
            $current = $this->getCurrentProfilePicture($userId);
            $shouldUpdatePicture = empty($current) || $this->isExternalProfilePicture($current);
            
            if ($shouldUpdatePicture) {
            $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, profile_picture = :profile_picture, last_login = NOW() WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':profile_picture', $picture);
            $stmt->bindParam(':user_id', $userId);
            } else {
                $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, last_login = NOW() WHERE id = :user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':first_name', $firstName);
                $stmt->bindParam(':last_name', $lastName);
                $stmt->bindParam(':user_id', $userId);
            }
            $stmt->execute();
            
            // Login the user
            return $this->loginGoogleUser($userId);
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update user: ' . $e->getMessage()];
        }
    }
    
    private function linkGoogleToExistingUser($userId, $profile) {
        try {
            $googleId = $profile['id'];
            $picture = $profile['picture'] ?? '';
            $current = $this->getCurrentProfilePicture($userId);
            $shouldUpdatePicture = empty($current) || $this->isExternalProfilePicture($current);
            
            if ($shouldUpdatePicture) {
            $query = "UPDATE users SET oauth_provider = 'google', oauth_id = :oauth_id, profile_picture = :profile_picture, last_login = NOW() WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':oauth_id', $googleId);
            $stmt->bindParam(':profile_picture', $picture);
            $stmt->bindParam(':user_id', $userId);
            } else {
                $query = "UPDATE users SET oauth_provider = 'google', oauth_id = :oauth_id, last_login = NOW() WHERE id = :user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':oauth_id', $googleId);
                $stmt->bindParam(':user_id', $userId);
            }
            $stmt->execute();
            
            // Login the user
            return $this->loginGoogleUser($userId);
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to link Google account: ' . $e->getMessage()];
        }
    }
    
    private function loginGoogleUser($userId) {
        try {
            // Get user data
            $query = "SELECT * FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $user = $stmt->fetch();
            
            if ($user) {
                // Start session if not already started
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                
                // Set session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_premium'] = $user['is_premium'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                return [
                    'success' => true,
                    'message' => 'Google login successful',
                    'user' => $user
                ];
            }
            
            return ['success' => false, 'message' => 'User not found'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }
    
    // GitHub OAuth Methods
    public function handleGitHubCallback($code, $state) {
        try {
            // Verify state parameter
            if (!verifyOAuthState($state)) {
                return ['success' => false, 'message' => 'Invalid state parameter'];
            }
            
            // Exchange code for access token
            $tokenData = $this->getGitHubAccessToken($code);
            if (!$tokenData['success']) {
                return $tokenData;
            }
            
            // Get user profile from GitHub
            $profileData = $this->getGitHubProfile($tokenData['access_token']);
            if (!$profileData['success']) {
                return $profileData;
            }
            
            // Create or update user
            $userData = $this->createOrUpdateGitHubUser($profileData['profile']);
            
            return $userData;
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'GitHub authentication failed: ' . $e->getMessage()];
        }
    }
    
    private function getGitHubAccessToken($code) {
        $url = 'https://github.com/login/oauth/access_token';
        $data = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => GITHUB_REDIRECT_URI,
            'client_id' => GITHUB_CLIENT_ID,
            'client_secret' => GITHUB_CLIENT_SECRET
        ];
        
        $response = $this->makeHttpRequest($url, 'POST', $data, [
            'Accept: application/json',
            'User-Agent: TelieAcademy-OAuth'
        ]);
        
        if ($response['success']) {
            $tokenData = json_decode($response['data'], true);
            if (isset($tokenData['access_token'])) {
                return [
                    'success' => true,
                    'access_token' => $tokenData['access_token'],
                    'expires_in' => $tokenData['expires_in'] ?? null
                ];
            } else {
                // Log the error response
                error_log('GitHub token error: ' . json_encode($tokenData));
                return ['success' => false, 'message' => 'Failed to get access token: ' . json_encode($tokenData)];
            }
        }
        
        return ['success' => false, 'message' => 'Failed to get access token: ' . $response['message']];
    }
    
    private function getGitHubProfile($accessToken) {
        // Get user profile
        $profileUrl = 'https://api.github.com/user';
        $profileResponse = $this->makeHttpRequest($profileUrl, 'GET', [], [
            'Authorization: token ' . $accessToken,
            'Accept: application/vnd.github.v3+json',
            'User-Agent: TelieAcademy-OAuth'
        ]);
        
        if (!$profileResponse['success']) {
            error_log('GitHub profile error: ' . $profileResponse['message']);
            return ['success' => false, 'message' => 'Failed to get GitHub profile: ' . $profileResponse['message']];
        }
        
        $profile = json_decode($profileResponse['data'], true);
        
        // Check if profile is valid
        if (!isset($profile['id']) || !isset($profile['login'])) {
            error_log('GitHub profile invalid: ' . json_encode($profile));
            return ['success' => false, 'message' => 'Invalid GitHub profile response'];
        }
        
        // Get email addresses
        $emailUrl = 'https://api.github.com/user/emails';
        $emailResponse = $this->makeHttpRequest($emailUrl, 'GET', [], [
            'Authorization: token ' . $accessToken,
            'Accept: application/vnd.github.v3+json',
            'User-Agent: TelieAcademy-OAuth'
        ]);
        
        $email = null;
        if ($emailResponse['success']) {
            $emailData = json_decode($emailResponse['data'], true);
            foreach ($emailData as $emailInfo) {
                if ($emailInfo['primary'] && $emailInfo['verified']) {
                    $email = $emailInfo['email'];
                    break;
                }
            }
            // If no primary email, use the first verified email
            if (!$email) {
                foreach ($emailData as $emailInfo) {
                    if ($emailInfo['verified']) {
                        $email = $emailInfo['email'];
                        break;
                    }
                }
            }
        }
        
        // Add email to profile
        $profile['email'] = $email;
        
        return [
            'success' => true,
            'profile' => $profile
        ];
    }
    
    private function createOrUpdateGitHubUser($profile) {
        try {
            $githubId = $profile['id'];
            $email = $profile['email'] ?? $this->generateGitHubEmail($githubId);
            $username = $profile['login'];
            $name = $profile['name'] ?? '';
            $picture = $profile['avatar_url'] ?? '';
            
            // Split name into first and last name
            $nameParts = explode(' ', $name, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            
            // Check if user exists by GitHub ID
            $query = "SELECT * FROM users WHERE oauth_provider = 'github' AND oauth_id = :oauth_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':oauth_id', $githubId);
            $stmt->execute();
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                // Update existing user
                return $this->updateGitHubUser($existingUser['id'], $profile);
            }
            
            // Check if user exists by email
            if ($email) {
                $query = "SELECT * FROM users WHERE email = :email";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $existingUser = $stmt->fetch();
                
                if ($existingUser) {
                    // Link GitHub account to existing user
                    return $this->linkGitHubToExistingUser($existingUser['id'], $profile);
                }
            }
            
            // Check if username exists
            if ($this->usernameExists($username)) {
                $username = $this->generateUniqueUsername($firstName, $lastName);
            }
            
            // Create new user
            return $this->createGitHubUser($profile);
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create or update user: ' . $e->getMessage()];
        }
    }
    
    private function createGitHubUser($profile) {
        try {
            $githubId = $profile['id'];
            $email = $profile['email'] ?? $this->generateGitHubEmail($githubId);
            $username = $profile['login'];
            $name = $profile['name'] ?? '';
            $picture = $profile['avatar_url'] ?? '';
            
            // Split name into first and last name
            $nameParts = explode(' ', $name, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            
            // Check if username exists
            if ($this->usernameExists($username)) {
                $username = $this->generateUniqueUsername($firstName, $lastName);
            }
            
            // Create user
            $query = "INSERT INTO users (username, email, first_name, last_name, oauth_provider, oauth_id, profile_picture, email_verified, is_active, created_at) 
                     VALUES (:username, :email, :first_name, :last_name, 'github', :oauth_id, :profile_picture, 1, 1, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':oauth_id', $githubId);
            $stmt->bindParam(':profile_picture', $picture);
            $stmt->execute();
            
            $userId = $this->conn->lastInsertId();
            
            // Login the user
            return $this->loginGitHubUser($userId);
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create user: ' . $e->getMessage()];
        }
    }
    
    private function updateGitHubUser($userId, $profile) {
        try {
            $name = $profile['name'] ?? '';
            $picture = $profile['avatar_url'] ?? '';
            
            // Split name into first and last name
            $nameParts = explode(' ', $name, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            $current = $this->getCurrentProfilePicture($userId);
            $shouldUpdatePicture = empty($current) || $this->isExternalProfilePicture($current);
            
            if ($shouldUpdatePicture) {
            $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, profile_picture = :profile_picture, last_login = NOW() WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':profile_picture', $picture);
            $stmt->bindParam(':user_id', $userId);
            } else {
                $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, last_login = NOW() WHERE id = :user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':first_name', $firstName);
                $stmt->bindParam(':last_name', $lastName);
                $stmt->bindParam(':user_id', $userId);
            }
            $stmt->execute();
            
            // Login the user
            return $this->loginGitHubUser($userId);
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update user: ' . $e->getMessage()];
        }
    }
    
    private function linkGitHubToExistingUser($userId, $profile) {
        try {
            $githubId = $profile['id'];
            $picture = $profile['avatar_url'] ?? '';
            $current = $this->getCurrentProfilePicture($userId);
            $shouldUpdatePicture = empty($current) || $this->isExternalProfilePicture($current);
            
            if ($shouldUpdatePicture) {
            $query = "UPDATE users SET oauth_provider = 'github', oauth_id = :oauth_id, profile_picture = :profile_picture, last_login = NOW() WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':oauth_id', $githubId);
            $stmt->bindParam(':profile_picture', $picture);
            $stmt->bindParam(':user_id', $userId);
            } else {
                $query = "UPDATE users SET oauth_provider = 'github', oauth_id = :oauth_id, last_login = NOW() WHERE id = :user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':oauth_id', $githubId);
                $stmt->bindParam(':user_id', $userId);
            }
            $stmt->execute();
            
            // Login the user
            return $this->loginGitHubUser($userId);
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to link GitHub account: ' . $e->getMessage()];
        }
    }

    // Helper: check if a stored profile picture is external (OAuth) versus local upload
    private function isExternalProfilePicture($profilePicturePath) {
        if (!$profilePicturePath) return false;
        if (filter_var($profilePicturePath, FILTER_VALIDATE_URL)) return true;
        $oauthDomains = [
            'googleusercontent.com',
            'githubusercontent.com',
            'licdn.com',
            'linkedin.com'
        ];
        foreach ($oauthDomains as $domain) {
            if (strpos($profilePicturePath, $domain) !== false) {
                return true;
            }
        }
        return false;
    }

    // Helper: get current profile picture for a user
    private function getCurrentProfilePicture($userId) {
        try {
            $query = "SELECT profile_picture FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ? ($row['profile_picture'] ?? '') : '';
        } catch (Exception $e) {
            return '';
        }
    }
    
    private function loginGitHubUser($userId) {
        try {
            // Get user data
            $query = "SELECT * FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $user = $stmt->fetch();
            
            if ($user) {
                // Start session if not already started
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                
                // Set session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_premium'] = $user['is_premium'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                return [
                    'success' => true,
                    'message' => 'GitHub login successful',
                    'user' => $user
                ];
            }
            
            return ['success' => false, 'message' => 'User not found'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }
    
    private function generateUniqueUsername($firstName, $lastName) {
        $baseUsername = strtolower($firstName . $lastName);
        $baseUsername = preg_replace('/[^a-z0-9]/', '', $baseUsername);
        
        $username = $baseUsername;
        $counter = 1;
        
        while ($this->usernameExists($username)) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        
        return $username;
    }
    
    private function generateLinkedInEmail($linkedinId) {
        return 'linkedin_' . $linkedinId . '@telieacademy.com';
    }

    private function generateGitHubEmail($githubId) {
        return 'github_' . $githubId . '@telieacademy.com';
    }
    
    private function usernameExists($username) {
        $query = "SELECT COUNT(*) FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
    
    private function makeHttpRequest($url, $method = 'GET', $data = [], $headers = []) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'message' => 'cURL error: ' . $error];
        }
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'data' => $response];
        } else {
            return ['success' => false, 'message' => 'HTTP error: ' . $httpCode];
        }
    }
}
?> 