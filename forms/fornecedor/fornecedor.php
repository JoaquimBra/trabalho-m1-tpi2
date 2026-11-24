<?php

require "./../login/sessao.php";
require "./../../db/dbconnect.php";

header("Content-Type: application/json; charset=UTF-8");

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo == "POST") {
    $dados = [
        "nome" => trim($_POST["nomeEmpresa"] ?? ""),
        "email" => trim($_POST["emailPedidos"] ?? ""),
        "prazo_entrega" => trim($_POST["prazoEntrega"] ?? ""),
        "condicao_pagamento" => trim($_POST["condicaoPagamento"] ?? ""),
        "valor_minimo" => trim($_POST["valorPedido"] ?? "")
    ];

    $resposta = verificarDados($dados);

    if ($resposta["status"] === "Sucesso") {
        $resultado = insert("fornecedor", $resposta["dados"]);
        echo json_encode([
            "status" => "Sucesso",
            "mensagem" => "Fornecedor cadastrado com sucesso!",
            "dados" => $resultado
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
    }
}
else if ($metodo == "GET") {
    $dados = findAll("fornecedor");
    echo json_encode(["dados" => $dados], JSON_UNESCAPED_UNICODE);
}
else if ($metodo == "DELETE") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    $id = $dados["id"] ?? null;
    if ($id) {
        $resultado = delete("fornecedor", $id);
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
            $resultado = update("fornecedor", $id, $dadosAtualizacao);

            echo json_encode([
                "status" => "Sucesso",
                "mensagem" => "Fornecedor atualizado com sucesso!",
                "dados" => $resultado
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
        }
    }
}

function verificarDados($dados) {
    $obrigatorios = ["nome", "prazo_entrega", "condicao_pagamento", "valor_minimo"];
    foreach ($obrigatorios as $campo) {
        if ($dados[$campo] === "" || $dados[$campo] === null) {
            return ["status" => "Erro", "mensagem" => "O campo '$campo' é obrigatório.", "dados" => $dados];
        }
    }

    if ($dados["email"] === "") {
        $dados["email"] = null;
    } else if (!filter_var($dados["email"], FILTER_VALIDATE_EMAIL)) {
        return ["status" => "Erro", "mensagem" => "O e-mail informado é inválido.", "dados" => $dados];
    }

    if (!is_numeric($dados["valor_minimo"]) || (float)$dados["valor_minimo"] < 0) {
        return ["status" => "Erro", "mensagem" => "O valor mínimo do pedido deve ser um número positivo.", "dados" => $dados];
    }

    return ["status" => "Sucesso", "mensagem" => "Fornecedor cadastrado com sucesso!", "dados" => $dados];
}

?>
