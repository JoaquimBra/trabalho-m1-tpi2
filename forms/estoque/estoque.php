<?php

require "./../login/sessao.php";
require "./../../db/dbconnect.php";

header("Content-Type: application/json; charset=UTF-8");

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo == "POST") {
    $dados = [
        "nome" => trim($_POST["tituloLivro"] ?? ""),
        "isbn" => trim($_POST["isbn"] ?? ""),
        "qtde" => trim($_POST["quantidadeAtual"] ?? ""),
        "qtde_minima" => trim($_POST["quantidadeMinima"] ?? ""),
        "localizacao" => trim($_POST["localizacao"] ?? ""),
        "data_reposicao" => trim($_POST["dataReposicao"] ?? ""),
        "custo_unitario" => trim($_POST["custoUnitario"] ?? ""),
        "id_editora" => trim($_POST["idEditora"] ?? ""),
        "id_autor" => trim($_POST["idAutor"] ?? ""),
        "id_fornecedor" => trim($_POST["idFornecedor"] ?? "")
    ];

    $resposta = verificarDados($dados);

    if ($resposta["status"] === "Sucesso") {
        $resultado = insert("estoque", $resposta["dados"]);
        if ($resultado) {
            echo json_encode([
                "status" => "Sucesso",
                "mensagem" => "Estoque cadastrado com sucesso!",
                "dados" => $resultado
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo '{"status": "Erro", "mensagem": "Erro ao cadastrar estoque."}';
        }
    } else {
        echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
    }
}
else if ($metodo == "GET") {
    $conn = getConnection();
    $query = $conn->prepare(
        "SELECT estoque.*,
                editora.nome AS nome_editora,
                autor.nome AS nome_autor,
                fornecedor.nome AS nome_fornecedor
         FROM estoque
         JOIN editora ON editora.id = estoque.id_editora
         JOIN autor ON autor.id = estoque.id_autor
         JOIN fornecedor ON fornecedor.id = estoque.id_fornecedor
         ORDER BY estoque.id"
    );
    $query->execute();
    $dados = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["dados" => $dados], JSON_UNESCAPED_UNICODE);
}
else if ($metodo == "DELETE") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    $id = $dados["id"] ?? null;
    if ($id) {
        $resultado = delete("estoque", $id);
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
            $resultado = update("estoque", $id, $dadosAtualizacao);

            echo json_encode([
                "status" => "Sucesso",
                "mensagem" => "Estoque atualizado com sucesso!",
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
    if (!ctype_digit((string)$dados["qtde"])) {
        return ["status" => "Erro", "mensagem" => "A quantidade atual deve ser um número inteiro.", "dados" => $dados];
    }
    if (!ctype_digit((string)$dados["qtde_minima"])) {
        return ["status" => "Erro", "mensagem" => "A quantidade mínima deve ser um número inteiro.", "dados" => $dados];
    }
    if (!is_numeric($dados["custo_unitario"]) || (float)$dados["custo_unitario"] < 0) {
        return ["status" => "Erro", "mensagem" => "O custo unitário deve ser um número positivo.", "dados" => $dados];
    }

    $isbn = preg_replace('/\D/', '', $dados["isbn"]);
    if (strlen($isbn) != 10 && strlen($isbn) != 13) {
        return ["status" => "Erro", "mensagem" => "O ISBN deve conter 10 ou 13 números.", "dados" => $dados];
    }
    $dados["isbn"] = $isbn;

    return ["status" => "Sucesso", "mensagem" => "Estoque cadastrado com sucesso!", "dados" => $dados];
}

?>
