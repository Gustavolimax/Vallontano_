
<?php
session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Strict"
]);
session_start();

require __DIR__ . "/../config.php";

if (isset($_SESSION["admin_id"])) {
    header("Location: painel.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"] ?? "");
    $senha = $_POST["senha"] ?? "";

    $stmt = $conexao->prepare(
        "SELECT id, usuario, senha
         FROM administradores
         WHERE usuario = ?
         LIMIT 1"
    );
    $stmt->bind_param("s", $usuario);
    $stmt->execute();

    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($admin && password_verify($senha, $admin["senha"])) {
        session_regenerate_id(true);
        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_usuario"] = $admin["usuario"];

        header("Location: painel.php");
        exit;
    }

    $erro = "Usuário ou senha incorretos.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#35141d">
    <title>Acesso administrativo | Vallontano Vinhos Nobres</title>

    <style>
        :root {
            --vinho: #650B16;
            --vinho-escuro: #650B16;
            --dourado: #b49a63;
            --creme: #f8f5f0;
            --texto: #332a28;
            --suave: #786b68;
            --borda: #e6ddd4;
            --branco: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--texto);
            background:
                radial-gradient(
                    circle at top left,
                    #74404a 0,
                    transparent 38%
                ),
                var(--vinho-escuro);
        }

        .pagina-login {
            width: 100%;
            max-width: 430px;
        }

        .marca {
            margin-bottom: 24px;
            text-align: center;
            color: var(--branco);
        }

        .simbolo {
            width: 58px;
            height: 58px;
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--dourado);
            border-radius: 50%;
            color: #e6d1a5;
            font-size: 28px;
        }

        .marca h1 {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 27px;
            font-weight: normal;
            letter-spacing: 3px;
        }

        .marca p {
            margin: 9px 0 0;
            color: #d6bc8c;
            font-size: 11px;
            letter-spacing: 3px;
        }

        .cartao-login {
            padding: 34px;
            background: var(--creme);
            border: 1px solid rgba(180, 154, 99, 0.65);
            border-radius: 10px;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.25);
        }

        .cartao-login h2 {
            margin: 0;
            color: var(--vinho-escuro);
            text-align: center;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 27px;
            font-weight: normal;
        }

        .introducao {
            margin: 10px 0 26px;
            color: var(--suave);
            text-align: center;
            font-size: 14px;
            line-height: 1.6;
        }

        .campo {
            margin-bottom: 19px;
        }

        .campo label {
            display: block;
            margin-bottom: 8px;
            color: var(--vinho-escuro);
            font-size: 14px;
            font-weight: bold;
        }

        .campo input {
            width: 100%;
            min-height: 46px;
            padding: 12px 13px;
            border: 1px solid var(--borda);
            border-radius: 15px;
            background: var(--branco);
            color: var(--texto);
            font: inherit;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .campo input:focus {
            outline: none;
            border-color: var(--dourado);
            box-shadow: 0 0 0 3px rgba(180, 154, 99, 0.18);
        }

        .mensagem-erro {
            margin-bottom: 20px;
            padding: 12px 14px;
            border: 15px solid #e3bcbc;
            border-radius: 5px;
            background: #fff0f0;
            color: #650B16;
            font-size: 14px;
            line-height: 1.5;
        }

        .botao-entrar {
            width: 100%;
            min-height: 48px;
            margin-top: 5px;
            padding: 13px 16px;
            border: 1px solid var(--dourado);
            border-radius: 15px;
            background: var(--vinho);
            color: var(--branco);
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }

        .botao-entrar:hover {
            background: var(--vinho-escuro);
        }

        .botao-entrar:active {
            transform: translateY(1px);
        }

        .rodape {
            margin-top: 22px;
            color: #d8c9c5;
            text-align: center;
            font-size: 12px;
            line-height: 1.7;
        }

        .rodape a {
            color: #e6d1a5;
            text-decoration: none;
        }

        .rodape a:hover {
            text-decoration: underline;
        }

        input:focus-visible,
        button:focus-visible,
        a:focus-visible {
            outline: 3px solid var(--dourado);
            outline-offset: 3px;
        }

        @media (max-width: 480px) {
            body {
                padding: 18px;
            }

            .cartao-login {
                padding: 26px 21px;
            }

            .marca h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <main class="pagina-login">
        <header class="marca">
            <div class="simbolo" aria-hidden="true">🍷</div>
            <h1>VALLONTANO</h1>
            <p>VINHOS NOBRES</p>
        </header>

        <section class="cartao-login">
            <h2>Acesso administrativo</h2>

            <p class="introducao">
                Entre com seus dados para acessar o painel de gerenciamento.
            </p>

            <?php if ($erro !== ""): ?>
                <div class="mensagem-erro" role="alert">
                    <?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="campo">
                    <label for="usuario">Usuário</label>
                    <input
                        id="usuario"
                        type="text"
                        name="usuario"
                        required
                        autocomplete="username"
                        autofocus
                    >
                </div>

                <div class="campo">
                    <label for="senha">Senha</label>
                    <input
                        id="senha"
                        type="password"
                        name="senha"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button class="botao-entrar" type="submit">
                    Entrar no painel
                </button>
            </form>
        </section>

        <footer class="rodape">
            Acesso restrito à administração.<br>
            <a href="../index.php">Voltar para a loja</a>
        </footer>
    </main>
</body>
</html>
