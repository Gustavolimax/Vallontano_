
<?php
require __DIR__ . "/proteger.php";
require __DIR__ . "/../config.php";

function voltar($mensagem, $id = 0) {
    $_SESSION["mensagem_admin"] = $mensagem;
    $destino = "produtos.php";
    if ($id > 0) {
        $destino .= "?editar=" . $id;
    }
    header("Location: " . $destino);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: produtos.php");
    exit;
}

$token = $_POST["csrf_token"] ?? "";
if (!is_string($token) ||
    !hash_equals($_SESSION["csrf_token"] ?? "", $token)) {
    http_response_code(403);
    exit("Solicitação inválida. Atualize a página e tente novamente.");
}

$idTexto = trim((string)($_POST["id"] ?? ""));
$id = ($idTexto === "") ? 0 : filter_var($idTexto, FILTER_VALIDATE_INT);
$nome = trim($_POST["nome"] ?? "");
$descricao = trim($_POST["descricao"] ?? "");
$precoTexto = str_replace(",", ".", trim($_POST["preco"] ?? ""));
$categoria = trim($_POST["categoria"] ?? "");

$categoriasPermitidas = ["Tinto", "Branco", "Rose"];



if ($id === false || $id < 0) {
    voltar("Erro no identificador do produto.", max(0, (int)$id));
}

if ($nome === "" || mb_strlen($nome) > 150) {
    voltar("Verifique o nome do produto.", max(0, (int)$id));
}

if (!is_numeric($precoTexto) ||
    (float)$precoTexto < 0 ||
    !is_finite((float)$precoTexto) ||
    (float)$precoTexto > 99999999.99) {
    voltar("O preço informado é inválido.", max(0, (int)$id));
}

if (!in_array($categoria, $categoriasPermitidas, true)) {
    voltar("Categoria inválida. Selecione Tinto, Branco ou Rose.", max(0, (int)$id));
}



$preco = (float)$precoTexto;
$imagemAtual = null;

if ($id > 0) {
    $stmt = $conexao->prepare("SELECT imagem FROM produtos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $atual = $stmt->get_result()->fetch_assoc();

    if (!$atual) {
        voltar("Produto não encontrado.");
    }

    $imagemAtual = $atual["imagem"];
}

$imagemNova = null;
$arquivoEnviado = isset($_FILES["imagem"]) &&
    $_FILES["imagem"]["error"] !== UPLOAD_ERR_NO_FILE;

if ($arquivoEnviado) {
    if ($_FILES["imagem"]["error"] !== UPLOAD_ERR_OK ||
        $_FILES["imagem"]["size"] > 5 * 1024 * 1024) {
        voltar("A foto deve ter até 5 MB. Tente enviar novamente.", (int)$id);
    }

    $tmp = $_FILES["imagem"]["tmp_name"];
    $info = @getimagesize($tmp);
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);

    $tiposPermitidos = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    if (!$info || !isset($tiposPermitidos[$mime])) {
        voltar("Use uma imagem JPG, PNG ou WebP válida.", (int)$id);
    }

    $pasta = __DIR__ . "/../uploads/";

    if (!is_dir($pasta) && !mkdir($pasta, 0755, true)) {
        voltar("Não foi possível preparar a pasta de imagens.", (int)$id);
    }

    $imagemNova = bin2hex(random_bytes(16)) . "." . $tiposPermitidos[$mime];

    if (!move_uploaded_file($tmp, $pasta . $imagemNova)) {
        voltar("Não foi possível salvar a foto.", (int)$id);
    }
}

$imagemFinal = $imagemNova ?? $imagemAtual;

try {
    if ($id > 0) {
        $stmt = $conexao->prepare(
            "UPDATE produtos
             SET nome = ?, descricao = ?, preco = ?, imagem = ?, categoria = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "ssdssi",
            $nome,
            $descricao,
            $preco,
            $imagemFinal,
            $categoria,
            $id
        );

        $stmt->execute();
        $mensagem = "Produto atualizado com sucesso!";

    } else {
        $stmt = $conexao->prepare(
            "INSERT INTO produtos (nome, descricao, preco, imagem, categoria)
             VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssdss",
            $nome,
            $descricao,
            $preco,
            $imagemFinal,
            $categoria
        );

        $stmt->execute();
        $mensagem = "Produto cadastrado com sucesso!";
    }

} catch (Throwable $e) {
    if ($imagemNova !== null && isset($pasta)) {
        @unlink($pasta . $imagemNova);
    }

    error_log($e->getMessage());
    voltar("Não foi possível salvar o produto. Verifique os dados e tente novamente.", (int)$id);
}

if ($imagemNova !== null && !empty($imagemAtual)) {
    $antiga = __DIR__ . "/../uploads/" . basename($imagemAtual);

    if (is_file($antiga)) {
        @unlink($antiga);
    }
}

$_SESSION["mensagem_admin"] = $mensagem;
header("Location: produtos.php");
exit;

