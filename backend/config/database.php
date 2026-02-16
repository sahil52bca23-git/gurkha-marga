<?php
/**
 * Database Configuration and Helper Functions
 * Gurkha Marga - Army Recruitment Platform
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gurkha_marga');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Global PDO instance
$pdo = null;

/**
 * Get database connection
 * @return PDO Database connection instance
 */
function getDB() {
    global $pdo;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error (don't expose to user)
            error_log('Database Connection Error: ' . $e->getMessage());
            
            // Display user-friendly error
            die('Database connection failed. Please try again later.');
        }
    }
    
    return $pdo;
}

/**
 * Execute a SQL query with parameters
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind
 * @return PDOStatement Executed statement
 */
function query($sql, $params = []) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log('Query Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
        throw $e;
    }
}

/**
 * Execute INSERT query and return last inserted ID
 * @param string $sql SQL INSERT query
 * @param array $params Parameters to bind
 * @return string Last inserted ID
 */
function insert($sql, $params = []) {
    query($sql, $params);
    return getDB()->lastInsertId();
}

/**
 * Execute UPDATE/DELETE query and return affected rows
 * @param string $sql SQL UPDATE/DELETE query
 * @param array $params Parameters to bind
 * @return int Number of affected rows
 */
function execute($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->rowCount();
}

/**
 * Fetch single row
 * @param string $sql SQL query
 * @param array $params Parameters to bind
 * @return array|false Single row or false
 */
function fetchOne($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetch();
}

/**
 * Fetch all rows
 * @param string $sql SQL query
 * @param array $params Parameters to bind
 * @return array All rows
 */
function fetchAll($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Begin transaction
 */
function beginTransaction() {
    getDB()->beginTransaction();
}

/**
 * Commit transaction
 */
function commit() {
    getDB()->commit();
}

/**
 * Rollback transaction
 */
function rollback() {
    getDB()->rollBack();
}

/**
 * Check if table exists
 * @param string $tableName Table name
 * @return bool True if exists
 */
function tableExists($tableName) {
    $sql = "SHOW TABLES LIKE ?";
    $stmt = query($sql, [$tableName]);
    return $stmt->rowCount() > 0;
}

/**
 * Sanitize input string
 * @param string $input Input string
 * @return string Sanitized string
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 * @param string $email Email address
 * @return bool True if valid
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate random token
 * @param int $length Token length
 * @return string Random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Hash password
 * @param string $password Plain password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 * @param string $password Plain password
 * @param string $hash Hashed password
 * @return bool True if match
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Initialize database connection on include
getDB();