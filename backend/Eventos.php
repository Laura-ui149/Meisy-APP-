<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require_once './Conexao.php';

try {

     $titulo = $_POST['titulo'];
        $localizacao = $_POST['localizacao'];
        $data_evento = $_POST['data_evento'];
        $hora_inicio = $_POST['hora_inicio'];
        $hora_fim = $_POST['hora_fim']; 
        $cor = $_POST['cor'];

 $sql = "INSERT INTO eventos (titulo, localizacao, data_evento, hora_inicio, hora_fim, cor)
                VALUES (:titulo, :localizacao, :data_evento, :hora_inicio, :hora_fim, :cor)";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':localizacao', $localizacao);
        $stmt->bindParam(':data_evento', $data_evento);
        $stmt->bindParam(':hora_inicio', $hora_inicio);
        $stmt->bindParam(':hora_fim', $hora_fim);
        $stmt->bindParam(':cor', $cor);
        $stmt->execute();

    
  
    echo json_encode([
        "status" => "success",
        "message" => "Produto cadastrado com sucesso.",
        "data" => $result
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "success",
        "message" => "Erro",
        "data" => $result
    ]);
    exit();
}

