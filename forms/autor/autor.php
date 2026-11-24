<?php

require "./../login/sessao.php";
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

    if ($resposta["status"] === "Sucesso") {
        $resultado = insert("autor", $resposta["dados"]);
        echo json_encode([
            "status" => "Sucesso",
            "mensagem" => "Autor cadastrado com sucesso!",
            "dados" => $resultado
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
    }
}
else if ($metodo == "GET") {
    $dados = findAll("autor");
    echo json_encode(["dados" => $dados], JSON_UNESCAPED_UNICODE);
}
else if ($metodo == "DELETE") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    $id = $dados["id"] ?? null;
    if ($id) {
        $resultado = delete("autor", $id);
        echo json_encode(["dados" => $resultado], JSON_UNESCAPED_UNICODE);
    }
}
else if ($metodo == "UPDATE" || $metodo == "PUT") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    $id = $dados["id"] ?? null;
    if ($id) {
        $resposta = verificarDados($dados);

        if ($resposta["status"] === "Sucesso") {
            $dadosAtualizacao = $resposta["dados"];
            unset($dadosAtualizacao["id"]);
            $resultado = update("autor", $id, $dadosAtualizacao);

            echo json_encode([
                "status" => "Sucesso",
                "mensagem" => "Autor atualizado com sucesso!",
                "dados" => $resultado
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
        }
    }
}

function verificarDados($dados) {
    foreach ($dados as $campo => $valor) {
        if ($valor === "" || $valor === null) {
            return ["status" => "Erro", "mensagem" => "O campo '$campo' é obrigatório.", "dados" => $dados];
        }
    }

    $nascimento = DateTime::createFromFormat("Y-m-d", $dados["data_nascimento"]);
    $hoje = new DateTime();
    if (!$nascimento || $nascimento > $hoje) {
        return ["status" => "Erro", "mensagem" => "A data de nascimento é inválida.", "dados" => $dados];
    }

    $idade = $hoje->diff($nascimento)->y;
    if ($idade < 18) {
        return ["status" => "Erro", "mensagem" => "O autor deve ter no mínimo 18 anos.", "dados" => $dados];
    }

    return ["status" => "Sucesso", "mensagem" => "Autor cadastrado com sucesso!", "dados" => $dados];
}

?>
