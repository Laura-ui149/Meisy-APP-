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
    $nome = $_POST['nome'];
    $sobrenome = $_POST['sobrenome'];    

    $resultado = $conexao->prepare("INSERT INTO clientes (nome,sobrenome) VALUES (:nome, :sobrenome)");
    $resultado->bindParam(':nome' , $nome);
    $resultado->bindParam(':sobrenome' , $sobrenome);    

    $resultado->execute();
   
    }catch(PDOException $e){
        echo $e;
    }

?>