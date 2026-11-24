<?php

require "./../login/sessao.php";
require "./../../db/dbconnect.php";

header("Content-Type: application/json; charset=UTF-8");

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo == "POST") {
    $dados = [
        "id_cliente" => trim($_POST["idCliente"] ?? ""),
        "data_venda" => str_replace("T", " ", trim($_POST["dataVenda"] ?? "")),
        "pagamento" => trim($_POST["formaPagamento"] ?? ""),
        "desconto" => trim($_POST["descontoAplicado"] ?? "0"),
        "cupom_fiscal" => trim($_POST["cupomFiscal"] ?? "")
    ];

    $resposta = verificarDados($dados);
    if ($resposta["status"] !== "Sucesso") {
        echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $itensEstoque = $_POST["itemEstoque"] ?? [];
    $itensQuantidade = $_POST["itemQuantidade"] ?? [];

    if (count($itensEstoque) == 0) {
        echo '{"status": "Erro", "mensagem": "Adicione pelo menos um item à venda."}';
        exit;
    }

    // Soma as quantidades por livro e verifica o estoque
    $totais = [];
    foreach ($itensEstoque as $i => $idEstoque) {
        $qtd = (int)($itensQuantidade[$i] ?? 1);
        if (!$idEstoque || $qtd <= 0) {
            echo '{"status": "Erro", "mensagem": "Selecione um livro e quantidade válidos."}';
            exit;
        }
        $totais[(int)$idEstoque] = ($totais[(int)$idEstoque] ?? 0) + $qtd;
    }

    foreach ($totais as $idEstoque => $qtd) {
        $livro = findById("estoque", $idEstoque);
        if (!$livro || $livro[0]["qtde"] < $qtd) {
            $nomeLivro = $livro ? $livro[0]["nome"] : "ID $idEstoque";
            echo json_encode([
                "status" => "Erro",
                "mensagem" => "Estoque insuficiente para '$nomeLivro'."
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Cadastra a venda
    $venda = insert("venda", $resposta["dados"]);
    if (!$venda) {
        echo '{"status": "Erro", "mensagem": "Erro ao cadastrar venda."}';
        exit;
    }

    $idVenda = $venda[0]["id"];

    // Cadastra os itens da venda e dá baixa no estoque
    foreach ($totais as $idEstoque => $qtd) {
        insert("item_venda", [
            "id_estoque" => $idEstoque,
            "id_venda" => $idVenda,
            "quantidade" => $qtd
        ]);

        $livroAtual = findById("estoque", $idEstoque);
        update("estoque", $idEstoque, [
            "qtde" => $livroAtual[0]["qtde"] - $qtd
        ]);
    }

    echo json_encode([
        "status" => "Sucesso",
        "mensagem" => "Venda cadastrada com sucesso!",
        "dados" => $venda
    ], JSON_UNESCAPED_UNICODE);
}
else if ($metodo == "GET") {
    $conn = getConnection();

    $query = $conn->prepare(
        "SELECT venda.*, cliente.nome AS nome_cliente
         FROM venda
         JOIN cliente ON cliente.id = venda.id_cliente
         ORDER BY venda.id"
    );
    $query->execute();
    $vendas = $query->fetchAll(PDO::FETCH_ASSOC);

    $queryItens = $conn->prepare(
        "SELECT item_venda.*, estoque.nome AS nome_livro
         FROM item_venda
         JOIN estoque ON estoque.id = item_venda.id_estoque
         WHERE item_venda.id_venda = :id_venda"
    );

    foreach ($vendas as &$venda) {
        $queryItens->execute([":id_venda" => $venda["id"]]);
        $venda["itens"] = $queryItens->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($venda);

    echo json_encode(["dados" => $vendas], JSON_UNESCAPED_UNICODE);
}
else if ($metodo == "DELETE") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    $id = $dados["id"] ?? null;
    if ($id) {
        // Repõe os itens no estoque antes de excluir a venda
        $conn = getConnection();
        $query = $conn->prepare("SELECT id_estoque, quantidade FROM item_venda WHERE id_venda = :id");
        $query->execute([":id" => $id]);
        $itens = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($itens as $item) {
            $livro = findById("estoque", $item["id_estoque"]);
            if ($livro) {
                update("estoque", $item["id_estoque"], [
                    "qtde" => $livro[0]["qtde"] + $item["quantidade"]
                ]);
            }
        }

        $resultado = delete("venda", $id);
        echo json_encode(["dados" => $resultado], JSON_UNESCAPED_UNICODE);
    }
}
else if ($metodo == "UPDATE" || $metodo == "PUT") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    $id = $dados["id"] ?? null;
    if ($id) {
        $dados["data_venda"] = str_replace("T", " ", trim($dados["data_venda"] ?? ""));
        $resposta = verificarDados($dados);

        if ($resposta["status"] === "Sucesso") {
            $dadosAtualizacao = $resposta["dados"];
            unset($dadosAtualizacao["id"]);
            $resultado = update("venda", $id, $dadosAtualizacao);

            echo json_encode([
                "status" => "Sucesso",
                "mensagem" => "Venda atualizada com sucesso!",
                "dados" => $resultado
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
        }
    }
}

function verificarDados($dados) {
    $pagamentos = ["dinheiro", "pix", "cartao_credito", "cartao_debito", "boleto"];

    if (($dados["id_cliente"] ?? "") === "") {
        return ["status" => "Erro", "mensagem" => "Selecione um cliente.", "dados" => $dados];
    }
    if (($dados["data_venda"] ?? "") === "") {
        return ["status" => "Erro", "mensagem" => "Informe a data da venda.", "dados" => $dados];
    }
    if (!in_array($dados["pagamento"] ?? "", $pagamentos)) {
        return ["status" => "Erro", "mensagem" => "Forma de pagamento inválida.", "dados" => $dados];
    }

    if (($dados["desconto"] ?? "") === "") {
        $dados["desconto"] = 0;
    }
    if (!is_numeric($dados["desconto"]) || $dados["desconto"] < 0 || $dados["desconto"] > 100) {
        return ["status" => "Erro", "mensagem" => "O desconto deve estar entre 0% e 100%.", "dados" => $dados];
    }

    if (($dados["cupom_fiscal"] ?? "") === "") {
        $dados["cupom_fiscal"] = null;
    }

    return ["status" => "Sucesso", "mensagem" => "Venda cadastrada com sucesso!", "dados" => $dados];
}

?>
