<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS, DELETE"); // Adicionado DELETE para boas práticas
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once './Conexao.php';

function sendResponse($status, $message, $data = [], $httpCode = 200) {
    if ($status === 'error' && $httpCode === 200) {
        $httpCode = 500; 
    }
    http_response_code($httpCode);
    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data
    ]);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        sendResponse('error', 'Método não permitido. Use POST ou DELETE.', [], 405);
    }

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        sendResponse('error', 'ID do produto inválido ou não informado.', [], 400); 
    }

    $checkSql = "SELECT id FROM custos_fixos WHERE id = :id";
    $checkStmt = $conexao->prepare($checkSql);
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->rowCount() === 0) {
        sendResponse('error', 'Produto com ID ' . $id . ' não encontrado.', [], 404); // 404 Not Found
    }

    $deleteSql = "DELETE FROM custos_fixos WHERE id = :id";
    $deleteStmt = $conexao->prepare($deleteSql);
    $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $deleteStmt->execute();

    sendResponse('success', 'Produto deletado com sucesso.', ['id' => $id]);

} catch (PDOException $e) {
    sendResponse('error', 'Erro ao deletar produto (PDO): ' . $e->getMessage(), [], 500);
} catch (Exception $e) {
    sendResponse('error', 'Erro interno do servidor: ' . $e->getMessage(), [], 500);
}
?>