<?php
require __DIR__ . "/config.php";

echo "Banco conectado: ";
echo htmlspecialchars(
    $conexao->query("SELECT DATABASE()")->fetch_row()[0]
);

echo "<br>Administradores cadastrados: ";
echo $conexao
    ->query("SELECT COUNT(*) FROM administradores")
    ->fetch_row()[0];