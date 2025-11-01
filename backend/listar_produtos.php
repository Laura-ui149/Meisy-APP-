<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require_once './Conexao.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido."]);
    exit();
}

try {
    
    $sql = "SELECT id, nome, preco, categoria FROM produto WHERE created_at IS NOT NULL ORDER BY created_at DESC";
    
    $stmt = $conexao->prepare($sql);
    $stmt->execute();
    
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(
     $produtos 
    );

} catch (PDOException $e) {
    http_response_code(500); 
    error_log("Erro de PDO na listagem de produtos: " . $e->getMessage()); 
    
    echo json_encode([
        "status" => "error",
        "message" => "Erro interno ao listar os produtos."
    ]);
}
// Este é o script listagem de PRODUTOS. Certifique-se de salvar como `listar_produto.php`.