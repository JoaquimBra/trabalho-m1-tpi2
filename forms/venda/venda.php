<?php


require "./../../db/dbconnect.php"; 

header("Content-Type: application/json; charset=UTF-8");

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo == "POST") {
    $dados = [
        "data_venda" => trim($_POST["dataVenda"] ?? ""),
        "pagamento" => trim($_POST["formaPagamento"] ?? ""),
        "desconto" => trim($_POST["descontoAplicado"] ?? ""),
        "cupom_fiscal" => trim($_POST["cupomFiscal"] ?? "")
    ];
    $resposta = verificarDados($dados);

    if (json_decode($resposta)->status === "Sucesso") {
        $dados = json_decode($resposta, true);
        $resultado = insert("venda", $dados["dados"]);
        echo '{"status": "Sucesso", "mensagem": "Venda cadastrado com sucesso!", "dados": '. json_encode($resultado) . ' }';
    } else {
        echo $resposta;
    }
}
else if ($metodo == "GET") {
    $dados = findAll( "venda" ); 
    echo '{"dados": '. json_encode($dados) . ' }';
}
else if ($metodo == "DELETE") {
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    $id = $dados["id"] ?? null;
    if ($id) {
        $dados = delete("venda", $id);
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
            $resultado = update("venda", $id, $dadosAtualizacao);

            echo json_encode([
                "status" => "Sucesso",
                "mensagem" => "Venda atualizada com sucesso!",
                "dados" => $resultado
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo $resposta;
        }
    }
}

function verificarDados($dados) {
    $status="Sucesso"; $msg="Venda cadastrada com sucesso!";
    foreach ($dados as $campo=>$valor) {
        if (empty($valor)) { $status="Erro"; $msg="O campo '$campo' e obrigatorio."; return json_encode(["status"=>$status,"mensagem"=>$msg,"dados"=>$dados], JSON_UNESCAPED_UNICODE); }
    }
    if (!in_array($dados["pagamento"],["pix","cartao","dinheiro"],true)) { $status="Erro"; $msg="A forma de pagamento selecionada e invalida."; }
    if (!is_numeric($dados["desconto"])) { $status="Erro"; $msg="O desconto deve ser um numero."; }
    elseif ((float)$dados["desconto"]<0 || (float)$dados["desconto"]>100) { $status="Erro"; $msg="O desconto deve estar entre 0% e 100%."; }
    return json_encode(["status"=>$status,"mensagem"=>$msg,"dados"=>$dados], JSON_UNESCAPED_UNICODE);
}

?>
