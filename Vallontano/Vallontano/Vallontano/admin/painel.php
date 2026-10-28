
<?php
require __DIR__ . "/proteger.php";
require __DIR__ . "/../config.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$resultado = $conexao->query(
    "SELECT id, nome, preco FROM produtos ORDER BY id DESC"
);

$resultadoPedidos = $conexao->query(
    "SELECT id, nome, email, total, pagamento, status, criado_em
     FROM pedidos
     ORDER BY id DESC"
);


$itensPorPedido = [];

$resultadoItens = $conexao->query(
    "SELECT
        ip.pedido_id,
        ip.produto_id,
        ip.quantidade,
        ip.preco,
        p.nome AS nome_produto
     FROM itens_pedido ip
     LEFT JOIN produtos p ON p.id = ip.produto_id
     ORDER BY ip.pedido_id DESC"
);

while ($item = $resultadoItens->fetch_assoc()) {
    $pedidoId = (int) $item["pedido_id"];
    $itensPorPedido[$pedidoId][] = $item;
}


$mensagemPedido = $_SESSION["mensagem_admin"] ?? "";
unset($_SESSION["mensagem_admin"]);

function h($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Painel administrativo | Vallontano Vinhos Nobres</title>

    <style>
        :root {
            --vinho: #511d2b;
            --vinho-escuro: #35141d;
            --dourado: #b49a63;
            --creme: #f8f5f0;
            --texto: #332a28;
            --suave: #786b68;
            --branco: #ffffff;
            --borda: #e9e0d7;
            --vermelho: #a52828;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--creme);
            color: var(--texto);
            line-height: 1.6;
        }

        a {
            color: inherit;
        }

        .cabecalho {
            background: var(--vinho-escuro);
            color: var(--branco);
            border-bottom: 3px solid var(--dourado);
            padding: 28px 20px;
        }

        .cabecalho-conteudo,
        .conteudo {
            width: min(1120px, 100%);
            margin: 0 auto;
        }

        .marca {
            color: #e6d1a5;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 3px;
            margin: 0 0 8px;
        }

        .cabecalho h1 {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(26px, 4vw, 36px);
            font-weight: normal;
        }

        .subtitulo {
            color: #e8dadd;
            margin: 8px 0 0;
        }

        .conteudo {
            padding: 24px 20px 48px;
        }

        .navegacao {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 24px;
        }

        .botao {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 7px;
            padding: 11px 16px;
            border: 1px solid var(--dourado);
            border-radius: 6px;
            background: var(--vinho);
            color: var(--branco);
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

        .botao:hover {
            background: var(--vinho-escuro);
        }

        .botao-secundario {
            background: var(--branco);
            color: var(--vinho);
            border-color: var(--borda);
        }

        .botao-secundario:hover {
            background: #f1e9e1;
        }

        .boas-vindas {
            margin-bottom: 22px;
        }

        .boas-vindas h2,
        .secao-titulo {
            color: var(--vinho-escuro);
            font-family: Georgia, "Times New Roman", serif;
            font-weight: normal;
        }

        .boas-vindas h2 {
            margin-bottom: 4px;
        }

        .boas-vindas p {
            margin-top: 4px;
            color: var(--suave);
        }

        .resumos {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin: 24px 0 34px;
        }

        .resumo {
            background: var(--branco);
            border: 1px solid var(--borda);
            border-top: 3px solid var(--dourado);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 3px 12px rgba(53, 20, 29, 0.04);
        }

        .resumo p {
            color: var(--suave);
            margin: 0;
            font-size: 14px;
        }

        .resumo strong {
            display: block;
            margin-top: 4px;
            color: var(--vinho);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 32px;
            line-height: 1.3;
        }

        .secao {
            margin-top: 34px;
            scroll-margin-top: 20px;
        }

        .secao-titulo {
            font-size: 27px;
            margin: 0 0 6px;
        }

        .descricao-secao {
            margin: 0 0 18px;
            color: var(--suave);
            font-size: 14px;
        }

        .lista-produtos {
            padding: 0;
            margin: 0;
            list-style: none;
            background: var(--branco);
            border: 1px solid var(--borda);
            border-radius: 8px;
            overflow: hidden;
        }

        .lista-produtos li {
            padding: 13px 17px;
            border-bottom: 1px solid var(--borda);
        }

        .lista-produtos li:last-child {
            border-bottom: 0;
        }

        .mensagem {
            margin: 18px 0;
            padding: 14px 16px;
            background: #edf5ec;
            border: 1px solid #c9dfc5;
            border-radius: 6px;
            color: #28562c;
        }

        .vazio {
            background: var(--branco);
            border: 1px dashed #d6c8bd;
            border-radius: 8px;
            padding: 24px;
            color: var(--suave);
            text-align: center;
        }

        .pedidos-lista {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            align-items: start;
        }

        .pedido {
            min-width: 0;
            background: var(--branco);
            border: 1px solid var(--borda);
            border-radius: 9px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(53, 20, 29, 0.04);
        }

        .pedido-cabecalho {
            padding: 17px 19px;
            background: #fcfaf7;
            border-bottom: 1px solid var(--borda);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .pedido-cabecalho h3 {
            color: var(--vinho);
            margin: 0;
            font-size: 18px;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            background: #f2eadb;
            color: #73551d;
            font-size: 12px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .pedido-corpo {
            padding: 18px 19px;
        }

        .pedido-dado {
            margin: 0 0 13px;
            overflow-wrap: anywhere;
        }

        .pedido-dado strong {
            display: block;
            color: var(--suave);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .pedido-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            border-top: 1px solid var(--borda);
            padding-top: 15px;
            margin-top: 6px;
        }

        .pedido-total span {
            color: var(--suave);
            font-size: 14px;
        }

        .pedido-total strong {
            color: var(--vinho);
            font-size: 22px;
        }

        .pedido-acoes {
            margin-top: 18px;
            padding-top: 15px;
            border-top: 1px solid var(--borda);
        }

        .botao-excluir {
            width: 100%;
            padding: 10px 14px;
            background: var(--branco);
            color: var(--vermelho);
            border: 1px solid #d9aaaa;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }

        .botao-excluir:hover {
            background: #fff1f1;
        }

        .rodape {
            margin-top: 42px;
            padding-top: 20px;
            border-top: 1px solid var(--borda);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 14px;
            color: var(--suave);
            font-size: 14px;
        }

        .rodape a {
            color: var(--vinho);
            font-weight: bold;
        }

        a:focus-visible,
        button:focus-visible {
            outline: 3px solid var(--dourado);
            outline-offset: 3px;
        }

        @media (max-width: 700px) {
            .conteudo {
                padding: 20px 14px 36px;
            }

            .resumos,
            .pedidos-lista {
                grid-template-columns: 1fr;
            }

            .cabecalho {
                padding: 24px 16px;
            }

            .navegacao {
                display: grid;
                grid-template-columns: 1fr;
            }

            .botao {
                width: 100%;
            }

            .pedido-cabecalho {
                align-items: flex-start;
            }

            .rodape {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
<header class="cabecalho">
    <div class="cabecalho-conteudo">
        <p class="marca">VALLONTANO VINHOS NOBRES</p>
        <h1>Painel administrativo</h1>
        <p class="subtitulo">
            Gerencie seus produtos e acompanhe os pedidos da loja.
        </p>
    </div>
</header>

<main class="conteudo">
    <nav class="navegacao" aria-label="Navegação administrativa">
        <a class="botao" href="produtos.php">
            🍷 Gerenciar produtos
        </a>
        <a class="botao botao-secundario" href="#pedidos">
            📦 Ver pedidos recebidos
        </a>
        <a class="botao botao-secundario" href="../index.php">
            🏠 Ver loja
        </a>
    </nav>

    <section class="boas-vindas">
        <h2>Olá, <?= h($_SESSION["admin_usuario"] ?? "Administrador") ?>!</h2>
        <p>Confira abaixo o resumo da sua loja e os pedidos recebidos.</p>
    </section>

    <section class="resumos" aria-label="Resumo da loja">
        <div class="resumo">
            <p>Produtos cadastrados</p>
            <strong><?= (int) $resultado->num_rows ?></strong>
        </div>
        <div class="resumo">
            <p>Pedidos recebidos</p>
            <strong><?= (int) $resultadoPedidos->num_rows ?></strong>
        </div>
    </section>

    <section class="secao" id="produtos">
        <h2 class="secao-titulo">Produtos atuais</h2>
        <p class="descricao-secao">
            Resumo dos produtos cadastrados na loja.
        </p>

        <?php if ($resultado->num_rows === 0): ?>
            <div class="vazio">Nenhum produto cadastrado.</div>
        <?php else: ?>
            <ul class="lista-produtos">
                <?php while ($produto = $resultado->fetch_assoc()): ?>
                    <li>
                        <strong><?= h($produto["nome"]) ?></strong>
                        <span> — R$ <?= number_format((float) $produto["preco"], 2, ",", ".") ?></span>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>

        <p>
            <a class="botao botao-secundario" href="produtos.php">
                Acessar gerenciamento de produtos
            </a>
        </p>
    </section>

    <section class="secao" id="pedidos">
        <h2 class="secao-titulo">Pedidos recebidos</h2>
        <p class="descricao-secao">
            Consulte os dados de cada pedido e o valor da compra.
        </p>

        <?php if ($mensagemPedido !== ""): ?>
            <div class="mensagem" role="status">
                <?= h($mensagemPedido) ?>
            </div>
        <?php endif; ?>

        <?php if ($resultadoPedidos->num_rows === 0): ?>
            <div class="vazio">
                Nenhum pedido registrado até o momento.
            </div>
        <?php else: ?>
            <div class="pedidos-lista">
                <?php while ($pedido = $resultadoPedidos->fetch_assoc()): ?>
                    <article class="pedido">
                        <header class="pedido-cabecalho">
                            <h3>Pedido nº <?= (int) $pedido["id"] ?></h3>
                            <span class="status"><?= h($pedido["status"]) ?></span>
                        </header>

                        <div class="pedido-corpo">
                            <p class="pedido-dado">
                                <strong>Cliente</strong>
                                <?= h($pedido["nome"]) ?>
                            </p>

                            <p class="pedido-dado">
                                <strong>E-mail</strong>
                                <?= h($pedido["email"]) ?>
                            </p>

                            <p class="pedido-dado">
                                <strong>Pagamento</strong>
                                <?= h($pedido["pagamento"]) ?>
                            </p>

                            <p class="pedido-dado">
                                <strong>Data do pedido</strong>
                                <?php
                                $dataPedido = strtotime($pedido["criado_em"]);
                                echo $dataPedido !== false
                                    ? date("d/m/Y H:i", $dataPedido)
                                    : "Data indisponível";
                                ?>
                            </p>
                            
<div class="itens-comprados">
    <h4>Vinhos do pedido</h4>

    <?php $pedidoId = (int) $pedido["id"]; ?>

    <?php if (!empty($itensPorPedido[$pedidoId])): ?>
        <ul>
            <?php foreach ($itensPorPedido[$pedidoId] as $item): ?>
                <li>
                    <span>
                        <?= h($item["nome_produto"] ?? "") !== ""
                            ? h($item["nome_produto"])
                            : "Produto indisponível" ?>
                        — <?= (int) $item["quantidade"] ?> unidade(s)
                    </span>

                    <strong>
                        R$ <?= number_format(
                            (float) $item["preco"] * (int) $item["quantidade"],
                            2,
                            ",",
                            "."
                        ) ?>
                    </strong>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Não há itens registrados para este pedido.</p>
    <?php endif; ?>
</div>


                            <div class="pedido-total">
                                <span>Total do pedido</span>
                                <strong>
                                    R$ <?= number_format((float) $pedido["total"], 2, ",", ".") ?>
                                </strong>
                            </div>

                            <div class="pedido-acoes">
                                <form
                                    action="excluir.php"
                                    method="POST"
                                    onsubmit="return confirm('Deseja realmente excluir o pedido nº <?= (int) $pedido["id"] ?>? Essa ação não pode ser desfeita.');"
                                >
                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= h($_SESSION["csrf_token"]) ?>"
                                    >
                                    <input
                                        type="hidden"
                                        name="tipo"
                                        value="pedido"
                                    >
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) $pedido["id"] ?>"
                                    >
                                    <button class="botao-excluir" type="submit">
                                        Excluir pedido
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </section>

    <footer class="rodape">
        <span>Vallontano Vinhos Nobres · Administração</span>
        <a href="sair.php">Sair do painel</a>
    </footer>
</main>
</body>
</html>
