<?php

require "./../../db/dbconnect.php"; 

header("Content-Type: application/json; charset=UTF-8");

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo == "POST") {
    $dados = [
        "nome" => trim($_POST["nome"] ?? ""),
        "data_nascimento" => trim($_POST["dataNasc"] ?? ""),
        "cpf" => trim($_POST["cpf"] ?? ""),
        "email" => trim($_POST["email"] ?? ""),
        "telefone" => trim($_POST["telefone"] ?? ""),
        "endereco" => trim($_POST["endereco"] ?? "")
    ];
    $resposta = verificarDados($dados);

    if (json_decode($resposta)->status === "Sucesso") {
        $dados = json_decode($resposta, true);
        $resultado = insert("cliente", $dados["dados"]);
        echo '{"status": "Sucesso", "mensagem": "Cliente cadastrado com sucesso!", "dados": '. json_encode($resultado) . ' }';
    } else {
        echo $resposta;
    }
}
else if ($metodo == "GET") {
    $dados = findAll( "cliente" ); 
    echo '{"dados": '. json_encode($dados) . ' }';
}
else if ($metodo == "DELETE") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    $id = $dados["id"] ?? null;
    if ($id) {
        $dados = delete("cliente", $id);
        echo '{"dados": '. json_encode($dados) . ' }';
    }
}

else if ($metodo == "UPDATE") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    $id = $dados["id"] ?? null;

    if ($id) {
        $resposta = verificarDados($dados);

        if (json_decode($resposta)->status === "Sucesso") {
            $dados = json_decode($resposta, true);
            $dadosAtualizacao = $dados["dados"];
            unset($dadosAtualizacao["id"]);
            $resultado = update("cliente", $id, $dadosAtualizacao);

            echo json_encode([
                "status" => "Sucesso",
                "mensagem" => "Cliente atualizado com sucesso!",
                "dados" => $resultado
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo $resposta;
        }
    }
}

function verificarDados($dados) {
    $status = "Sucesso";
    $msg = "Cliente cadastrado com sucesso!";
    foreach ($dados as $campo => $valor) {
        if (empty($valor)) {
            $status = "Erro"; $msg = "O campo '$campo' e obrigatorio.";
            return json_encode(["status"=>$status,"mensagem"=>$msg,"dados"=>$dados], JSON_UNESCAPED_UNICODE);
        }
    }
    if ($dados["data_nascimento"] < "1900-01-01" || $dados["data_nascimento"] > date("Y-m-d")) { $status="Erro"; $msg="A data de nascimento e invalida."; }
    if (!filter_var($dados["email"], FILTER_VALIDATE_EMAIL)) { $status="Erro"; $msg="O e-mail informado e invalido."; }
    if (strlen(preg_replace('/\D/', '', $dados["cpf"])) != 11) { $status="Erro"; $msg="O CPF deve ter 11 numeros."; }
    return json_encode(["status"=>$status,"mensagem"=>$msg,"dados"=>$dados], JSON_UNESCAPED_UNICODE);
}

?>
