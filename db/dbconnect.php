<?php

include 'dbconfig.php';

function getConnection() {
    $conn = null; 
 
    try { 
        $conn = new PDO( "mysql:host=".HOST.";dbname=".DB_NAME, USER, PASSWORD );
        //echo "ok"; 
    } 
    catch (PDOException $pe) { 
        echo( "Não foi possível se conectar ao banco de dados $dbname :" . $pe );
    }

    return $conn; 
}

function findAll( $table ) { 
    $conn = getConnection(); 
 
    $query = $conn->prepare( "SELECT * FROM " . $table ); 
    $query->execute(); 
 
    $response = $query->fetchAll(PDO::FETCH_ASSOC); 
 
    return $response; 
}

function findById( $table, $id ) { 
    $conn = getConnection(); 
    
    $query = $conn->prepare( "SELECT * FROM " . $table . " WHERE id = " . $id ); 
    
    $query->execute(); 
 
    $response = $query->fetchAll(PDO::FETCH_ASSOC); 
 
    return $response; 
} 

function insert( $table, $dados ) { 
    $conn = getConnection(); 
 
    $colunas = implode(", ", array_keys($dados)); 
    $valores = ":" . implode(", :", array_keys($dados)); 
 
    $query = $conn->prepare( "INSERT INTO " . $table . " (" . $colunas . ") VALUES (" . $valores . ")" ); 
 
    foreach ($dados as $coluna => $valor) {
        $query->bindValue(":$coluna", $valor);
    }
 
    try {
        $query->execute();
        return findById($table, $conn->lastInsertId());
    } catch (PDOException $pe) {
        error_log($pe->getMessage());
        return false;
    }

}

function update($table, $id, $dados) {
    $conn = getConnection();

    if (empty($dados)) {
        return false;
    }

    $campos = [];

    foreach ($dados as $coluna => $valor) {
        $campos[] = "`$coluna` = :$coluna";
    }

    $sql = "UPDATE `$table` SET " . implode(", ", $campos) . " WHERE id = :id";

    try {
        $query = $conn->prepare($sql);

        foreach ($dados as $coluna => $valor) {
            $query->bindValue(":$coluna", $valor);
        }

        $query->bindValue(":id", $id, PDO::PARAM_INT);

        return $query->execute();

    } catch (PDOException $pe) {
        error_log($pe->getMessage());
        return false;
    }
}

function delete( $table, $id ) { 
    $conn = getConnection(); 
 
    $query = $conn->prepare( "DELETE FROM " . $table . " WHERE id = " . $id ); 
         
    try { 
        $query->execute(); 
        return true; 
    } 
    catch( PDOException $pe ) {  
        echo( "Não foi possível se conectar ao banco de dados $dbname :" . $pe ); 
    } 
    
    return false; 
}

?>