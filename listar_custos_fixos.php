<?php
// Configurações de cabeçalho
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Inclua seu arquivo de conexão
require_once './Conexao.php'; 

// 1. Verifica se a requisição é GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido. Use GET."]);
    exit();
}

try {
    // 2. CORREÇÃO DE NOMECLATURA E SELEÇÃO DE ID
    // Seleciona o 'id' e usa 'data_registro' para ordenação, conforme sua tabela SQL.
    $sql = "SELECT id, descricao, valor, data_vencimento, periodicidade 
            FROM custos_fixos 
            ORDER BY data_registro DESC"; // Assuming 'data_registro' is your timestamp column
    
    $stmt = $conexao->prepare($sql);
    $stmt->execute();
    
    // 3. Renomeando variável para clareza (de $produtos para $custosFixos)
    $custosFixos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Retorna status 200 e os dados
    http_response_code(200);
    echo json_encode($custosFixos);

} catch (PDOException $e) {
    // 5. Tratamento de erro aprimorado
    http_response_code(500); 
    // Registra o erro internamente
    error_log("Erro de PDO na listagem de custos fixos: " . $e->getMessage()); 
    
    // Retorna uma mensagem genérica para o usuário
    echo json_encode([
        "status" => "error",
        "message" => "Erro interno no servidor ao listar custos fixos."
    ]);
    exit();
}