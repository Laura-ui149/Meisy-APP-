<?php
// Configurações de CORS e tipo de conteúdo
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once './Conexao.php'; // Inclui a conexão com o banco de dados

try {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Método Não Permitido
        echo json_encode([
            "status" => "error",
            "message" => "Método não permitido. Use POST."
        ]);
        exit;
    }

    // 3. Leitura dos dados
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data)) {
        $data = $_POST; 
    }

    $id             = $data['id'];
    $descricao      = $data['descricao'];
    $valor          = $data['valor'];
    $data_vencimento = $data['data_vencimento']; 
    $periodicidade  = $data['periodicidade'];


    $sql = "UPDATE custos_fixos 
            SET descricao = :descricao,
                valor = :valor,
                data_vencimento = :data_vencimento, 
                periodicidade = :periodicidade
            WHERE id = :id";

    $stmt = $conexao->prepare($sql);
    
    // 10. Bind dos parâmetros
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
    $stmt->bindParam(':valor', $valor); 
    $stmt->bindParam(':data_vencimento', $data_vencimento, PDO::PARAM_STR); 
    $stmt->bindParam(':periodicidade', $periodicidade, PDO::PARAM_STR); 

    $stmt->execute();
    
    // 11. Verifica se alguma linha foi afetada
    if ($stmt->rowCount() > 0) {
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Custo Fixo atualizado com sucesso."]);
    } else {
        http_response_code(404);
        echo json_encode(["status" => "warning", "message" => "Nenhum Custo Fixo encontrado com o ID fornecido ou nenhum dado foi alterado."]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro no banco de dados: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro inesperado: " . $e->getMessage()]);
}
?>