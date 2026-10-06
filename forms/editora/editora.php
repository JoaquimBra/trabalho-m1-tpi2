<?php

header("Content-Type: application/json; charset=UTF-8");

$dados = [
    "nomeEditora" => trim($_POST["nomeEditora"] ?? ""),
    "cnpj" => trim($_POST["cnpj"] ?? ""),
    "enderecoComercial" => trim($_POST["enderecoComercial"] ?? ""),
    "telefoneContato" => trim($_POST["telefoneContato"] ?? ""),
    "valorMinimoPedido" => trim($_POST["valorMinimoPedido"] ?? "")
];

$status = "Sucesso";
$msg = "Editora cadastrada com sucesso!";

foreach ($dados as $campo => $valor) {
    if (empty($valor)) {
        $status = "Erro";
        $msg = "O campo '$campo' é obrigatório.";
        echo json_encode([
            "status" => $status,
            "mensagem" => $msg,
            "dados" => $dados
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (strlen(preg_replace('/\D/', '', $dados["cnpj"])) != 14) {
    $status = "Erro";
    $msg = "O CNPJ deve conter exatamente 14 números.";
} else {
    $dados["cnpj"] = preg_replace('/\D/', '', $dados["cnpj"]);
}

$telVerificacao = preg_replace('/\D/', '', $dados["telefoneContato"]);
if (strlen($telVerificacao) < 10 || strlen($telVerificacao) > 11) {
    $status = "Erro";
    $msg = "O telefone deve possuir 10 ou 11 dígitos.";
}

if ((float)!is_numeric($editora["valorMinimoPedido"])) {
    $status = "Erro";
    $msg = "O valor mínimo do pedido deve ser um número.";
}

if ((float)$dados["valorMinimoPedido"] < 0){
    $status = "Erro";
    $msg = "O valor minimo do pedido não pode ser menor que 0";
}


echo json_encode([
    "status" => $status,
    "mensagem" => $msg,
    "dados" => $dados
], JSON_UNESCAPED_UNICODE);

?>