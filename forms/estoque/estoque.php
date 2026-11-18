<?php

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
        "custo_unitario" => trim($_POST["custoUnitario"] ?? "")
    ];
    $resposta = verificarDados($dados);

    if (json_decode($resposta)->status === "Sucesso") {
        $dados = json_decode($resposta, true);
        $resultado = insert("estoque", $dados["dados"]);
        echo '{"status": "Sucesso", "mensagem": "Estoque cadastrado com sucesso!", "dados": '. json_encode($resultado) . ' }';
    } else {
        echo $resposta;
    }
}
else if ($metodo == "GET") {
    $dados = findAll( "estoque" ); 
    echo '{"dados": '. json_encode($dados) . ' }';
}
else if ($metodo == "DELETE") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    $id = $dados["id"] ?? null;
    if ($id) {
        $dados = delete("estoque", $id);
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
            $resultado = update("estoque", $id, $dadosAtualizacao);

            echo json_encode([
                "status" => "Sucesso",
                "mensagem" => "Estoque atualizado com sucesso!",
                "dados" => $resultado
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo $resposta;
        }
    }
}

function verificarDados($dados) {
    $status="Sucesso"; $msg="Estoque cadastrado com sucesso!";
    foreach ($dados as $campo=>$valor) {
        if (empty($valor)) { $status="Erro"; $msg="O campo '$campo' e obrigatorio."; return json_encode(["status"=>$status,"mensagem"=>$msg,"dados"=>$dados], JSON_UNESCAPED_UNICODE); }
    }
    if (!ctype_digit((string)$dados["qtde"])) { $status="Erro"; $msg="A quantidade atual deve conter apenas numeros inteiros."; }
    if (!ctype_digit((string)$dados["qtde_minima"])) { $status="Erro"; $msg="A quantidade minima deve conter apenas numeros inteiros."; }
    if (!is_numeric($dados["custo_unitario"])) { $status="Erro"; $msg="O custo unitario deve ser um numero."; }
    elseif ((float)$dados["custo_unitario"]<0) { $status="Erro"; $msg="O custo unitario nao pode ser negativo."; }
    $isbn=preg_replace('/\D/','',$dados["isbn"]);
    if (strlen($isbn)!=10 && strlen($isbn)!=13) { $status="Erro"; $msg="O ISBN deve conter 10 ou 13 numeros."; } else { $dados["isbn"]=$isbn; }
    return json_encode(["status"=>$status,"mensagem"=>$msg,"dados"=>$dados], JSON_UNESCAPED_UNICODE);
}

?>
