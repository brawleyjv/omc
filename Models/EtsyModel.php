<?php
namespace MyApp\Models;

use PDO;
use Exception;

/**
 * EtsyModel - Handles Etsy API OAuth authentication and API requests
 * 
 * This model manages:
 * - OAuth 2.0 authentication flow
 * - Token storage and refresh
 * - API request wrapper with error handling
 * - Rate limiting and caching
 */
class EtsyModel {
    private $db;
    private $apiKey;
    private $sharedSecret;
    private $accessToken;
    private $refreshToken;
    private $shopId;
    private $connected;
    
    // Etsy API v3 endpoints
    private const API_BASE_URL = 'https://openapi.etsy.com/v3';
    private const AUTH_URL = 'https://www.etsy.com/oauth/connect';
    private const TOKEN_URL = 'https://api.etsy.com/v3/public/oauth/token';
    
    // OAuth scopes needed for our application
    private const SCOPES = [
        'shops_r',           // Read shop information
        'transactions_r',    // Read order/transaction data
        'listings_r',        // Read listings
        'shipping_r',        // Read shipping information
        'transactions_w',    // Write transaction updates (fulfillment)
    ];
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->loadCredentials();
    }
    
    /**
     * Load Etsy credentials from database
     */
    private function loadCredentials() {
        $query = "SELECT etsy_api_key, etsy_shared_secret, etsy_access_token, 
                         etsy_refresh_token, etsy_shop_id, etsy_connected, etsy_token_expires
                  FROM settings WHERE id = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($settings) {
            $this->apiKey = $settings['etsy_api_key'];
            $this->sharedSecret = $settings['etsy_shared_secret'];
            $this->accessToken = $settings['etsy_access_token'];
            $this->refreshToken = $settings['etsy_refresh_token'];
            $this->shopId = $settings['etsy_shop_id'];
            $this->connected = $settings['etsy_connected'];
        }
    }
    
    /**
     * Generate OAuth authorization URL
     * 
     * @param string $redirectUri OAuth callback URL
     * @param string $state CSRF protection state token
     * @return string Authorization URL
     */
    public function getAuthorizationUrl($redirectUri, $state) {
        // Generate code verifier for PKCE (OAuth 2.0 security enhancement)
        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);
        
        // Store code verifier in session for callback
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['etsy_code_verifier'] = $codeVerifier;
        $_SESSION['etsy_oauth_state'] = $state;
        
        $params = [
            'response_type' => 'code',
            'client_id' => $this->apiKey,
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', self::SCOPES),
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256'
        ];
        
        return self::AUTH_URL . '?' . http_build_query($params);
    }
    
    /**
     * Exchange authorization code for access token
     * 
     * @param string $code Authorization code from callback
     * @param string $redirectUri Same redirect URI used in authorization
     * @return array Token response with access_token, refresh_token, expires_in
     * @throws Exception on error
     */
    public function exchangeCodeForToken($code, $redirectUri) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $codeVerifier = $_SESSION['etsy_code_verifier'] ?? null;
        if (!$codeVerifier) {
            throw new Exception('Code verifier not found in session');
        }
        
        $data = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->apiKey,
            'redirect_uri' => $redirectUri,
            'code' => $code,
            'code_verifier' => $codeVerifier
        ];
        
        $response = $this->makeTokenRequest($data);
        
        if (isset($response['access_token'])) {
            $this->saveTokens(
                $response['access_token'],
                $response['refresh_token'],
                $response['expires_in']
            );
            
            // Fetch and store shop information
            $this->fetchAndStoreShopInfo();
            
            // Clear session data
            unset($_SESSION['etsy_code_verifier']);
            unset($_SESSION['etsy_oauth_state']);
            
            return $response;
        }
        
        throw new Exception('Failed to exchange code for token: ' . ($response['error_description'] ?? 'Unknown error'));
    }
    
    /**
     * Refresh access token using refresh token
     * 
     * @return bool Success status
     */
    public function refreshAccessToken() {
        if (!$this->refreshToken) {
            return false;
        }
        
        $data = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->apiKey,
            'refresh_token' => $this->refreshToken
        ];
        
        try {
            $response = $this->makeTokenRequest($data);
            
            if (isset($response['access_token'])) {
                $this->saveTokens(
                    $response['access_token'],
                    $response['refresh_token'],
                    $response['expires_in']
                );
                return true;
            }
        } catch (Exception $e) {
            error_log('Etsy token refresh failed: ' . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Make token request to Etsy
     * 
     * @param array $data Request data
     * @return array Response data
     * @throws Exception on error
     */
    private function makeTokenRequest($data) {
        $ch = curl_init(self::TOKEN_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        
        $data = json_decode($response, true);
        
        if ($httpCode !== 200) {
            throw new Exception('Token request failed: ' . ($data['error_description'] ?? 'HTTP ' . $httpCode));
        }
        
        return $data;
    }
    
    /**
     * Save tokens to database
     * 
     * @param string $accessToken Access token
     * @param string $refreshToken Refresh token
     * @param int $expiresIn Token lifetime in seconds
     */
    private function saveTokens($accessToken, $refreshToken, $expiresIn) {
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);
        
        $query = "UPDATE settings SET 
                  etsy_access_token = :access_token,
                  etsy_refresh_token = :refresh_token,
                  etsy_token_expires = :expires_at,
                  etsy_connected = 1
                  WHERE id = 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':access_token' => $accessToken,
            ':refresh_token' => $refreshToken,
            ':expires_at' => $expiresAt
        ]);
        
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->connected = true;
    }
    
    /**
     * Fetch shop information and store shop ID
     */
    private function fetchAndStoreShopInfo() {
        try {
            // Use "me" endpoint to get current user's shop
            $response = $this->makeApiRequest('GET', '/application/shops');
            
            if (isset($response['results'][0])) {
                $shop = $response['results'][0];
                $shopId = $shop['shop_id'];
                $shopName = $shop['shop_name'];
                
                $query = "UPDATE settings SET 
                          etsy_shop_id = :shop_id,
                          etsy_shop_name = :shop_name
                          WHERE id = 1";
                
                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    ':shop_id' => $shopId,
                    ':shop_name' => $shopName
                ]);
                
                $this->shopId = $shopId;
            }
        } catch (Exception $e) {
            error_log('Failed to fetch shop info: ' . $e->getMessage());
        }
    }
    
    /**
     * Make API request to Etsy
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint (e.g., '/shops/{shop_id}/receipts')
     * @param array $params Query parameters or POST data
     * @return array Response data
     * @throws Exception on error
     */
    public function makeApiRequest($method, $endpoint, $params = []) {
        // Check if token needs refresh (refresh if expires in less than 5 minutes)
        $this->checkAndRefreshToken();
        
        if (!$this->accessToken) {
            throw new Exception('Not authenticated with Etsy');
        }
        
        $url = self::API_BASE_URL . $endpoint;
        
        // Replace {shop_id} placeholder with actual shop ID
        $url = str_replace('{shop_id}', $this->shopId, $url);
        
        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'x-api-key: ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        
        if ($method !== 'GET' && !empty($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        
        $data = json_decode($response, true);
        
        if ($httpCode >= 400) {
            throw new Exception('API request failed: ' . ($data['error'] ?? 'HTTP ' . $httpCode));
        }
        
        return $data;
    }
    
    /**
     * Check token expiration and refresh if needed
     */
    private function checkAndRefreshToken() {
        $query = "SELECT etsy_token_expires FROM settings WHERE id = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['etsy_token_expires']) {
            $expiresAt = strtotime($result['etsy_token_expires']);
            $now = time();
            
            // Refresh if expires in less than 5 minutes
            if ($expiresAt - $now < 300) {
                $this->refreshAccessToken();
            }
        }
    }
    
    /**
     * Disconnect from Etsy (clear tokens)
     */
    public function disconnect() {
        $query = "UPDATE settings SET 
                  etsy_access_token = NULL,
                  etsy_refresh_token = NULL,
                  etsy_token_expires = NULL,
                  etsy_connected = 0,
                  etsy_shop_id = NULL,
                  etsy_shop_name = NULL
                  WHERE id = 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $this->accessToken = null;
        $this->refreshToken = null;
        $this->shopId = null;
        $this->connected = false;
    }
    
    /**
     * Check if connected to Etsy
     * 
     * @return bool Connection status
     */
    public function isConnected() {
        return $this->connected && $this->accessToken;
    }
    
    /**
     * Get shop ID
     * 
     * @return string|null Shop ID
     */
    public function getShopId() {
        return $this->shopId;
    }
    
    /**
     * Generate code verifier for PKCE (43-128 characters)
     */
    private function generateCodeVerifier() {
        return bin2hex(random_bytes(32)); // 64 characters
    }
    
    /**
     * Generate code challenge from verifier (SHA256 hash, base64url encoded)
     */
    private function generateCodeChallenge($verifier) {
        $hash = hash('sha256', $verifier, true);
        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }
    
    /**
     * Log sync operation
     * 
     * @param string $syncType Type of sync (orders, listings, etc.)
     * @param string $status Status (success, failure, partial)
     * @param array $stats Statistics array
     * @param string|null $error Error message if failed
     */
    public function logSync($syncType, $status, $stats = [], $error = null) {
        $query = "INSERT INTO etsy_sync_log (
                    sync_type, status, records_processed, records_added, 
                    records_updated, records_failed, error_message, 
                    api_calls_made, started_at, completed_at
                  ) VALUES (
                    :sync_type, :status, :processed, :added, 
                    :updated, :failed, :error, 
                    :api_calls, :started, :completed
                  )";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':sync_type' => $syncType,
            ':status' => $status,
            ':processed' => $stats['processed'] ?? 0,
            ':added' => $stats['added'] ?? 0,
            ':updated' => $stats['updated'] ?? 0,
            ':failed' => $stats['failed'] ?? 0,
            ':error' => $error,
            ':api_calls' => $stats['api_calls'] ?? 0,
            ':started' => $stats['started_at'] ?? date('Y-m-d H:i:s'),
            ':completed' => date('Y-m-d H:i:s')
        ]);
    }
}
?>
