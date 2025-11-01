<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require_once './Conexao.php';

try {
    $id_usuario = $_GET['id_usuario'];

    if (empty($id_usuario)) {
        echo json_encode([]);
        exit();
    }

    $sql = "SELECT * FROM pagamentos WHERE id_usuario = :id_usuario ORDER BY data_lancamento DESC";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode([]);
}
