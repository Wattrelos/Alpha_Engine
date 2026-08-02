<?php

// Script que substitui todo store_id = 1 por store_id = 1 nas tabelas do banco de dados
// eliminando esse antipattern do sistema.
// Substitua pelas credenciais do seu banco de dados
$host = 'localhost';
$user = 'root';
$password = '42010052';
$dbname = 'myDatabase';
$prefix = 'tbkk_'; // Lembre-se de colocar o prefixo do seu banco de dados

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pega todas as tabelas com esse prefixo
    $stmt = $conn->query("SHOW TABLES LIKE '" . $prefix . "%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        // Verifica se a coluna store_id existe na tabela
        $colStmt = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'store_id'");
        if ($colStmt->rowCount() > 0) {
            // Executa a atualização
            $sql = "UPDATE `$table` SET `store_id` = 1 WHERE `store_id` = 0";
            $conn->exec($sql);
            echo "Tabela `$table` atualizada com sucesso!<br>";
        }
    }
    echo "Processo concluído!";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
