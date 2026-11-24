<?php

require "./../login/sessao.php";
require "./../../db/dbconnect.php";

header("Content-Type: application/json; charset=UTF-8");

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo == "POST") {
    $dados = [
        "nome" => trim($_POST["nomeEditora"] ?? ""),
        "cnpj" => trim($_POST["cnpj"] ?? ""),
        "endereco" => trim($_POST["enderecoComercial"] ?? ""),
        "telefone" => trim($_POST["telefoneContato"] ?? ""),
        "valor_minimo" => trim($_POST["valorMinimoPedido"] ?? "")
    ];

    $resposta = verificarDados($dados);

    if ($resposta["status"] === "Sucesso") {
        $resultado = insert("editora", $resposta["dados"]);
        echo json_encode([
            "status" => "Sucesso",
            "mensagem" => "Editora cadastrada com sucesso!",
            "dados" => $resultado
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
    }
}
else if ($metodo == "GET") {
    $dados = findAll("editora");
    echo json_encode(["dados" => $dados], JSON_UNESCAPED_UNICODE);
}
else if ($metodo == "DELETE") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    $id = $dados["id"] ?? null;
    if ($id) {
        $resultado = delete("editora", $id);
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
            $resultado = update("editora", $id, $dadosAtualizacao);

            echo json_encode([
                "status" => "Sucesso",
                "mensagem" => "Editora atualizada com sucesso!",
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

    $cnpj = preg_replace('/\D/', '', $dados["cnpj"]);
    if (strlen($cnpj) != 14) {
        return ["status" => "Erro", "mensagem" => "O CNPJ deve conter exatamente 14 números.", "dados" => $dados];
    }
    $dados["cnpj"] = $cnpj;

    $telefone = preg_replace('/\D/', '', $dados["telefone"]);
    if (strlen($telefone) < 9 || strlen($telefone) > 11) {
        return ["status" => "Erro", "mensagem" => "O telefone deve possuir entre 9 e 11 dígitos.", "dados" => $dados];
    }
    $dados["telefone"] = $telefone;

    if (!is_numeric($dados["valor_minimo"]) || (float)$dados["valor_minimo"] < 0) {
        return ["status" => "Erro", "mensagem" => "O valor mínimo do pedido deve ser um número positivo.", "dados" => $dados];
    }

    return ["status" => "Sucesso", "mensagem" => "Editora cadastrada com sucesso!", "dados" => $dados];
}

?>
