<?php
declare(strict_types=1);
date_default_timezone_set("Europe/Riga");
require __DIR__ . "/features.php";
if (PHP_SAPI !== "cli") {
    // Novākšana pēc pārejas uz vienoto sesijas nosaukumu.
    if (isset($_COOKIE["PHPSESSID"])) {
        setcookie("PHPSESSID", "", [
            "expires" => time() - 3600,
            "path" => "/",
            "httponly" => true,
            "samesite" => "Lax",
        ]);
        unset($_COOKIE["PHPSESSID"]);
    }
    session_start();
}
function db(): PDO
{
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO(
            "mysql:host=" .
                (getenv("DB_HOST") ?: "mysql") .
                ";port=" .
                (getenv("DB_PORT") ?: "3306") .
                ";dbname=" .
                (getenv("DB_NAME") ?: "rootrepair") .
                ";charset=utf8mb4",
            getenv("DB_USER") ?: "rootrepair",
            getenv("DB_PASSWORD") ?: "rootrepair_lab",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
            ],
        );
    }
    return $pdo;
}
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
function text_param(array $source, string $key): string
{
    return isset($source[$key]) && is_string($source[$key]) ? trim($source[$key]) : "";
}
function redirect(string $path): never
{
    header("Location: " . $path, true, 303);
    exit();
}
function current_user(): ?array
{
    return $_SESSION["user"] ?? null;
}
function csrf(): string
{
    return $_SESSION["csrf"] ??= bin2hex(random_bytes(24));
}
function check_csrf(): void
{
    if (!hash_equals(csrf(), text_param($_POST, "csrf"))) {
        http_response_code(403);
        exit("Sesija ir beigusies. Pārlādējiet lapu.");
    }
}
function flash(string $message): void
{
    $_SESSION["flash"] = $message;
}
function status_label(string $status): string
{
    return [
        "submitted" => "Pieteikts",
        "received" => "Saņemts",
        "diagnostics" => "Diagnostikā",
        "repairing" => "Remontā",
        "ready" => "Gatavs saņemšanai",
        "collected" => "Izsniegts",
    ][$status] ?? $status;
}
function pretty_date(?string $date): string
{
    if (!$date) {
        return "—";
    }
    $time = strtotime($date);
    return $time === false ? $date : date("d.m.Y.", $time);
}
function render(string $view, string $title, array $data = []): never
{
    extract($data, EXTR_SKIP);
    $active = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $notice = $_SESSION["flash"] ?? null;
    unset($_SESSION["flash"]);
    require __DIR__ . "/../views/layout.php";
    exit();
}


