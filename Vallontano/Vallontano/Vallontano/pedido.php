
<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");
$endereco = trim($_POST["endereco"] ?? "");
$pagamento = $_POST["pagamento"] ?? "";
$carrinho = json_decode($_POST["carrinho"] ?? "", true);

if (!$nome) {
    exit("Erro: nome não recebido.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Erro: e-mail inválido.");
}

if (!$endereco) {
    exit("Erro: endereço não recebido.");
}

if (!in_array($pagamento, ["Pix simulado", "Cartão simulado"], true)) {
    exit("Erro: forma de pagamento inválida.");
}

if (!is_array($carrinho) || !$carrinho) {
    exit("Erro: produtos do carrinho não foram recebidos.");
}

$conexao->begin_transaction();

try {
    $itens = [];
    $total = 0;

    $consulta = $conexao->prepare(
        "SELECT id, preco FROM produtos WHERE id = ?"
    );

    foreach ($carrinho as $item) {
        $id = (int) ($item["id"] ?? 0);
        $quantidade = (int) ($item["quantidade"] ?? 0);

        if ($id < 1 || $quantidade < 1) {
            throw new Exception("Item inválido.");
        }

        $consulta->bind_param("i", $id);
        $consulta->execute();
        $produto = $consulta->get_result()->fetch_assoc();

        if (!$produto) {
            throw new Exception("Produto não encontrado.");
        }

        $preco = (float) $produto["preco"];
        $total += $preco * $quantidade;
        $itens[] = [$id, $quantidade, $preco];
    }

    $pedido = $conexao->prepare(
        "INSERT INTO pedidos (nome, email, endereco, pagamento, total)
         VALUES (?, ?, ?, ?, ?)"
    );
    $pedido->bind_param("ssssd", $nome, $email, $endereco, $pagamento, $total);
    $pedido->execute();

    $pedido_id = $conexao->insert_id;
    $inserir = $conexao->prepare(
        "INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco)
         VALUES (?, ?, ?, ?)"
    );

    foreach ($itens as [$id, $quantidade, $preco]) {
        $inserir->bind_param("iiid", $pedido_id, $id, $quantidade, $preco);
        $inserir->execute();
    }

    $conexao->commit();
} catch (Throwable $e) {
    $conexao->rollback();
    error_log($e->getMessage());
    http_response_code(500);
    exit("Não foi possível registrar o pedido. Tente novamente.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedido registrado | Vallontano</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main class="checkout">
        <h1>Pedido registrado!</h1>
        <p>Seu pedido foi registrado com sucesso.</p>
        <p>Pagamento: <?= htmlspecialchars($pagamento, ENT_QUOTES, "UTF-8") ?></p>
        <p>Total: <?= number_format($total, 2, ",", ".") ?></p>
        <p>Esta é uma simulação. Nenhuma cobrança foi realizada.</p>
        <a href="index.php">Voltar à loja</a>
    </main>
</body>
</html>
