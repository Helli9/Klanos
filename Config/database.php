<?php
declare(strict_types=1);
namespace Config;
use PDO;
use PDOException;


class Database
{
    private static ?PDO $instance = null;

    private string $host;
    private string $dbname;
    private string $username;
    private string $password;
    private string $charset;

    private function __construct() {
        $this->host     = $_ENV['DB_HOST']     ?? getenv('DB_HOST')     ?: 'localhost';
        $this->dbname   = $_ENV['DB_NAME']     ?? getenv('DB_NAME')     ?: '';
        $this->username = $_ENV['DB_USER']     ?? getenv('DB_USER')     ?: 'root';
        $this->password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
        $this->charset  = $_ENV['DB_CHARSET']  ?? getenv('DB_CHARSET')  ?: 'utf8mb4';
    }
    private function __clone() {}

    public static function getInstance(): PDO{
        if (self::$instance === null) {
            self::$instance = (new self())->connect();
        }
        return self::$instance;
    }

    private function connect(): PDO{
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            return new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // Log the real error with credentials — never expose it to the browser
            error_log('[DB] Connection failed: ' . $e->getMessage());
            throw new \RuntimeException('Database connection failed.');
        }
    }
}