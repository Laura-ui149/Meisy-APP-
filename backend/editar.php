<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    //Conexão com Banco de Dados 
    try{
        $conexao = new PDO('mysql:host=localhost;dbname=tripwiser_brian', 'tripwiser_brian', 'VqdQgbuh25ff7RKuUQ95');
       // $conexao = new PDO('mysql:host=localhost;dbname=flutterapp', 'root', '');
        $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      //  echo "ok";

      
    }catch(PDOException $e){
        echo $e;
    }

?>

<?php

    try{

    //Declara os atributos do Banco
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $sobrenome = $_POST['sobrenome'];    

    $resultado = $conexao->prepare("UPDATE clientes SET nome = :nome, sobrenome = :sobrenome WHERE id = :id ");
    $resultado->bindParam(':id' , $id, PDO::PARAM_INT);
    $resultado->bindParam(':nome' , $nome, PDO::PARAM_STR);
    $resultado->bindParam(':sobrenome' , $sobrenome, PDO::PARAM_STR);
   

    if($resultado->execute()){
        echo json_encode(['status' => 'success', 'message' => 'Cadastro com Sucesso' ]);
    }else{
        echo json_encode(['status' => 'error' , 'message' => 'Não foi possível Deletar' ]);
    }
   
    }catch(PDOException $e){
         echo json_encode(['status' => 'error' , 'message' => $ex ]);
         echo $e;
    }

?>