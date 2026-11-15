<?php

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

    if (json_decode($resposta)->status === "Sucesso") {
        $dados = json_decode($resposta, true);
        $resultado = insert("fornecedor", $dados["dados"]);
        echo '{"status": "Sucesso", "mensagem": "Fornecedor cadastrado com sucesso!", "dados": '. json_encode($resultado) . ' }';
    } else {
        echo $resposta;
    }
} 
else if ($metodo == "GET") {
    $dados = findAll( "fornecedor" ); 
    echo '{"dados": '. json_encode($dados) . ' }';
}
else if ($metodo == "DELETE") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    $id = $dados["id"] ?? null;
    if ($id) {
        $dados = delete("fornecedor", $id);
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

            $resultado = update("fornecedor", $id, $dadosAtualizacao);

            echo json_encode([
                "status" => "Sucesso",
                "mensagem" => "Fornecedor atualizado com sucesso!",
                "dados" => $resultado
            ], JSON_UNESCAPED_UNICODE);

        } else {
            echo $resposta;
        }
    }
}

function verificarDados($dados) {

    $status = "Sucesso";
    $msg = "Fornecedor cadastrado com sucesso!";

    foreach ($dados as $campo => $valor) {
        if (empty($valor)) {
            $status = "Erro";
            $msg = "O campo '$campo' é obrigatório.";
            return json_encode([
                "status" => $status,
                "mensagem" => $msg,
                "dados" => $dados
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    if (!filter_var($dados["email"], FILTER_VALIDATE_EMAIL)) {
        $status = "Erro";
        $msg = "O e-mail informado é inválido.";
    }

    if (!is_numeric((float)$dados["valor_minimo"])) {
        $status = "Erro";
        $msg = "O valor mínimo do pedido deve ser um número.";
    }
    if ((float)$dados["valor_minimo"] < 0) {
        $status = "Erro";
        $msg = "O valor mínimo do pedido não pode ser negativo.";
    }

    return json_encode([
        "status" => $status,
        "mensagem" => $msg,
        "dados" => $dados
    ], JSON_UNESCAPED_UNICODE);
}

?>
