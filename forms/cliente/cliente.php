<?php

header("Content-Type: application/json; charset=UTF-8");

$cliente = [
    "nome" => trim($_POST["nome"] ?? ""),
    "data" => trim($_POST["dataNasc"] ?? ""),
    "cpf" => trim($_POST["cpf"] ?? ""),
    "email" => trim($_POST["email"] ?? ""),
    "telefone" => trim($_POST["telefone"] ?? ""),
    "endereco" => trim($_POST["endereco"] ?? ""),
];

if ($cliente['data'] < "1900-01-01" || $cliente['data'] > date("Y-m-d")) {
    echo json_encode([
        "status" => "Erro",
        "mensagem" => "A data de nascimento inválida."
    ]);

    exit;
}
if (!filter_var($cliente["email"], FILTER_VALIDATE_EMAIL)) {
    $status = "Erro";
    $msg = "O e-mail informado é inválido.";
}

if (strlen(preg_replace('/\D/', '', $cliente["cpf"])) != 11) {
    echo json_encode([
        "status" => "Erro",
        "mensagem" => "O CPF deve ter 11 números."
    ]);
    exit;
}

foreach ($cliente as $campo => $valor) {
    if (empty($valor)) {
        echo json_encode([
            "status" => "Erro",
            "mensagem" => "O campo '$campo' é obrigatório."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

echo json_encode([
    "status" => "Sucesso",
    "mensagem" => "Cliente cadastrado com sucesso!"
], JSON_UNESCAPED_UNICODE);

?>