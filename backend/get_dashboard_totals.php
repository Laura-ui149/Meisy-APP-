<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once './Conexao.php'; 
$id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 51;
$sessionToken = isset($_POST['session_token']) ? $_POST['session_token'] : '';

if ($id_usuario <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de usuário inválido.']);
    exit();
}

$totalVendas = 0;
$totalPagamentos = 0;
$response = [];

try {
   
    $sqlVendas = "SELECT COALESCE(SUM(valor), 0) AS total FROM venda WHERE id_usuario = :id_usuario";
    $stmtVendas = $conexao->prepare($sqlVendas); 
    $stmtVendas->execute([':id_usuario' => $id_usuario]);
    $totalVendas = $stmtVendas->fetchColumn(); 

   
    $sqlPagamentos = "SELECT COALESCE(SUM(valor), 0) AS total FROM pagamentos WHERE id_usuario = :id_usuario";
    $stmtPagamentos = $conexao->prepare($sqlPagamentos); 
    $stmtPagamentos->execute([':id_usuario' => $id_usuario]);
    $totalPagamentos = $stmtPagamentos->fetchColumn();

    $response = [
        'success' => true,
        'data' => [
            'total_vendas' => (float)$totalVendas, 
            'total_pagamentos' => (float)$totalPagamentos
        ]
    ];

}  catch (PDOException $e) { 
    $response = [
        'success' => false,
        'message' => 'Erro ao consultar o banco de dados (PDO)',
        'error_detail' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ];

} finally {

}

echo json_encode($response);
?>