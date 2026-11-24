<?php

require "./../../db/dbconnect.php";

session_start();
header("Content-Type: application/json; charset=UTF-8");

$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo == "GET") {
    if (isset($_SESSION["usuario_id"])) {
        echo json_encode([
            "autenticado" => true,
            "nome" => $_SESSION["usuario_nome"],
            "email" => $_SESSION["usuario_email"]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo '{"autenticado": false}';
    }
}
else if ($metodo == "POST") {
    $acao = $_POST["acao"] ?? "";

    if ($acao == "cadastro") {
        $nome = trim($_POST["nome"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $senha = $_POST["senha"] ?? "";
        $confirmarSenha = $_POST["confirmarSenha"] ?? "";

        if ($nome == "" || $email == "" || $senha == "" || $confirmarSenha == "") {
            echo '{"status": "Erro", "mensagem": "Preencha todos os campos."}';
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo '{"status": "Erro", "mensagem": "E-mail inválido."}';
            exit;
        }
        if (strlen($senha) < 6) {
            echo '{"status": "Erro", "mensagem": "A senha deve ter no mínimo 6 caracteres."}';
            exit;
        }
        if ($senha != $confirmarSenha) {
            echo '{"status": "Erro", "mensagem": "As senhas não conferem."}';
            exit;
        }

        if (buscarUsuarioPorEmail($email)) {
            echo '{"status": "Erro", "mensagem": "Este e-mail já está cadastrado."}';
            exit;
        }

        $resultado = insert("usuario", [
            "nome" => $nome,
            "email" => $email,
            "senha" => password_hash($senha, PASSWORD_DEFAULT)
        ]);

        if ($resultado) {
            echo '{"status": "Sucesso", "mensagem": "Cadastro realizado com sucesso!"}';
        } else {
            echo '{"status": "Erro", "mensagem": "Erro ao cadastrar usuário."}';
        }
    }
    else if ($acao == "login") {
        $email = trim($_POST["email"] ?? "");
        $senha = $_POST["senha"] ?? "";

        if ($email == "" || $senha == "") {
            echo '{"status": "Erro", "mensagem": "Informe e-mail e senha."}';
            exit;
        }

        $usuario = buscarUsuarioPorEmail($email);

        if ($usuario && password_verify($senha, $usuario["senha"])) {
            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["usuario_nome"] = $usuario["nome"];
            $_SESSION["usuario_email"] = $usuario["email"];

            echo '{"status": "Sucesso", "mensagem": "Login realizado com sucesso!"}';
        } else {
            http_response_code(401);
            echo '{"status": "Erro", "mensagem": "E-mail ou senha inválidos."}';
        }
    }
    else if ($acao == "logout") {
        session_destroy();
        echo '{"status": "Sucesso", "mensagem": "Logout realizado!"}';
    }
}

function buscarUsuarioPorEmail($email) {
    $conn = getConnection();
    $query = $conn->prepare("SELECT * FROM usuario WHERE email = :email");
    $query->bindValue(":email", $email);
    $query->execute();
    return $query->fetch(PDO::FETCH_ASSOC);
}

?>
