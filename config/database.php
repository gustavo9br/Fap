<?php
/**
 * Configuração do Banco de Dados
 * FAP Pádua - Sistema de Gerenciamento
 */

// Configurações do MySQL
define('DB_HOST', 'tasks.mysql_mysql'); // Nome do serviço Docker Swarm
define('DB_NAME', 'fap_padua');
define('DB_USER', 'root');
define('DB_PASS', 'BAAE3A32D667F546851BED3777633');
define('DB_CHARSET', 'utf8mb4');

// Classe de conexão com banco de dados
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch(PDOException $e) {
            error_log("Erro de conexão: " . $e->getMessage());
            die("Erro ao conectar com o banco de dados. Tente novamente mais tarde.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Prevenir clonagem
    private function __clone() {}
    
    // Prevenir deserialização
    public function __wakeup() {
        throw new Exception("Não é permitido deserializar singleton");
    }
}
