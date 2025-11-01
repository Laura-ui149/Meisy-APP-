<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require_once './Conexao.php';

function sendResponse($status, $message, $data = [], $httpCode = 200) {
    if ($status === 'error') {
        $httpCode = 500;
    }
    http_response_code($httpCode);
    echo json_encode(array("status" => $status, "message" => $message, "data" => $data));
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse('error', 'Método não permitido. Use GET.', [], 405);
    }
    
    $sql = "SELECT id, titulo, localizacao, data_evento, hora_inicio, hora_fim, cor 
            FROM eventos 
            ORDER BY data_evento ASC, hora_inicio ASC";
    
    $stmt = $conexao->prepare($sql);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events as &$event) {
        // Verifica se a cor NÃO começa com '#'
        if (!empty($event['cor']) && substr($event['cor'], 0, 1) !== '#') {
            $event['cor'] = '#' . $event['cor'];
        }
    }
    unset($event); 

    if (empty($events)) {
        sendResponse('success', 'Nenhum evento encontrado.', []);
    } else {
        sendResponse('success', 'Eventos listados com sucesso.', $events);
    }

} catch (PDOException $e) {
    sendResponse('error', 'Erro ao buscar eventos (PDO): ' . $e->getMessage(), [], 500);
} catch (Exception $e) {
    sendResponse('error', 'Erro interno do servidor: ' . $e->getMessage(), [], 500);
}
?>