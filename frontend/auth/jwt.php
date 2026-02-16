<?php
/**
 * JWT (JSON Web Token) Helper Class
 * Simple JWT implementation for authentication
 * Gurkha Marga - Army Recruitment Platform
 */

class JWT {
    // Secret key for signing tokens (CHANGE THIS IN PRODUCTION!)
    private static $secret = 'your-secret-key-change-this-in-production-2026';
    
    // Token expiration time (7 days in seconds)
    private static $expiration = 604800;
    
    // Cookie name for storing token
    private static $cookieName = 'auth_token';
    
    /**
     * Create a JWT token
     * @param array $payload Data to encode in token
     * @return string JWT token
     */
    public static function create($payload) {
        // Add issued at and expiration time
        $payload['iat'] = time();
        $payload['exp'] = time() + self::$expiration;
        
        // Create header
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        
        // Encode header and payload
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        
        // Create signature
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", self::$secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);
        
        // Combine all parts
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }
    
    /**
     * Verify and decode a JWT token
     * @param string $token JWT token
     * @return array|false Decoded payload or false if invalid
     */
    public static function verify($token) {
        // Split token into parts
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
        
        // Verify signature
        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", self::$secret, true);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }
        
        // Decode payload
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        
        if (!$payload) {
            return false;
        }
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    /**
     * Set JWT token in HTTP-only cookie
     * @param string $token JWT token
     * @param bool $remember Remember me option
     */
    public static function setTokenCookie($token, $remember = false) {
        $expiration = $remember ? time() + (30 * 24 * 60 * 60) : 0; // 30 days or session
        
        setcookie(
            self::$cookieName,
            $token,
            [
                'expires' => $expiration,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']), // Only HTTPS in production
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }
    
    /**
     * Get JWT token from cookie
     * @return string|null Token or null if not found
     */
    public static function getTokenFromCookie() {
        return $_COOKIE[self::$cookieName] ?? null;
    }
    
    /**
     * Clear JWT token cookie
     */
    public static function clearTokenCookie() {
        setcookie(
            self::$cookieName,
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }
    
    /**
     * Get user data from token
     * @param string $token JWT token
     * @return array|false User data or false
     */
    public static function getUserFromToken($token) {
        $payload = self::verify($token);
        
        if (!$payload) {
            return false;
        }
        
        // Return user data (excluding sensitive fields)
        return [
            'id' => $payload['id'] ?? null,
            'email' => $payload['email'] ?? null,
            'full_name' => $payload['full_name'] ?? null,
            'role' => $payload['role'] ?? 'user'
        ];
    }
    
    /**
     * Create token for user login
     * @param array $user User data from database
     * @param bool $remember Remember me option
     * @return string JWT token
     */
    public static function createUserToken($user, $remember = false) {
        $payload = [
            'id' => $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role'] ?? 'user'
        ];
        
        $token = self::create($payload);
        self::setTokenCookie($token, $remember);
        
        return $token;
    }
    
    /**
     * Refresh token (extend expiration)
     * @param string $token Current token
     * @return string|false New token or false
     */
    public static function refresh($token) {
        $payload = self::verify($token);
        
        if (!$payload) {
            return false;
        }
        
        // Remove old timestamps
        unset($payload['iat']);
        unset($payload['exp']);
        
        // Create new token
        $newToken = self::create($payload);
        self::setTokenCookie($newToken);
        
        return $newToken;
    }
    
    /**
     * Check if user is authenticated
     * @return bool True if authenticated
     */
    public static function isAuthenticated() {
        $token = self::getTokenFromCookie();
        
        if (!$token) {
            return false;
        }
        
        return self::verify($token) !== false;
    }
    
    /**
     * Require authentication (redirect if not logged in)
     * @param string $redirectUrl URL to redirect if not authenticated
     */
    public static function requireAuth($redirectUrl = '/login.php') {
        if (!self::isAuthenticated()) {
            header('Location: ' . $redirectUrl);
            exit();
        }
    }
    
    /**
     * Get current user ID
     * @return int|null User ID or null
     */
    public static function getCurrentUserId() {
        $token = self::getTokenFromCookie();
        
        if (!$token) {
            return null;
        }
        
        $user = self::getUserFromToken($token);
        return $user['id'] ?? null;
    }
    
    /**
     * Base64 URL encode
     * @param string $data Data to encode
     * @return string Encoded string
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     * @param string $data Data to decode
     * @return string Decoded string
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
    
    /**
     * Set custom secret key
     * @param string $secret Secret key
     */
    public static function setSecret($secret) {
        self::$secret = $secret;
    }
    
    /**
     * Set custom expiration time
     * @param int $seconds Expiration time in seconds
     */
    public static function setExpiration($seconds) {
        self::$expiration = $seconds;
    }
}