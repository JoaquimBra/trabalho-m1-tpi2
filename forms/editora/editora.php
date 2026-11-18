<?php

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

    if (json_decode($resposta)->status === "Sucesso") {
        $dados = json_decode($resposta, true);
        $resultado = insert("editora", $dados["dados"]);
        echo '{"status": "Sucesso", "mensagem": "Editora cadastrado com sucesso!", "dados": '. json_encode($resultado) . ' }';
    } else {
        echo $resposta;
    }
}
else if ($metodo == "GET") {
    $dados = findAll( "editora" ); 
    echo '{"dados": '. json_encode($dados) . ' }';
}
else if ($metodo == "DELETE") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    $id = $dados["id"] ?? null;
    if ($id) {
        $dados = delete("editora", $id);
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
            $resultado = update("editora", $id, $dadosAtualizacao);

            echo json_encode([
                "status" => "Sucesso",
                "mensagem" => "Editora atualizada com sucesso!",
                "dados" => $resultado
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo $resposta;
        }
    }
}

function verificarDados($dados) {
    $status = "Sucesso"; $msg = "Editora cadastrada com sucesso!";
    foreach ($dados as $campo => $valor) {
        if (empty($valor)) { $status="Erro"; $msg="O campo '$campo' e obrigatorio."; return json_encode(["status"=>$status,"mensagem"=>$msg,"dados"=>$dados], JSON_UNESCAPED_UNICODE); }
    }
    $cnpj=preg_replace('/\D/','',$dados["cnpj"]);
    if (strlen($cnpj)!=14) { $status="Erro"; $msg="O CNPJ deve conter exatamente 14 numeros."; } else { $dados["cnpj"]=$cnpj; }
    $telefone=preg_replace('/\D/','',$dados["telefone"]);
    if (strlen($telefone)<10 || strlen($telefone)>11) { $status="Erro"; $msg="O telefone deve possuir 10 ou 11 digitos."; }
    if (!is_numeric($dados["valor_minimo"])) { $status="Erro"; $msg="O valor minimo do pedido deve ser um numero."; }
    elseif ((float)$dados["valor_minimo"]<0) { $status="Erro"; $msg="O valor minimo do pedido nao pode ser menor que 0."; }
    return json_encode(["status"=>$status,"mensagem"=>$msg,"dados"=>$dados], JSON_UNESCAPED_UNICODE);
}

?>
