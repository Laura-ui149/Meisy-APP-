<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require_once './Conexao.php';

try {
    $id_usuario = isset($_POST['id_usuario']) ? $_POST['id_usuario'] : '';
    
    // Verifica se o id_usuario foi informado
    if (empty($id_usuario)) {
        echo json_encode([
            "status" => "error",
            "message" => "ID do usuário não informado"
        ]);
        exit();
    }
    
    // Se for apenas para listar, retorna os dados sem inserir
    if (isset($_POST['listar']) && $_POST['listar'] == 'true') {
        $sql = "SELECT * FROM ponto_equilibrio WHERE id_usuario = :id_usuario ORDER BY data DESC";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "success",
            "data" => $result
        ]);
        exit();
    }
    
    // Se não for listagem, é um insert - pega os outros parâmetros
    $data = isset($_POST['data']) ? $_POST['data'] : '';
    $custos_fixos = isset($_POST['custos_fixos']) ? $_POST['custos_fixos'] : '';
    $custos_variaveis = isset($_POST['custos_variaveis']) ? $_POST['custos_variaveis'] : '';
    $preco_venda = isset($_POST['preco_venda']) ? $_POST['preco_venda'] : '';
    $resultado = isset($_POST['resultado']) ? $_POST['resultado'] : '';

    if (empty($data) || empty($custos_fixos) || empty($custos_variaveis) || empty($preco_venda) || empty($resultado)) {
        echo json_encode([
            "status" => "error",
            "message" => "Parâmetros inválidos"
        ]);
        exit();
    }

    // Insere no banco
    $sql = "INSERT INTO ponto_equilibrio (id_usuario, data, custos_fixos, custos_variaveis, preco_venda, resultado) 
            VALUES (:id_usuario, :data, :custos_fixos, :custos_variaveis, :preco_venda, :resultado)";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->bindParam(':data', $data);
    $stmt->bindParam(':custos_fixos', $custos_fixos);
    $stmt->bindParam(':custos_variaveis', $custos_variaveis);
    $stmt->bindParam(':preco_venda', $preco_venda);
    $stmt->bindParam(':resultado', $resultado);
    $stmt->execute();

    // Busca valores inseridos
    $sql = "SELECT * FROM ponto_equilibrio WHERE id_usuario = :id_usuario ORDER BY data DESC";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "message" => "Ponto de equilíbrio cadastrado com sucesso.",
        "data" => $result
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Erro de Conexão com o Servidor",
        "error_details" => $e->getMessage()
    ]);
    exit();
}
?>
