<?php

    require_once './Conexao.php';

    try{

        $token = $_GET['token'];
        $user_id = $_GET['user_id'];

        //Selecionar do Banco de dados o token o user_id
        $vericaToken = "SELECT * FROM login WHERE token = :token AND id = :user_id";
        $verificaTokenPDO = $conexao->prepare($vericaToken);
        $verificaTokenPDO->bindParam(':token' , $token);
        $verificaTokenPDO->bindParam(':user_id' , $user_id);
        $verificaTokenPDO->execute();
        $verificaTokenPDOResultado = $verificaTokenPDO->fetch(PDO::FETCH_ASSOC);

        if($verificaTokenPDOResultado){

            $editaBanco = "UPDATE login SET verified = 1 WHERE token = :token AND id = :user_id";
            $editalink = $conexao->prepare($editaBanco);
            $editalink->bindParam(':token', $token);
            $editalink->bindParam(':user_id', $user_id);
            $editalink->execute();
            //$editalinResultado =  $editalink;
            header("Location: sucesso.html");
            exit();
        }else{
            echo "Não foi pssível verificar sua Conta";
        }

    }
    catch(PDOException $ex){
        echo "Erro de Conexão com Servidor";
    }