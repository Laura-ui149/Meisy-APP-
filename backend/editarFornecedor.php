<?php
// Configurações de CORS e tipo de conteúdo
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");


require_once './Conexao.php'; // Inclui a conexão com o banco de dados

try {
    // 1. Tratamento da requisição OPTIONS (preflight)
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    // 2. Verifica o método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Método Não Permitido
        echo json_encode([
            "status" => "error",
            "message" => "Método não permitido. Use POST."
        ]);
        exit;
    }

    // 3. Prioriza a leitura de dados JSON
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data)) {
        $data = $_POST; // Fallback para form-data
    }

    // 4. Mapeamento e obtenção dos dados (usando ?? null para segurança)
    $id        = $data['id'];
    $nome      = $data['nome'];
    $cnpj      = $data['cnpj'];
    $telefone  = $data['telefone'];
    $endereco  = $data['endereco'];

    // 6. Validação de tipo (ID)
    if (!filter_var($id, FILTER_VALIDATE_INT)) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "O campo 'id' deve ser um número inteiro."
        ]);
        exit;
    }
    $id = (int) $id;

    // REMOVIDO: As validações de 'preco' não fazem sentido para um Fornecedor.
    /*
    if (!is_numeric($preco) || $preco <= 0) { ... }
    $preco_formatado = number_format((float)$preco, 2, '.', '');
    */
    
    // 7. Preparação da query SQL (Corrigido: Adicionada a vírgula para 'endereco')
    $sql = "UPDATE fornecedores 
            SET nome = :nome,
                cnpj = :cnpj,
                telefone = :telefone, 
                endereco = :endereco
            WHERE id = :id";

    $stmt = $conexao->prepare($sql);
    
    // 8. Bind dos parâmetros com tipos PDO explícitos (ajustados para as colunas)
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
    // CNPJ e Telefone geralmente são tratados como STRING no banco para preservar zeros à esquerda
    $stmt->bindParam(':cnpj', $cnpj, PDO::PARAM_STR); 
    $stmt->bindParam(':telefone', $telefone, PDO::PARAM_STR); 
    // Novo campo: Endereço
    $stmt->bindParam(':endereco', $endereco, PDO::PARAM_STR); 

    $stmt->execute();
    
    // 9. Verifica se alguma linha foi afetada
    if ($stmt->rowCount() > 0) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            // Corrigida a mensagem de sucesso
            "message" => "Fornecedor atualizado com sucesso." 
        ]);
    } else {
        http_response_code(404); // Não Encontrado
        echo json_encode([
            "status" => "warning",
            // Corrigida a mensagem de warning
            "message" => "Nenhum fornecedor encontrado com o ID fornecido ou nenhum dado foi alterado."
        ]);
    }

} catch (PDOException $e) {
    // 10. Tratamento de erros de Banco de Dados
    http_response_code(500); // Erro Interno do Servidor
    echo json_encode([
        "status" => "error",
        "message" => "Erro no banco de dados: " . $e->getMessage()
    ]);
    exit;
} catch (Exception $e) {
    // 11. Tratamento de outros erros
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Erro inesperado: " . $e->getMessage()
    ]);
    exit;
}
?>