<?php

header("Content-Type: application/json; charset=UTF-8");

$dados = [
    "nomeEmpresa" => trim($_POST["nomeEmpresa"] ?? ""),
    "emailPedidos" => trim($_POST["emailPedidos"] ?? ""),
    "prazoEntrega" => trim($_POST["prazoEntrega"] ?? ""),
    "condicaoPagamento" => trim($_POST["condicaoPagamento"] ?? ""),
    "valorPedido" => trim($_POST["valorPedido"] ?? "")
];

$status = "Sucesso";
$msg = "Fornecedor cadastrado com sucesso!";

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

if (!filter_var($dados["emailPedidos"], FILTER_VALIDATE_EMAIL)) {
    $status = "Erro";
    $msg = "O e-mail informado é inválido.";
}

if (!is_numeric((float)$dados["valorPedido"])) {
    $status = "Erro";
    $msg = "O valor mínimo do pedido deve ser um número.";
}
if ((float)$dados["valorPedido"] < 0) {
    $status = "Erro";
    $msg = "O valor mínimo do pedido não pode ser negativo.";
}

echo json_encode([
    "status" => $status,
    "mensagem" => $msg,
    "dados" => $dados
], JSON_UNESCAPED_UNICODE);

?>