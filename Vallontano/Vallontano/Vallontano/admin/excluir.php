```php
<?php
require __DIR__ . "/proteger.php";
require __DIR__ . "/../config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: painel.php");
    exit;
}

$token = $_POST["csrf_token"] ?? "";

if (
    !is_string($token) ||
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(403);
    exit("Solicitação inválida. Atualize a página e tente novamente.");
}

$tipo = $_POST["tipo"] ?? "produto";
$id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id < 1) {
    $_SESSION["mensagem_admin"] = "Registro inválido.";
    header("Location: " . ($tipo === "pedido" ? "painel.php#pedidos" : "produtos.php"));
    exit;
}

/* EXCLUIR PEDIDO */
if ($tipo === "pedido") {
    try {
        $conexao->begin_transaction();

        $stmt = $conexao->prepare(
            "DELETE FROM itens_pedido WHERE pedido_id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conexao->prepare(
            "DELETE FROM pedidos WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $pedidoExcluido = $stmt->affected_rows > 0;
        $stmt->close();

        if (!$pedidoExcluido) {
            $conexao->rollback();
            $_SESSION["mensagem_admin"] = "Pedido não encontrado.";
        } else {
            $conexao->commit();
            $_SESSION["mensagem_admin"] = "Pedido excluído com sucesso.";
        }
    } catch (Throwable $e) {
        $conexao->rollback();
        error_log($e->getMessage());
        $_SESSION["mensagem_admin"] = "Não foi possível excluir o pedido.";
    }

    header("Location: painel.php#pedidos");
    exit;
}

/* EXCLUIR PRODUTO */
if ($tipo !== "produto") {
    http_response_code(400);
    exit("Tipo de registro inválido.");
}

$stmt = $conexao->prepare(
    "SELECT imagem FROM produtos WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$produto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$produto) {
    $_SESSION["mensagem_admin"] = "Produto não encontrado.";
    header("Location: produtos.php");
    exit;
}

$stmt = $conexao->prepare(
    "DELETE FROM produtos WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

if (!empty($produto["imagem"])) {
    $arquivo = __DIR__ . "/../uploads/" . basename($produto["imagem"]);

    if (is_file($arquivo)) {
        @unlink($arquivo);
    }
}

$_SESSION["mensagem_admin"] = "Produto excluído com sucesso.";
header("Location: produtos.php");
exit;
