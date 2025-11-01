<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once './Conexao.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Método não permitido."]);
    exit;
}

try {
 
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $data_vencimento = $_POST['data_vencimento'];
    $periodicidade = $_POST['periodicidade'];

    if (!$descricao || !$valor || !$data_vencimento || !$periodicidade) {
        echo json_encode([
            "status" => "error",
            "message" => "Campos obrigatórios: descricao, valor, data_vencimento ou periodicidade não informados."
        ]);
        exit;
    }
    
    if (!is_numeric($valor) || floatval($valor) <= 0) {
        echo json_encode([
            "status" => "error",
            "message" => "O valor deve ser um número positivo válido."
        ]);
        exit;
    }


    $sql = "INSERT INTO custos_fixos (descricao, valor, data_vencimento, periodicidade)
            VALUES (:descricao, :valor, :data_vencimento, :periodicidade)";
    $stmt = $conexao->prepare($sql);
    
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':data_vencimento', $data_vencimento);
    $stmt->bindParam(':periodicidade', $periodicidade);
    
    $stmt->execute();

    echo json_encode([
        "status" => "success",
        "message" => "Custo Fixo registrado com sucesso."
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Erro ao registrar Custo Fixo: " . $e->getMessage()
    ]);
    exit;
}
?>