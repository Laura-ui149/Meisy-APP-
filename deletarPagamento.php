<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS, DELETE"); 
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

    // AQUI: BUSCA A CHAVE 'id' (que agora o Flutter vai enviar)
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        // Mensagem de erro atualizada
        sendResponse('error', 'ID do pagamento inválido ou não informado.', [], 400); 
    }

    // Verifica se existe usando a coluna 'id'
    $checkSql = "SELECT id FROM pagamentos WHERE id = :id";
    $checkStmt = $conexao->prepare($checkSql);
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->rowCount() === 0) {
        // Mensagem de erro atualizada
        sendResponse('error', 'Pagamento com ID ' . $id . ' não encontrado.', [], 404); 
    }

    // Deleta usando a coluna 'id'
    $deleteSql = "DELETE FROM pagamentos WHERE id = :id";
    $deleteStmt = $conexao->prepare($deleteSql);
    $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $deleteStmt->execute();

    sendResponse('success', 'Pagamento deletado com sucesso.', ['id' => $id]);

} catch (PDOException $e) {
    sendResponse('error', 'Erro ao deletar pagamento (PDO): ' . $e->getMessage(), [], 500);
} catch (Exception $e) {
    sendResponse('error', 'Erro interno do servidor: ' . $e->getMessage(), [], 500);
}
?>