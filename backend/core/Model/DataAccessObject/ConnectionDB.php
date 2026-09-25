<?php
namespace Alpha\Model\DataAccessObject;

use PDO;
use PDOException;
use Exception;

/**
 * Refere-se a ConnectionDB.java
 * Adaptado para PHP 8.4.16
 * Implementa o padrão Singleton para gerenciar a conexão com o banco de dados via PDO nativo.
 * Totalmente controlado pela Alpha Engine.
 */
class ConnectionDB
{
    private static ?ConnectionDB $instance = null;
    private ?PDO $connection = null;

    /**
     * Construtor privado para implementar o padrão Singleton.
     * Tenta estabelecer a conexão com o banco de dados.
     * Lança uma exceção se a conexão falhar.
     */
    private function __construct()
    {
        // Em PHP, não precisamos carregar um driver JDBC explicitamente como em Java.
        // O PDO já vem com drivers para diversos bancos de dados, basta que a extensão esteja habilitada.
        // Por exemplo, para MySQL/MariaDB, a extensão 'pdo_mysql' deve estar habilitada no php.ini.

        try {
            // DSN (Data Source Name) para MariaDB/MySQL
            // Ex: "mysql:host=localhost;dbname=gwj2;charset=utf8mb4"
            // Montando o DSN
            $dsn = "mysql:host=" . DB_HOSTNAME . ";dbname=" . DB_DATABASE . ";port=" . DB_PORT . ";charset=utf8mb4";
            $this->connection = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Alpha Engine Failsafe: Previne OOM e erro 2014 "Cannot execute queries while other unbuffered queries are active"
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                PDO::ATTR_EMULATE_PREPARES => false, // Garante tipos nativos no PDO, reduzindo footprint de memória drasticamente
                PDO::ATTR_STRINGIFY_FETCHES => false // Impede a conversão automática de tipos numéricos para string na leitura
            ]);
            $this->connection->exec("SET NAMES 'utf8mb4'");
            $this->connection->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");


        } catch (PDOException $e) {
            // Em caso de falha na conexão, loga o erro e lança uma exceção.
            error_log("Erro de conexão com o banco de dados: " . $e->getMessage());
            throw new Exception("Não foi possível conectar ao banco de dados.", 0, $e);
        }
    }

    /**
     * Impede a clonagem da instância do Singleton.
     */
    private function __clone() {}

    /**
     * Impede a desserialização da instância do Singleton.
     */
    public function __wakeup(): void
    {
        throw new Exception("Não é possível desserializar um singleton.");
    }

    /**
     * Retorna a única instância da classe ConnectionDB.
     * 
     * @param mixed $db Parâmetro opcional e ignorado, mantido apenas para compatibilidade de assinatura.
     * @return ConnectionDB
     */
    public static function getInstance($db = null): ConnectionDB
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retorna o objeto PDO da conexão com o banco de dados.
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            // Isso não deveria acontecer se o construtor foi bem-sucedido,
            // mas é uma salvaguarda.
            throw new Exception("A conexão com o banco de dados não foi estabelecida.");
        }
        return $this->connection;
    }

    /**
     * Fecha a conexão com o banco de dados.
     * Em PHP, a conexão PDO é geralmente fechada automaticamente no final da execução do script.
     * No entanto, este método pode ser usado para fechar explicitamente se necessário.
     */
    public function closeConnection(): void
    {
        $this->connection = null;
    }

    /**
     * Executa uma consulta preparada e retorna um único resultado.
     * Útil para hidratação de Proxies (Lazy Loading).
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erro em queryOne: " . $e->getMessage());
            return null;
        }
    }
}