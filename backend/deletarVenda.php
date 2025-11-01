<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
require_once './Conexao.php';

function sendResponse($status, $message, $data = [], $httpCode = 200) {
    if ($status === 'error') {
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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse('error', 'Método não permitido. Use POST.', [], 405);
    }

    // Recebe o ID do evento via POST
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        sendResponse('error', 'ID inválido ou não informado.');
    }

    // Verifica se o evento existe antes de excluir
    $checkSql = "SELECT id FROM venda WHERE id = :id";
    $checkStmt = $conexao->prepare($checkSql);
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->rowCount() === 0) {
        sendResponse('error', 'Venda não encontrado.');
    }

    // Exclui o evento
    $deleteSql = "DELETE FROM venda WHERE id = :id";
    $deleteStmt = $conexao->prepare($deleteSql);
    $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $deleteStmt->execute();

    sendResponse('success', 'Venda deletado com sucesso.', ['id' => $id]);

} catch (PDOException $e) {
    sendResponse('error', 'Erro ao deletar evento (PDO): ' . $e->getMessage());
} catch (Exception $e) {
    sendResponse('error', 'Erro interno do servidor: ' . $e->getMessage());
}
?>
