<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json"); // <- ADICIONE ESTA LINHA
require_once './Conexao.php';
/*
public function verificaAcento(){
}
public function senhaForte(){
}
*/
try{

    $novoUsuario = $_POST['username'];
    $novaSenha = $_POST['password'];
    $novoEmail = $_POST['email'];
    $novoNome = $_POST['name'];

    if($novoUsuario == '' || $novoUsuario == null){
        $response = array("status" => "error", "message" => "Por favor, preencha o nome do Usuário.");
        echo json_encode($response);
        exit();
    }
    if($novaSenha == '' || $novaSenha == null){
        $response = array("status" => "error", "message" => "Por favor, preencha a Senha.");
        echo json_encode($response);
        exit();
    }
    if($novoEmail == '' || $novoEmail == null){
        $response = array("status" => "error", "message" => "Por favor, preencha a Email.");
        echo json_encode($response);
        exit();
    }
    if($novoNome == '' || $novoNome == null){
        $response = array("status" => "error", "message" => "Por favor, preencha o Nome.");
        echo json_encode($response);
        exit();
    }

    //Gerar Token
    $token = md5(uniqid(rand(), true));

    //Criptograr a Senha
    $novaSenha = password_hash($novaSenha , PASSWORD_DEFAULT);

    //Selecionar o Usuário do Banco Verificar se não existe 
    $usuarioExiste = "SELECT COUNT(*)  as count  FROM login WHERE username = :username";
    $usuarioExistePDO = $conexao->prepare($usuarioExiste); 
    $usuarioExistePDO->bindParam('username', $novoUsuario);
    $usuarioExistePDO->execute();
    $resposta =  $usuarioExistePDO->fetch(PDO::FETCH_ASSOC);

    if($resposta['count'] > 0){
        $response = array("status" => "error", "message" => "Esse usuário já Existe no Base de Dados");
        echo json_encode($response);
        exit();
    }

    // Validar o Email
    if(!filter_var($novoEmail, FILTER_VALIDATE_EMAIL)){
        $response = array("status" => "error", "message" => "Digite um email Válido");
        echo json_encode($response);
        exit();
    }

    //Verificar se o email existe no Banco de dados
    $usuarioExistePerfil = "SELECT COUNT(*)  as count  FROM perfis WHERE email = :email";
    $usuarioExistePDOPerfil = $conexao->prepare($usuarioExistePerfil); 
    $usuarioExistePDOPerfil->bindParam('email', $novoEmail);
    $usuarioExistePDOPerfil->execute();
    $respostaPerfil =  $usuarioExistePDOPerfil->fetch(PDO::FETCH_ASSOC);

    if($respostaPerfil['count'] > 0){
        $response = array("status" => "error", "message" => "Esse email já Existe no Base de Dados");
        echo json_encode($response);
        exit();
    }

    //Inserir no Banco de dados tabela Login
    $inserirUsuario = "INSERT INTO login (username, password, token, verified) VALUES(:username, :password, :token, 0)";
    $inserirUsuarioLogin = $conexao->prepare($inserirUsuario);
    $inserirUsuarioLogin->bindParam(':username', $novoUsuario);
    $inserirUsuarioLogin->bindParam(':password', $novaSenha);
    $inserirUsuarioLogin->bindParam(':token', $token);
    $inserirUsuarioLogin->execute();

    //Recuperar o id do usuario e inserir na tabela Perfis
    $user_id = $conexao->lastInsertId();
    
    $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : 0.00;
    $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : 0.00;
    $rua = isset($_POST['rua']) ? $_POST['rua'] : 'Sem Rua';

    //Inserir na Tebela Perfis
    $inserirUsuarioPerfil = "INSERT INTO perfis (user_id, nome, email, latitude, longitude, rua) VALUES(:user_id, :nome, :email, :latitude, :longitude, :rua)";
    $inserirUsuarioLoginPerfil = $conexao->prepare($inserirUsuarioPerfil);
    $inserirUsuarioLoginPerfil->bindParam(':user_id', $user_id);
    $inserirUsuarioLoginPerfil->bindParam(':nome', $novoNome);
    $inserirUsuarioLoginPerfil->bindParam(':email', $novoEmail);
    $inserirUsuarioLoginPerfil->bindParam(':latitude', $latitude);
    $inserirUsuarioLoginPerfil->bindParam(':longitude', $longitude);
    $inserirUsuarioLoginPerfil->bindParam(':rua', $rua);
    $inserirUsuarioLoginPerfil->execute();

    //Enviar Email
    require_once('envia-email/PHPMailer/class.phpmailer.php');

    //https://tripwiser.com.br/aplicativo/brian/Confirmar.php

    // Envie o e-mail de confirmação
    $verificationLink = "https://tripwiser.com.br/brian/aplicativo/Confirmar.php?token=$token&user_id=$user_id";

    // Configurar o PHPMailer para enviar e-mail
    $Email = new PHPMailer();
    $Email->SetLanguage("br");
    $Email->IsSMTP(); // Habilita o SMTP 
    $Email->SMTPAuth = true; // Ativa e-mail autenticado
    $Email->Host = 'mail.tripwiser.com.br'; // Servidor de envio (verifique com sua hospedagem)
    $Email->Port = '587'; // Porta de envio (verifique com sua hospedagem)
    $Email->Username = 'tds@tripwiser.com.br'; // E-mail que será autenticado
    $Email->Password = 'sbQUC6BUFjwU9GSa68XC'; // Senha do e-mail (substitua pela sua senha)
    // Ativa o envio de e-mails em HTML, se false, desativa.
    $Email->IsHTML(true);
    $Email->SMTPSecure = "sll"; // tls ou sll
    // E-mail do remetente da mensagem
    $Email->From = 'tds@tripwiser.com.br';
    // Nome do remetente do e-mail
    $Email->FromName = utf8_decode($novoEmail);
    // Endereço de destino do e-mail, ou seja, para onde você quer que a mensagem do formulário vá?
    $Email->AddAddress($novoEmail, $novoNome); // Para quem será enviada a mensagem
    // Informando no e-mail o assunto da mensagem
    $Email->Subject = utf8_decode("Confirmação de Cadastro Aplicativo");
    $Email->AddCC('tds@tripwiser.com.br', ''); // Copia
    $Email->Body = <<<body
    Seguem os dados para acesso ao Gerenciador do Sistema APP My Trash<br /><br />
    <strong>Nome:</strong> $novoNome<br />
    <strong>Email:</strong> $novoEmail<br />
    <strong>Usuário:</strong> $novoUsuario<br />
    <strong>Obs:</strong> Você não precisa responder a este e-mail<br/>
    <a href="$verificationLink">Clique aqui para confirmar seu cadastro</a>
body;

    // Verifica se está tudo ok com os parâmetros acima, se não, avisa do erro. Se sim, envia.
    if (!$Email->Send()) {
        $response = array("status" => "error", "message" => "Erro ao enviar E-mail");
    echo json_encode($response);
    }

    // Retorne uma mensagem de sucesso
    $response = array("status" => "success", "message" => "Cadastro bem-sucedido. Verifique seu e-mail para confirmar.");
    echo json_encode($response);

}catch(PDOException $e){
    $response = array("status" => "error", "message" => "Erro de Conexão com o Servidor");
    echo json_encode($response);
    exit();
}