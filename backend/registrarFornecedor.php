<?php
// Configurações de cabeçalho
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Inclua seu arquivo de conexão
require_once './Conexao.php'; 

// 1. Verifica se o método é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido. Use POST."]);
    exit();
}

try {
    // 2. Coleta e sanitização de dados POST com verificação
    $nome = $_POST['nome'];
    $cnpj = $_POST['cnpj'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];

    // 3. CORREÇÃO CRÍTICA: Validação dos campos
    if (empty($nome) || empty($cnpj) || empty($telefone) || empty($endereco)) {
        http_response_code(400); // Bad Request
        echo json_encode([
            "status" => "error",
            "message" => "Campos obrigatórios (nome, cnpj, telefone, endereco) não informados."
        ]);
        exit;
    }

    // 4. Inserção no banco de dados (seu código de inserção está correto)
    $sql = "INSERT INTO fornecedores (nome, cnpj, telefone, endereco)
            VALUES (:nome, :cnpj, :telefone, :endereco)";
    
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':cnpj', $cnpj);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':endereco', $endereco);
    
    $stmt->execute();
    
    // 5. CORREÇÃO CRÍTICA: Resposta de Sucesso
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Fornecedor cadastrado com sucesso."
    ]);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    // Trata erros específicos de PDO (ex: CNPJ duplicado)
    echo json_encode([
        "status" => "error", 
        "message" => "Erro de Banco de Dados: " . $e->getMessage(), 
        "code" => $e->getCode()
    ]);
    exit();
} catch (Exception $e) { 
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "status" => "error",
        "message" => "Ocorreu um erro inesperado: " . $e->getMessage()
    ]);
    exit();
}
?>