<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once './Conexao.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Método não permitido. Use POST.'
        ]);
        exit;
    }

    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $localizacao = $_POST['localizacao'];
    $data_evento = $_POST['data_evento'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fim = $_POST['hora_fim'];
    $cor = $_POST['cor'];

    if (empty($id) || empty($titulo) || empty($data_evento)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Campos obrigatórios ausentes.'
        ]);
        exit;
    }

    $sql = "UPDATE eventos 
            SET titulo = :titulo, 
                localizacao = :localizacao, 
                data_evento = :data_evento, 
                hora_inicio = :hora_inicio, 
                hora_fim = :hora_fim, 
                cor = :cor
            WHERE id = :id";

    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
    $stmt->bindParam(':localizacao', $localizacao, PDO::PARAM_STR);
    $stmt->bindParam(':data_evento', $data_evento, PDO::PARAM_STR);
    $stmt->bindParam(':hora_inicio', $hora_inicio, PDO::PARAM_STR);
    $stmt->bindParam(':hora_fim', $hora_fim, PDO::PARAM_STR);
    $stmt->bindParam(':cor', $cor, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Evento atualizado com sucesso.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Falha ao atualizar evento.']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro no banco: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro inesperado: ' . $e->getMessage()]);
}
?>
