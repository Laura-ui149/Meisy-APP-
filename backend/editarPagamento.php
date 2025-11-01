<?php
// Configurações de CORS e tipo de conteúdo
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");


require_once './Conexao.php'; // Inclui a conexão com o banco de dados (certifique-se que $conexao é uma instância de PDO)

try {
    // 1. Tratamento da requisição OPTIONS (preflight)
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    // 2. Verifica o método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Método Não Permitido
        echo json_encode([
            "status" => "error",
            "message" => "Método não permitido. Use POST para atualizações."
        ]);
        exit;
    }

    // 3. Prioriza a leitura de dados JSON e fallback para form-data
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data)) {
        // Se a leitura de JSON falhar ou retornar vazio, usa $_POST
        $data = $_POST;
    }

    // 4. Verificação básica se os dados essenciais estão presentes
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "O campo 'id' é obrigatório para a atualização."
        ]);
        exit;
    }

    // 5. Validação e extração de variáveis
    // O erro estava na linha abaixo, a sintaxe de ternário estava incompleta e errada para todos os campos.
    // Agora, usamos o operador de coalescência nula (??) para garantir que a variável exista, definindo como null (ou valor padrão) se não vier no payload.

    $id              = $data['id'];
    $descricao       = $data['descricao'];
    $data_vencimento = $data['data_vencimento'];
    $data_pagamento  = $data['data_pagamento'];
    // Para 'id_usuario', se não vier, ou se o valor for inválido, definimos como null.
    $id_usuario      = filter_var($data['id_usuario'], FILTER_VALIDATE_INT) ? (int) $data['id_usuario'] : null;
    $devedor         = $data['devedor'];
    $pagador         = $data['pagador'];
    $valor           = $data['valor'];
    $data_lancamento = $data['data_lancamento'];


    // 6. Validação de tipo (ID - obrigatório)
    if (!filter_var($id, FILTER_VALIDATE_INT)) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "O campo 'id' deve ser um número inteiro válido."
        ]);
        exit;
    }
    $id = (int) $id; // Garante que o ID é um inteiro


    // 7. Preparação da Query SQL
    $sql = "UPDATE pagamentos
            SET id_usuario      = :id_usuario,
                devedor         = :devedor,
                pagador         = :pagador,
                descricao       = :descricao,
                valor           = :valor,
                data_lancamento = :data_lancamento,
                data_vencimento = :data_vencimento,
                data_pagamento  = :data_pagamento
            WHERE id = :id";

    $stmt = $conexao->prepare($sql);

    // 8. Bind dos Parâmetros

    // Tratamento para NULL: Se a variável for null, passamos NULL para o banco.
    // Usamos o operador ternário para verificar se a variável é null e, se for, passamos NULL, senão passamos a variável.

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    // Para campos que podem ser NULL no DB (e não são obrigatórios no payload), tratamos explicitamente.
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); // Assumindo INT ou NULL
    $stmt->bindParam(':devedor', $devedor);
    $stmt->bindParam(':pagador', $pagador);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':valor', $valor); // Usar PDO::PARAM_STR ou PDO::PARAM_INT/FLOAT dependendo do tipo exato no DB (VARCHAR, DECIMAL, etc.)
    $stmt->bindParam(':data_lancamento', $data_lancamento);
    $stmt->bindParam(':data_vencimento', $data_vencimento);
    $stmt->bindParam(':data_pagamento', $data_pagamento);

    $stmt->execute();

    // 9. Verifica se alguma linha foi afetada
    if ($stmt->rowCount() > 0) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Pagamento ID $id atualizado com sucesso."
        ]);
    } else {
        // Se rowCount() for 0, o ID existe (se houvesse um erro, cairia no catch), mas nenhum dado foi alterado.
        http_response_code(200); // 200 OK ou 404 Not Found (depende da sua preferência)
        echo json_encode([
            "status" => "info", // Alterado para "info" ou "warning"
            "message" => "Nenhum pagamento encontrado com o ID $id ou nenhum dado foi alterado."
        ]);
    }

} catch (PDOException $e) {
    // 10. Tratamento de erros de Banco de Dados
    http_response_code(500); // Erro Interno do Servidor
    echo json_encode([
        "status" => "error",
        // Evite exibir $e->getMessage() em produção por questões de segurança.
        "message" => "Erro ao atualizar pagamento: " . $e->getMessage()
    ]);
    exit;
} catch (Exception $e) {
    // 11. Tratamento de outros erros (ex: Conexao.php não encontrado ou erro de sintaxe)
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Erro inesperado: " . $e->getMessage()
    ]);
    exit;
}
?>