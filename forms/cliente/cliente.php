<?php

require "./../login/sessao.php";
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

    if ($resposta["status"] === "Sucesso") {
        $resultado = insert("cliente", $resposta["dados"]);
        echo json_encode([
            "status" => "Sucesso",
            "mensagem" => "Cliente cadastrado com sucesso!",
            "dados" => $resultado
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
    }
}
else if ($metodo == "GET") {
    $dados = findAll("cliente");
    echo json_encode(["dados" => $dados], JSON_UNESCAPED_UNICODE);
}
else if ($metodo == "DELETE") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    $id = $dados["id"] ?? null;
    if ($id) {
        $resultado = delete("cliente", $id);
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
            $resultado = update("cliente", $id, $dadosAtualizacao);

            echo json_encode([
                "status" => "Sucesso",
                "mensagem" => "Cliente atualizado com sucesso!",
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
        return ["status" => "Erro", "mensagem" => "O cliente deve ter no mínimo 18 anos.", "dados" => $dados];
    }
    if ($idade > 100) {
        return ["status" => "Erro", "mensagem" => "O cliente deve ter no máximo 100 anos.", "dados" => $dados];
    }
    if (!filter_var($dados["email"], FILTER_VALIDATE_EMAIL)) {
        return ["status" => "Erro", "mensagem" => "O e-mail informado é inválido.", "dados" => $dados];
    }

    $cpf = preg_replace('/\D/', '', $dados["cpf"]);
    if (strlen($cpf) != 11) {
        return ["status" => "Erro", "mensagem" => "O CPF deve ter 11 números.", "dados" => $dados];
    }
    $dados["cpf"] = $cpf;

    return ["status" => "Sucesso", "mensagem" => "Cliente cadastrado com sucesso!", "dados" => $dados];
}

?>
