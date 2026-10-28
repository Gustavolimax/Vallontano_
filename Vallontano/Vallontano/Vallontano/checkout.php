
<?php
require_once "config.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar compra | Vallontano</title>
    <link rel="stylesheet" href="css/style.css">
</head>


<body>
    <main class="checkout">
        <h1>Finalizar compra</h1>
        <form action="pedido.php" method="post" id="form-pedido">
        <input type="hidden" name="carrinho" id="dados-carrinho">
            <label>Nome completo</label>
            <input type="text" name="nome" required>

            <label>E-mail</label>
            <input type="email" name="email" required>

            <label>Endereço de entrega</label>
            <input type="text" name="endereco" required>

            <h2>Pagamento simulado</h2>
            <p>Escolha uma forma de pagamento para testar sua compra.</p>

            <label>Forma de pagamento</label>
            <select name="pagamento" required>
                <option value="">Selecione</option>
                <option value="Pix simulado">Pix</option>
                <option value="Cartão simulado">Cartão de crédito</option>
            </select>

            <button type="submit">Confirmar pedido</button>
            
<a href="index.php" class="botao-voltar">
    ← Voltar à loja
</a>

        </form>
    </main>

<script>
const carrinhoSalvo = sessionStorage.getItem("carrinho");
const campoCarrinho = document.querySelector("#dados-carrinho");

if (carrinhoSalvo) {
    campoCarrinho.value = carrinhoSalvo;
}
</script>
</body>
</html>
