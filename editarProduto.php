<?php
// Configurações de CORS e tipo de conteúdo
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Exibir erros (apenas para desenvolvimento)
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

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

    // 3. Prioriza a leitura de dados JSON (melhor para APIs)
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data)) {
        $data = $_POST; // Fallback para form-data
    }

    // 4. Mapeamento e obtenção dos dados
    $id        = $data['id'];
    $nome      = $data['nome'];
    $preco     = $data['preco'];
    $categoria = $data['categoria'];

    // 5. Validação dos campos obrigatórios
    if (empty($id) || empty($nome) || empty($preco) || empty($categoria)) {
        http_response_code(400); // Requisição Inválida
        echo json_encode([
            "status" => "error",
            "message" => "Todos os campos (id, nome, preco, categoria) são obrigatórios."
        ]);
        exit;
    }

    // 6. Validação de tipo (ID e Preço)
    if (!filter_var($id, FILTER_VALIDATE_INT)) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "O campo 'id' deve ser um número inteiro."
        ]);
        exit;
    }
    $id = (int) $id;

    if (!is_numeric($preco) || $preco <= 0) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "O campo 'preco' deve ser um valor numérico positivo válido."
        ]);
        exit;
    }
    $preco_formatado = number_format((float)$preco, 2, '.', ''); 
    
    // 7. Preparação da query SQL (Corrigido: removido a vírgula extra)
    $sql = "UPDATE produto 
            SET nome = :nome,
                preco = :preco,
                categoria = :categoria
            WHERE id = :id";

    $stmt = $conexao->prepare($sql);
    
    // 8. Bind dos parâmetros com tipos PDO explícitos
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
    // Bind sem tipo para o preco_formatado. O banco se encarrega
    $stmt->bindParam(':preco', $preco_formatado); 
    $stmt->bindParam(':categoria', $categoria, PDO::PARAM_STR);

    $stmt->execute();
    
    // 9. Verifica se alguma linha foi afetada
    if ($stmt->rowCount() > 0) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Produto atualizado com sucesso." // Corrigido para Produto
        ]);
    } else {
        http_response_code(404); // Não Encontrado
        echo json_encode([
            "status" => "warning",
            "message" => "Nenhum produto encontrado com o ID fornecido ou nenhum dado foi alterado."
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