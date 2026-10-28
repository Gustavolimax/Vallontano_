
<?php
require __DIR__ . "/config.php";

$busca = trim($_GET["busca"] ?? "");

if ($busca !== "") {
    $termo = "%" . $busca . "%";

    $stmt = $conexao->prepare(
        "SELECT id, nome, descricao, preco, imagem, categoria
         FROM produtos
         WHERE nome LIKE ? OR descricao LIKE ?
         ORDER BY id DESC"
    );

    $stmt->bind_param("ss", $termo, $termo);
    $stmt->execute();
    $produtos = $stmt->get_result();

} else {
    $produtos = $conexao->query(
        "SELECT id, nome, descricao, preco, imagem, categoria
         FROM produtos
         ORDER BY id DESC"
    );
}


$consultaAvaliacoes = $conexao->query(
    "SELECT id, nome, nota, comentario, criado_em
     FROM avaliacoes
     ORDER BY id DESC
     LIMIT 20"
);

$avaliacoes = $consultaAvaliacoes->fetch_all(MYSQLI_ASSOC);


function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vallontano | Vinhos Nobres</title>
    <link rel="stylesheet" href="css/style.css">
</head>


<button id="carrinho" class="carrinho-flutuante" type="button"
    aria-label="Abrir carrinho">
    🛒 <span id="contador">0</span>
</button>

<div id="painel-carrinho" class="painel-carrinho" hidden>
    <button id="fechar-carrinho" type="button">Fechar ×</button>
    <h2>Seu carrinho</h2>
    <div id="itens-carrinho">Seu carrinho está vazio.</div>
    <p>Subtotal: <strong id="subtotal">R$ 0,00</strong></p>

    <button type="button" id="finalizar-pedido">
    Finalizar pedido
    </button>
</div>

<script src="js/carrinho.js?v=2" defer></script>

<script>
const carrinho = sessionStorage.getItem("carrinho");

if (carrinho) {
    document.querySelector("#dados-carrinho").value = carrinho;
}
</script>


<body>

<nav class="menu-superior">
    <a href="#inicio" class="menu-logo">VALLONTANO</a>

    <div class="menu-links">
        <a href="#inicio">Início</a>
        <a href="#sobre">Sobre nós</a>
        <a href="#catalogo">Nossos vinhos</a>
        <a href="admin/login.php" class="menu-admin">
            ⚙ Administração
        </a>
    </div>
</nav>

<section class="hero" id="inicio">

    <img
        src="imagens/logo.jpg"
        alt="Vallontano Vinhos Nobres"
        class="imagem-hero"
    >
</section>

<!-- SOBRE A VINÍCOLA -->

<section class="sobre" id="sobre">
    <div class="sobre-texto">
        <span class="subtitulo">NOSSA ESSÊNCIA</span>

        <h2>Sobre nossa vinícola</h2>

        <p>
            A Vallontano Vinhos Nobres acredita que cada garrafa
            carrega uma história, uma tradição e momentos especiais.
        </p>

        <p>
            Selecionamos nossos rótulos com cuidado, valorizando
            a qualidade, a elegância e a experiência de apreciar
            um bom vinho.
        </p>

        <a href="#catalogo" class="botao-sobre">
            Conheça nossos rótulos
        </a>
    </div>
    

</section>


<section class="beneficios">
    <div class="beneficios-titulo">
        <span class="subtitulo">A EXPERIÊNCIA VALLONTANO</span>
        <h2>Por que escolher nossos vinhos?</h2>
        <p>Qualidade e elegância em cada detalhe.</p>
    </div>

    <div class="beneficios-grade">
        <article class="beneficio-card">
            <span class="beneficio-icone">♜</span>
            <h3>Seleção Especial</h3>
            <p>Rótulos escolhidos com cuidado para valorizar cada ocasião.</p>
        </article>

        <article class="beneficio-card">
            <span class="beneficio-icone">✧</span>
            <h3>Qualidade</h3>
            <p>Uma experiência que valoriza aromas, sabores e personalidade.</p>
        </article>

        <article class="beneficio-card">
            <span class="beneficio-icone">♡</span>
            <h3>Momentos Especiais</h3>
            <p>Vinhos para acompanhar celebrações e boas lembranças.</p>
        </article>

        <article class="beneficio-card">
            <span class="beneficio-icone">❖</span>
            <h3>Elegância</h3>
            <p>Uma seleção que une sofisticação e prazer à mesa.</p>
        </article>
    </div>
</section>

<section id="catalogo">
    <div class="catalogo">
        <h2>Nossa Carta de Vinhos</h2>

        <form class="pesquisa" method="GET">
            <input type="search" name="busca"
                   placeholder="Pesquisar vinhos..."
                   value="<?= escapar($busca) ?>">
            <button type="submit">Pesquisar</button>
        </form>

        
<?php
$listaProdutos = $produtos->fetch_all(MYSQLI_ASSOC);

$categorias = [
    "Tinto" => "Vinhos Tintos",
    "Branco" => "Vinhos Brancos",
    "Rose" => "Vinhos Roses"
];

if (count($listaProdutos) === 0):
?>
    <p>Nenhum vinho encontrado.</p>
