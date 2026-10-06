<?php

header("Content-Type: application/json; charset=UTF-8");

$autor = [
    "nome" => trim($_POST["nome"] ?? ""),
    "data" => trim($_POST["dataNasc"] ?? ""),
    "nacionalidade" => trim($_POST["nacionalidade"] ?? ""),
    "bibliografia" => trim($_POST["bibliografia"] ?? ""),
    "genero" => trim($_POST["genero"] ?? ""),
];

if ($autor['data'] < "1900-01-01" || $autor['data'] > date("Y-m-d")) {
    echo json_encode([
        "status" => "Erro",
        "mensagem" => "A data de nascimento inválida."
    ]);

    exit;
}

foreach ($autor as $campo => $valor) {
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