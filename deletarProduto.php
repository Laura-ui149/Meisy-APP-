<?php
// Configurações de exibição de erro
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurações de cabeçalho
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS, DELETE"); // Adicionado DELETE para boas práticas
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Inclui o arquivo de conexão
require_once './Conexao.php';

/**
 * Função para padronizar as respostas da API.
 * @param string $status 'success' ou 'error'
 * @param string $message Mensagem de resposta
 * @param array $data Dados adicionais a serem retornados
 * @param int $httpCode Código HTTP de resposta
 */
function sendResponse($status, $message, $data = [], $httpCode = 200) {
    if ($status === 'error' && $httpCode === 200) {
        $httpCode = 500; // Define 500 para erro interno, se não for um erro de cliente
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
    // Verifica o método da requisição. POST é mantido para compatibilidade.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        sendResponse('error', 'Método não permitido. Use POST ou DELETE.', [], 405);
    }
    
    // --------------------------------------------------------------------------------
    // Tenta obter o ID, priorizando o POST (dados de formulário)
    // Se você estiver enviando o ID via JSON body, precisará usar:
    // $data = json_decode(file_get_contents('php://input'), true);
    // $id = isset($data['id']) ? intval($data['id']) : 0;
    // --------------------------------------------------------------------------------
    
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        sendResponse('error', 'ID do produto inválido ou não informado.', [], 400); // 400 Bad Request
    }

    // Verifica se o produto existe antes de excluir
    $checkSql = "SELECT id FROM produto WHERE id = :id";
    $checkStmt = $conexao->prepare($checkSql);
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->rowCount() === 0) {
        sendResponse('error', 'Produto com ID ' . $id . ' não encontrado.', [], 404); // 404 Not Found
    }

    // Exclui o produto
    $deleteSql = "DELETE FROM produto WHERE id = :id";
    $deleteStmt = $conexao->prepare($deleteSql);
    $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $deleteStmt->execute();

    // Resposta de sucesso
    sendResponse('success', 'Produto deletado com sucesso.', ['id' => $id]);

} catch (PDOException $e) {
    // Erro de banco de dados
    sendResponse('error', 'Erro ao deletar produto (PDO): ' . $e->getMessage(), [], 500);
} catch (Exception $e) {
    // Erro geral
    sendResponse('error', 'Erro interno do servidor: ' . $e->getMessage(), [], 500);
}
?>