<?php
else:
    foreach ($categorias as $categoria => $titulo):
        $produtosCategoria = array_filter(
            $listaProdutos,
            function ($produto) use ($categoria) {
                return trim($produto["categoria"] ?? "") === $categoria;
            }
        );

        if (count($produtosCategoria) > 0):
?>
            <section class="categoria-vinhos">
                <h3 class="titulo-categoria">
                    <?= escapar($titulo) ?>
                </h3>

                <div class="grade">
                   
<?php foreach ($produtosCategoria as $produto): ?>
    <article class="card produto">

        <?php if (!empty($produto["imagem"])): ?>
            <img
                src="uploads/<?= rawurlencode(basename($produto["imagem"])) ?>"
                alt="<?= escapar($produto["nome"]) ?>"
                loading="lazy"
            >
        <?php else: ?>
            <div class="produto-sem-imagem">
                <span>Vallontano</span>
                <small>Vinhos Nobres</small>
            </div>
        <?php endif; ?>

        <h3><?= escapar($produto["nome"]) ?></h3>

        <p>
            <?= escapar($produto["descricao"] ?? "") ?>
        </p>

        <span class="preco-produto">
            R$ <?= number_format(
                (float)$produto["preco"],
                2,
                ",",
                "."
            ) ?>
        </span>

        
<button type="button" class="adicionar-carrinho"
    data-id="<?= (int)$produto['id'] ?>"
    data-nome="<?= escapar($produto['nome']) ?>"
    data-preco="<?= (float)$produto['preco'] ?>">
    Adicionar ao carrinho
</button>


    </article>
<?php endforeach; ?>

                </div>
            </section>
<?php
        endif;
    endforeach;
endif;
?>

        </div>
    </div>
</section>


<section class="secao-avaliacoes" id="avaliacoes">
    <div class="avaliacoes-cabecalho">
        <span class="subtitulo-avaliacoes">— AVALIAÇÕES —</span>
        <h2>O que dizem nossos clientes</h2>
        <p>Algumas experiências que nos enchem de orgulho.</p>
    </div>

    <div id="lista-avaliacoes">
        <?php if (empty($avaliacoes)): ?>
            <p class="avaliacoes-vazias">
                Seja a primeira pessoa a avaliar nossa loja!
            </p>
        <?php else: ?>
            <?php foreach (array_slice($avaliacoes, 0, 2) as $avaliacao): ?>
                <article class="card-avaliacao">
                    <div class="avaliacao-iniciais">
                        <?= escapar(mb_strtoupper(mb_substr(trim($avaliacao["nome"]), 0, 1, "UTF-8"), "UTF-8")) ?>
                    </div>

                    
<div class="avaliacao-conteudo">
    <h3><?= escapar($avaliacao["nome"]) ?></h3>

    <div class="estrelas" aria-label="<?= (int)$avaliacao["nota"] ?> de 5 estrelas">
        <?= str_repeat("★", (int)$avaliacao["nota"]) ?><?= str_repeat("☆", 5 - (int)$avaliacao["nota"]) ?>
    </div>

    <p><?= nl2br(escapar($avaliacao["comentario"])) ?></p>

    <small>
        <?= date("d/m/Y", strtotime($avaliacao["criado_em"])) ?>
    </small>

    <form action="excluir_avaliacao.php" method="post"
          onsubmit="return confirm('Tem certeza que deseja excluir esta avaliação?');"
          class="form-excluir-avaliacao">

        <input type="hidden" name="id"
               value="<?= (int)$avaliacao["id"] ?>">

        <button type="submit">Excluir avaliação</button>
    </form>
</div>

                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <details class="avaliacao-detalhes">
        <summary>DEIXE SUA AVALIAÇÃO →</summary>

        <div class="formulario-avaliacao">
            <h3>Conte sua experiência</h3>
            <p>Sua opinião é muito importante para nós.</p>

            <form action="salvar_avaliacao.php" method="post">
                <label for="nome-avaliacao">Seu nome</label>
                <input
                    type="text"
                    id="nome-avaliacao"
                    name="nome"
                    maxlength="100"
                    required
                >

                <label for="nota-avaliacao">Sua nota</label>
                <select id="nota-avaliacao" name="nota" required>
                    <option value="">Selecione as estrelas</option>
                    <option value="5">★★★★★ - Excelente</option>
                    <option value="4">★★★★☆ - Muito bom</option>
                    <option value="3">★★★☆☆ - Bom</option>
                    <option value="2">★★☆☆☆ - Regular</option>
                    <option value="1">★☆☆☆☆ - Ruim</option>
                </select>

                <label for="comentario-avaliacao">Seu comentário</label>
                <textarea
                    id="comentario-avaliacao"
                    name="comentario"
                    rows="3"
                    maxlength="2000"
                    required
                ></textarea>

                <button type="submit">Enviar avaliação</button>
            </form>
        </div>
    </details>
</section>



<footer>
    <p>Vallontano Vinhos Nobres</p>
    <p>Contato@vallontano.com</p>
    <p>Instagram: @Vallontano</p>
    <br>
    <p>Projeto demonstrativo desenvolvido para fins de apresentação</p>
    <p>Desenvolvido por Vitoria Cristina Feitosa da Silva | Gustavo de Lima Alves | Geovany Fidelis Anthero</p>
</footer>

</body>
</html>
