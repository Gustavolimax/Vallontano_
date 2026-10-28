
<?php
require __DIR__ . "/proteger.php";
require __DIR__ . "/../config.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

$produtoEditar = null;

if (isset($_GET["editar"]) && ctype_digit($_GET["editar"])) {
    $id = (int) $_GET["editar"];

    $stmt = $conexao->prepare(
        "SELECT id, nome, descricao, preco, imagem, categoria
         FROM produtos WHERE id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $produtoEditar = $stmt->get_result()->fetch_assoc();
}

$produtos = $conexao->query(
    "SELECT id, nome, descricao, preco, imagem, categoria
     FROM produtos ORDER BY id DESC"
);

$mensagem = $_SESSION["mensagem_admin"] ?? "";
unset($_SESSION["mensagem_admin"]);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gerenciar produtos - Vallontano</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f5f7; color: #222; margin: 0; }
        main { max-width: 1000px; margin: 30px auto; padding: 20px; }
        section, .produto { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        input, textarea { box-sizing: border-box; width: 100%; padding: 10px; margin: 6px 0 14px; }
        button, .botao { display: inline-block; padding: 10px 14px; background: #243447; color: white; border: 0; border-radius: 5px; text-decoration: none; cursor: pointer; }
        .grade { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
        .produto img { max-width: 100%; height: 180px; object-fit: contain; }
        .aviso { background: #e8f5e9; padding: 12px; }
        .excluir { background: #a52828; }
    </style>
</head>
<body>
<main>
    <h1>Gerenciar produtos</h1>
    <p>Olá, <?= escapar($_SESSION["admin_usuario"] ?? "administrador") ?>!</p>
    <p><a href="../index.php">Ver catálogo público</a> | <a href="sair.php">Sair</a></p>

    <?php if ($mensagem !== ""): ?>
        <p class="aviso"><?= escapar($mensagem) ?></p>
    <?php endif; ?>

    <section>
        <h2><?= $produtoEditar ? "Editar produto" : "Cadastrar produto" ?></h2>

        <form action="salvar.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token"
                   value="<?= escapar($_SESSION["csrf_token"]) ?>">
            <input type="hidden" name="id"
                   value="<?= escapar($produtoEditar["id"] ?? "") ?>">

            <label>Nome do produto</label>
            <input name="nome" maxlength="150" required
                   value="<?= escapar($produtoEditar["nome"] ?? "") ?>">

            <label>Descrição</label>
            <textarea name="descricao" rows="4"><?= escapar($produtoEditar["descricao"] ?? "") ?></textarea>

            <label>Preço (R$)</label>
            <input type="number" name="preco" min="0" step="0.01"
                required value="<?= escapar($produtoEditar["preco"] ?? "0.00") ?>">
            
                   
            <label>Categoria do vinho</label>
            <select name="categoria" required
             style="box-sizing:border-box;width:100%;padding:10px;margin:6px 0 14px;">
            <option value="">Selecione uma categoria</option>
            <option value="Tinto"
                <?= ($produtoEditar["categoria"] ?? "") === "Tinto" ? "selected" : "" ?>> Tinto
            </option>
            <option value="Branco"
                <?= ($produtoEditar["categoria"] ?? "") === "Branco" ? "selected" : "" ?>> Branco
            </option>
            <option value="Rose"
                <?= ($produtoEditar["categoria"] ?? "") === "Rosé" ? "selected" : "" ?>> Rosé
            </option>
            </select>


            <label>Foto (JPG, PNG ou WebP; máximo 5 MB)</label>
            <input type="file" name="imagem" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

            <?php if (!empty($produtoEditar["imagem"])): ?>
                <p>Foto atual:</p>
                <img src="../uploads/<?= rawurlencode(basename($produtoEditar["imagem"])) ?>"
                     alt="Foto atual" width="140">
                <p>Se não escolher outra foto, a atual será mantida.</p>
            <?php endif; ?>

            <button type="submit">Salvar produto</button>
            <?php if ($produtoEditar): ?>
                <a class="botao" href="produtos.php">Cancelar edição</a>
            <?php endif; ?>
        </form>
    </section>

    <h2>Produtos cadastrados</h2>
    <div class="grade">
        <?php if ($produtos->num_rows === 0): ?>
            <p>Nenhum produto cadastrado.</p>
        <?php endif; ?>

        <?php while ($produto = $produtos->fetch_assoc()): ?>
            <article class="produto">
                <?php if (!empty($produto["imagem"])): ?>
                    <img src="../uploads/<?= rawurlencode(basename($produto["imagem"])) ?>"
                         alt="<?= escapar($produto["nome"]) ?>">
                <?php endif; ?>

                <h3><?= escapar($produto["nome"]) ?></h3>
                <p>
    <strong>Categoria:</strong>
    <?= escapar($produto["categoria"] ?? "Não definida") ?>
</p>
                <p><?= nl2br(escapar($produto["descricao"] ?? "")) ?></p>
                <p><strong>R$ <?= number_format((float)$produto["preco"], 2, ",", ".") ?></strong></p>

                <a class="botao" href="produtos.php?editar=<?= (int)$produto["id"] ?>">Editar</a>

                <form action="excluir.php" method="POST"
                      onsubmit="return confirm('Deseja realmente excluir este produto?');">
                    <input type="hidden" name="csrf_token"
                           value="<?= escapar($_SESSION["csrf_token"]) ?>">
                    <input type="hidden" name="id" value="<?= (int)$produto["id"] ?>">
                    <button class="excluir" type="submit">Excluir</button>
                </form>
            </article>
        <?php endwhile; ?>
    </div>
</main>
</body>
</html>
