<!--  Protecao das paginas por login -->
  <?php
session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Strict"
]);
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}