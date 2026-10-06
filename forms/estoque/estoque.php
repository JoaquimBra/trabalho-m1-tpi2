<?php

header("Content-Type: application/json; charset=UTF-8");

$dados = [
    "tituloLivro" => trim($_POST["tituloLivro"] ?? ""),
    "isbn" => trim($_POST["isbn"] ?? ""),
    "quantidadeAtual" => trim($_POST["quantidadeAtual"] ?? ""),
    "quantidadeMinima" => trim($_POST["quantidadeMinima"] ?? ""),
    "localizacao" => trim($_POST["localizacao"] ?? ""),
    "dataReposicao" => trim($_POST["dataReposicao"] ?? ""),
    "custoUnitario" => trim($_POST["custoUnitario"] ?? "")
];

$status = "Sucesso";
$msg = "Estoque cadastrado com sucesso!";

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

if (!ctype_digit((int)$dados["quantidadeAtual"])) {
    $status = "Erro";
    $msg = "A quantidade atual deve conter apenas números.";
}

if ((int)$dados["quantidadeAtual"] < 0) {
    $status = "Erro";
    $msg = "A quantidade atual não pode ser negativa.";
}

if (!ctype_digit((int)$dados["quantidadeMinima"])) {
    $status = "Erro";
    $msg = "A quantidade atual deve conter apenas números.";
}

if ((int)$dados["quantidadeMinima"] < 0) {
    $status = "Erro";
    $msg = "A quantidade atual não pode ser negativa.";
}

if (!is_numeric((float)$dados["custoUnitario"])) {
    $status = "Erro";
    $msg = "O custo unitário deve ser um número.";
}

if ((float)$dados["custoUnitario"] < 0) {
    $status = "Erro";
    $msg = "O custo unitário não pode ser negativo.";
}

$isbnNumeros = preg_replace('/\D/', '', $livro["isbn"]);
if (strlen($isbnNumeros) != 10 && strlen($isbnNumeros) != 13) {
    $status = "Erro";
    $msg = "O ISBN deve conter 10 ou 13 números.";
}
else {
    $livro["isbn"] = $isbnNumeros;
}

echo json_encode([
    "status" => $status,
    "mensagem" => $msg,
    "dados" => $dados
], JSON_UNESCAPED_UNICODE);

?>