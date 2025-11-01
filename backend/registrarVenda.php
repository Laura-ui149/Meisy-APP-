<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require_once './Conexao.php';

try {
    $valor = $_POST['valor'];
    $produto_id = $_POST['produto_id'];
    $quantidade = $_POST['quantidade'];
    $data_venda = $_POST['data_venda'];

    if (!$valor || !$quantidade) {
        echo json_encode([
            "status" => "error",
            "message" => "Campos obrigatórios não informados."
        ]);
        exit;
    }

    $sql = "INSERT INTO venda (valor, produto_id, quantidade, data_venda)
            VALUES (:valor, :produto_id, :quantidade, :data_venda)";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':produto_id', $produto_id);
    $stmt->bindParam(':quantidade', $quantidade);
     $stmt->bindParam(':data_venda', $data_venda);
    $stmt->execute();

    echo json_encode([
        "status" => "success",
        "message" => "Venda registrada com sucesso."
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Erro ao registrar venda: " . $e->getMessage()
    ]);
    exit;
}
?>