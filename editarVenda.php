<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require_once './Conexao.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            "status" => "error",
            "message" => "Método não permitido. Use POST."
        ]);
        exit;
    }

    $id           = $_POST['id'];
    $valor        = $_POST['valor'];
    $produto_id   = $_POST['produto_id'];
    $quantidade   = $_POST['quantidade'];
    $data_venda   = $_POST['data_venda'];

    if (empty($id) || empty($valor) || empty($quantidade)) {
        echo json_encode([
            "status" => "error",
            "message" => "Campos obrigatórios ausentes."
        ]);
        exit;
    }

    $sql = "UPDATE venda 
            SET valor = :valor,
                produto_id = :produto_id,
                quantidade = :quantidade,
                data_venda = :data_venda
            WHERE id = :id";

    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':produto_id', $produto_id, PDO::PARAM_INT);
    $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
    $stmt->bindParam(':data_venda', $data_venda, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Venda atualizada com sucesso."
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Falha ao atualizar venda."
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Erro no banco de dados: " . $e->getMessage()
    ]);
    exit;
}
?>
