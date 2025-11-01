<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require_once './Conexao.php';

try {
    // Dados Flutter
    $id_usuario      = $_POST['id_usuario'];
    $devedor         = $_POST['devedor'];
    $pagador         = $_POST['pagador'];
    $descricao       = $_POST['descricao'] ;
    $valor           = $_POST['valor'];
    $data_lancamento = $_POST['data_lancamento'];
    $data_vencimento = $_POST['data_vencimento'];
    $data_pagamento  = $_POST['data_pagamento'];

    // Verificação básica
    if (empty($id_usuario) || empty($devedor) || empty($pagador) || empty($valor) || empty($data_lancamento)) {
        echo json_encode([
            "status" => "error",
            "message" => "Parâmetros obrigatórios ausentes"
        ]);
        exit();
    }

    // Insere no banco
    $sql = "INSERT INTO pagamentos 
            (id_usuario, devedor, pagador, descricao, valor, data_lancamento, data_vencimento, data_pagamento) 
            VALUES 
            (:id_usuario, :devedor, :pagador, :descricao, :valor, :data_lancamento, :data_vencimento, :data_pagamento)";

    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->bindParam(':devedor', $devedor, PDO::PARAM_STR);
    $stmt->bindParam(':pagador', $pagador, PDO::PARAM_STR);
    $stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':data_lancamento', $data_lancamento);
    $stmt->bindParam(':data_vencimento', $data_vencimento);
    $stmt->bindParam(':data_pagamento', $data_pagamento);

    $stmt->execute();

    echo json_encode([
        "status" => "success",
        "message" => "Lançamento cadastrado com sucesso."
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Erro de Conexão com o Servidor"
    ]);
    exit();
}
