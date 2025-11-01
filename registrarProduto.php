<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(0);

require_once './Conexao.php'; 

try {
    if (empty($_POST['nome']) || empty($_POST['preco']) || empty($_POST['categoria'])) {
        echo json_encode([
            "status" => "error",
            "message" => "Todos os campos (nome, preco, categoria) são obrigatórios."
        ]);
        exit();
    }
    
    $nome = $_POST['nome'];
    $preco = floatval($_POST['preco']); 
    $categoria = $_POST['categoria'];

    $sql = "INSERT INTO produto (nome, preco, categoria)
            VALUES (:nome, :preco, :categoria)";
            
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':preco', $preco);
    $stmt->bindParam(':categoria', $categoria);
    $stmt->execute();
    
    echo json_encode([
        "status" => "success", 
        "message" => "Produto cadastrado com sucesso!" 
    ]);
    exit();

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error", 
        "message" => "Erro ao cadastrar produto: " . $e->getMessage(), 
        "code" => $e->getCode()
    ]);
    exit();
} catch (Exception $e) { 
    echo json_encode([
        "status" => "error",
        "message" => "Ocorreu um erro inesperado: " . $e->getMessage()
    ]);
    exit();
}
?>