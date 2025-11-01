<?php
ini_set('session.gc_maxlifetime', 60); // Define o tempo de expiração da sessão para 60 segundos (1 minuto)
session_start();
require "./Conexao.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = array();

    $usuarioOuEmail = $_POST['username'];
    $password = $_POST['password'];

    // Remova espaços em branco extras antes e depois do valor inserido
    $usuarioOuEmail = trim($usuarioOuEmail);

    // Verifique se os campos não estão em branco
    if (empty($usuarioOuEmail) || empty($password)) {
        $data['msg'] = "Campos em branco";
        echo json_encode($data);
    } else {
        // Verifique se a entrada é um endereço de e-mail válido
        if (filter_var($usuarioOuEmail, FILTER_VALIDATE_EMAIL)) {
            $sql = "SELECT login.*, perfis.nome, perfis.email, perfis.user_id, perfis.latitude, perfis.longitude FROM login
                LEFT JOIN perfis ON login.id = perfis.user_id
                WHERE perfis.email = :username";
        } else {
            $sql = "SELECT login.*, perfis.nome, perfis.email, perfis.user_id, perfis.latitude, perfis.longitude FROM login
                LEFT JOIN perfis ON login.id = perfis.user_id
                WHERE login.username = :username";
        }

        $result = $conexao->prepare($sql);
        $result->bindParam(':username', $usuarioOuEmail);
        $result->execute();
        $cek = $result->fetch();

        if (isset($cek) && $cek != null) {
            // Verifique se a conta está verificada
            if ($cek['verified'] == 1) {
                // Verifique a senha usando password_verify()
                $hashedPassword = $cek['password'];
                if (password_verify($password, $hashedPassword)) {
                    
                    $nome = $cek['nome'];
                    $email = $cek['email'];
                    $user_id = $cek['user_id'];
                    $latitude = $cek['latitude'];
                    $longitude = $cek['longitude'];
                    $data['msg'] = "";
                    $data['level'] = isset($cek['level']) ? $cek['level'] : ""; // Defina um valor padrão se 'level' não estiver presente
                    $data['username'] = $cek['username'];
                    $data['nome'] = $nome;
                    $data['email'] = $email;
                    $data['latitude'] = $latitude;
                    $data['longitude'] = $longitude;
                    $data['user_id'] = $user_id;
                    $data['password'] = $hashedPassword;

                
                    $_SESSION["loggedin"] = true;
                    $_SESSION["user_id"] = $user_id;
                    $_SESSION["username"] = $usuarioOuEmail;
                    $sessionToken = sha1(uniqid());

                    // Defina o sessionToken na sua sessão
                    $_SESSION['session_token'] = $sessionToken;
                    // Defina a hora da última atividade
                    $_SESSION['ultimaAtividade'] = time(); // Registra a hora atual


                    $sql = "INSERT INTO session_token (user_id, token, login_time) VALUES (:user_id, :token, NOW())";
                    $result = $conexao->prepare($sql);
                    $result->bindParam(':user_id', $user_id);
                    $result->bindParam(':token', $sessionToken);
                    $result->execute();
                    
                    $data['session_token'] = $sessionToken;
                    header('Accept: application/json');
                    echo json_encode($data);
                } else {
                    $data['msg'] = "Verifique seu Usuário e Senha";
                    echo json_encode($data);
                }
            } else {
                $data['msg'] = "Sua conta ainda não foi verificada. Verifique seu e-mail e clique no link de verificação.";
                echo json_encode($data);
            }
        } else {
            $data['msg'] = "Usuário não encontrado";
            echo json_encode($data);
        }
    }
} else {
    $data['msg'] = "Usuário não encontrado";
    echo json_encode($data);
}
?>
