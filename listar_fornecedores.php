<?php
// Configurações de cabeçalho para permitir a comunicação com o Flutter (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Inclua seu arquivo de conexão (que deve usar PDO)
require_once './Conexao.php'; 

// 1. Garante que apenas o método GET seja aceito
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido. Use GET."]);
    exit();
}

try {
    // 2. Query de Seleção de FORNECEDORES - SEM ORDENAÇÃO POR DATA
    // Seleciona todas as colunas necessárias: id, nome, cnpj, telefone, endereco.
    $sql = "SELECT id, nome, cnpj, telefone, endereco 
            FROM fornecedores";
    
    $stmt = $conexao->prepare($sql);
    $stmt->execute();
    
    // 3. Obtém todos os resultados como um array associativo
    $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Retorna status 200 (Sucesso) e os dados em formato JSON
    http_response_code(200);
    echo json_encode($fornecedores);

} catch (PDOException $e) {
    // 5. Tratamento de erro de banco de dados
    http_response_code(500); 
    
    // ATENÇÃO: Se o problema persistir, descomente as duas linhas abaixo
    // temporariamente para ver o erro SQL real e diagnosticar:
    /*
    echo json_encode([
        "status" => "error",
        "message" => "ERRO REAL DO PDO: " . $e->getMessage()
    ]);
    */
    
    // Versão segura (para produção):
    echo json_encode([
        "status" => "error",
        "message" => "Erro interno no servidor ao listar fornecedores."
    ]);
    
    // Registra o erro no log do servidor
    error_log("Erro de PDO na listagem de fornecedores: " . $e->getMessage());
    exit();
}
?>