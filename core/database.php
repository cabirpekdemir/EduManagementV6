<?php
// core/database.php

class Database {
    private static $instance = null;
    private $pdo;
    private $transactionLevel = 0;

    private function __construct() {
        $host = defined('DB_HOST') ? DB_HOST : 'localhost';
         $dbname = 'hipotezegitimcom_edumanagement8'; 
        $username = 'root';
        $password = 'root'; //
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Veritabanı bağlantı hatası. Lütfen sistem yöneticisine başvurun.");
        }
    }

    public static function getInstance(): Database {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }

    public function select(string $query, array $params = []): ?array {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Select Error: " . $e->getMessage());
            return null;
        }
    }

    public function fetch(string $query, array $params = []): ?array {
    try {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    } catch (PDOException $e) {
        error_log("Database Fetch Error: " . $e->getMessage());
        error_log("HATA VEREN QUERY: " . $query);
        error_log("PARAMS: " . print_r($params, true));
        return null;
    }
}

    public function execute(string $query, array $params = []): int {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database Execute Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function beginTransaction(): void {
        if ($this->transactionLevel === 0) {
            $this->pdo->beginTransaction();
        }
        $this->transactionLevel++;
    }

    public function begin(): void {
        $this->beginTransaction();
    }
    
    public function commit(): void {
        $this->transactionLevel--;
        if ($this->transactionLevel === 0 && $this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    public function rollBack(): void {
        if ($this->transactionLevel > 0) {
            $this->transactionLevel--;
            if ($this->transactionLevel === 0 && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
        }
    }

    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }

    public function inTransaction(): bool {
        return $this->pdo->inTransaction();
    }

    private function __clone() {}
    
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
    
}
