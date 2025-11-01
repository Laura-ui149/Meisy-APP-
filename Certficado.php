<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require_once './Conexao.php';

try {
    $user_id = $_POST['user_id'];
    $numero_certificado = $_POST['numero_certificado'];

    if (empty($user_id) || empty($numero_certificado)) {
        echo json_encode([
            "status" => "error",
            "message" => "Parâmetros inválidos"
        ]);
        exit();
    }

    // Insere no banco
    $sql = "INSERT INTO certificados_fiscais (user_id, numero_certificado) 
            VALUES (:user_id, :numero_certificado)";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':numero_certificado', $numero_certificado);
    $stmt->execute();

    echo json_encode([
        "status" => "success",
        "message" => "Certificado cadastrado com sucesso."
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Erro de Conexão com o Servidor"
    ]);
    exit();
}
