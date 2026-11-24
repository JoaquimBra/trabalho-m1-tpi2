<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {
    http_response_code(401);
    header("Content-Type: application/json; charset=UTF-8");
    echo '{"status": "Erro", "mensagem": "Faça login para continuar."}';
    exit;
}

?>
