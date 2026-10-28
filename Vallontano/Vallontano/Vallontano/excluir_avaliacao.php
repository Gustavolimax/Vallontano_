
<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php#avaliacoes");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php?avaliacao=erro#avaliacoes");
    exit;
}

try {
    $stmt = $conexao->prepare(
        "DELETE FROM avaliacoes WHERE id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?avaliacao=excluida#avaliacoes");
    exit;
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    header("Location: index.php?avaliacao=erro#avaliacoes");
    exit;
}
