
<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$banco = "meu_catalogo";
$usuario = "root";
$senha = "";

try {
    $conexao = new mysqli(
        $host,
        $usuario,
        $senha,
        $banco
    );

    $conexao->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Erro ao conectar ao banco de dados.");
}
