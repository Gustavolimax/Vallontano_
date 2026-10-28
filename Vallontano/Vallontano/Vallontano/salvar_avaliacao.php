
<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php#avaliacoes");
    exit;
}

$nome = trim($_POST["nome"] ?? "");
$nota = filter_var($_POST["nota"] ?? null, FILTER_VALIDATE_INT);
$comentario = trim($_POST["comentario"] ?? "");

if (
    $nome === "" ||
    strlen($nome) > 400 ||
    $nota === false ||
    $nota === null ||
    $nota < 1 ||
    $nota > 5 ||
    $comentario === "" ||
    strlen($comentario) > 8000
) {
    header("Location: index.php?avaliacao=erro#avaliacoes");
    exit;
}

try {
    $sql = "INSERT INTO avaliacoes (nome, nota, comentario)
            VALUES (?, ?, ?)";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sis", $nome, $nota, $comentario);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?avaliacao=sucesso#avaliacoes");
    exit;
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    header("Location: index.php?avaliacao=erro#avaliacoes");
    exit;
}
