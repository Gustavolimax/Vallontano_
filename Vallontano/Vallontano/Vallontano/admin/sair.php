<!--  Encerrar a sessao administrativa -->
  <?php
require __DIR__ . "/proteger.php";

$_SESSION = [];
session_destroy();

header("Location: login.php");
exit;