<?php

require "./../../db/dbconnect.php"; 

header("Content-Type: application/json; charset=UTF-8");

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo == "POST") {
    $dados = [
        "nome" => trim($_POST["nome"] ?? ""),
        "data_nascimento" => trim($_POST["dataNasc"] ?? ""),
        "nacionalidade" => trim($_POST["nacionalidade"] ?? ""),
        "resumo" => trim($_POST["bibliografia"] ?? ""),
        "genero" => trim($_POST["genero"] ?? "")
    ];
    $resposta = verificarDados($dados);

    if (json_decode($resposta)->status === "Sucesso") {
        $dados = json_decode($resposta, true);
        $resultado = insert("autor", $dados["dados"]);
        echo '{"status": "Sucesso", "mensagem": "Autor cadastrado com sucesso!", "dados": '. json_encode($resultado) . ' }';
    } else {
        echo $resposta;
    }
}
else if ($metodo == "GET") {
    $dados = findAll( "autor" ); 
    echo '{"dados": '. json_encode($dados) . ' }';
}
else if ($metodo == "DELETE") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    $id = $dados["id"] ?? null;
    if ($id) {
        $dados = delete("autor", $id);
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
            $resultado = update("autor", $id, $dadosAtualizacao);

            echo json_encode([
                "status" => "Sucesso",
                "mensagem" => "Autor atualizado com sucesso!",
                "dados" => $resultado
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo $resposta;
        }
    }
}

function verificarDados($dados) {
    $status = "Sucesso";
    $msg = "Autor cadastrado com sucesso!";

    foreach ($dados as $campo => $valor) {
        if (empty($valor)) {
            $status = "Erro";
            $msg = "O campo '$campo' e obrigatorio.";
            return json_encode(["status" => $status, "mensagem" => $msg, "dados" => $dados], JSON_UNESCAPED_UNICODE);
        }
    }
    return json_encode(["status" => $status, "mensagem" => $msg, "dados" => $dados], JSON_UNESCAPED_UNICODE);
}

?>
