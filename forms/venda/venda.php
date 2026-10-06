<?php

$dados = [
    "dataVenda" => trim($_POST["dataVenda"] ?? ""),
    "horaVenda" => trim($_POST["horaVenda"] ?? ""),
    "formaPagamento" => trim($_POST["formaPagamento"] ?? ""),
    "descontoAplicado" => trim($_POST["descontoAplicado"] ?? ""),
    "cupomFiscal" => trim($_POST["cupomFiscal"] ?? "")
];

$status = "Sucesso";
$msg = "Venda cadastrada com sucesso!";

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

if (!in_array($dados["formaPagamento"], ["pix", "cartao", "dinheiro"])) {
    $status = "Erro";
    $msg = "A forma de pagamento selecionada é inválida.";
}

if (!is_numeric((float)$dados["descontoAplicado"])) {
    $status = "Erro";
    $msg = "O desconto deve ser um número.";
}

if ((float)$dados["descontoAplicado"] < 0 || (float)$dados["descontoAplicado"] > 100) {
    $status = "Erro";
    $msg = "O desconto deve estar entre 0% e 100%.";
}

echo json_encode([
    "status" => $status,
    "mensagem" => $msg,
    "dados" => $dados
], JSON_UNESCAPED_UNICODE);

?>