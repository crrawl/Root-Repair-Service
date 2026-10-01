<?php
declare(strict_types=1);
require dirname(__DIR__) . "/src/bootstrap.php";
$path = rtrim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) ?: "/", "/") ?: "/";
if ($path === "/admin.php") {
    $path = "/admin";
}
$method = $_SERVER["REQUEST_METHOD"];

try {
    require dirname(__DIR__) . "/src/feature-routes.php";
    if (preg_match('~^/profils/([1-9][0-9]*)$~D', $path, $profileMatch) && $method === "GET") {
        $viewer = current_user();
        if (!$viewer) {
            redirect("/pieteikties");
        }
        // Laboratorijas IDOR: nav pārbaudes, vai profils pieder pašreizējam lietotājam.
        $profileQuery = db()->prepare("SELECT id,name,email,role,note,created_at FROM users WHERE id=?");
        $profileQuery->execute([(int) $profileMatch[1]]);
        $profile = $profileQuery->fetch();
        if (!$profile) {
            http_response_code(404);
            render("error", "Profils nav atrasts", ["message" => "Lietotāja profils nav atrasts."]);
        }
        render("profile", "Profils", compact("profile"));
    }
    if ($path === "/health") {
        db()->query("SELECT 1 FROM repairs LIMIT 1");
        header("Content-Type: text/plain; charset=utf-8");
        echo "ok";
        exit();
    }
    if ($path === "/izrakstities" && $method === "POST") {
        check_csrf();
        $_SESSION = [];
        session_regenerate_id(true);
        flash("Jūs esat izrakstījies.");
        redirect("/");
    }
    if ($path === "/pieteikties" || $path === "/admin") {
        $adminOnly = $path === "/admin";
        $error = "";
        $email = text_param($_POST, "email");
        if ($method === "POST") {
            // Laboratorija: nav pieprasījumu ierobežojuma, CAPTCHA vai konta bloķēšanas.
            $stmt = db()->prepare(
                "SELECT id,name,email,password_hash,role FROM users WHERE email=?",
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if (
                $user &&
                password_verify(text_param($_POST, "password"), $user["password_hash"]) &&
                (!$adminOnly || $user["role"] === "owner")
            ) {
                session_regenerate_id(true);
                unset($user["password_hash"]);
                $_SESSION["user"] = $user;
                redirect($user["role"] === "owner" ? "/admin" : "/mani-remonti");
            }
            $error = "Nepareizs e-pasts vai parole.";
        }
        if ($method === "GET" && current_user()) {
            if ($adminOnly && current_user()["role"] === "owner") {
                admin_page();
            }
            if ($adminOnly) {
                http_response_code(403);
                render("error", "Piekļuve liegta", [
                    "message" => "Administrācijas panelis pieejams tikai īpašniekam.",
                ]);
            }
            if (!$adminOnly) {
                redirect(current_user()["role"] === "owner" ? "/admin" : "/mani-remonti");
            }
        }
        render(
            "login",
            $adminOnly ? "Administrācija" : "Pieteikties",
            compact("error", "email", "adminOnly"),
        );
    }
    if ($path === "/mani-remonti") {
        $user = current_user();
        if (!$user) {
            redirect("/pieteikties");
        }
        if ($user["role"] === "customer") {
            $stmt = db()->prepare(
                "SELECT * FROM repairs WHERE customer_id=? OR contact_email=? ORDER BY created_at DESC,id DESC",
            );
            $stmt->execute([$user["id"], $user["email"]]);
            $repairs = $stmt->fetchAll();
        } else {
            $repairs = db()
                ->query("SELECT * FROM repairs ORDER BY created_at DESC,id DESC")
                ->fetchAll();
        }
        render("dashboard", $user["role"] === "customer" ? "Mani remonti" : "Darba panelis", [
            "repairs" => $repairs,
            "staff" => [],
            "owner" => false,
        ]);
    }
    if ($path === "/atjaunot-remontu" && $method === "POST") {
        $user = current_user();
        if (!$user || !in_array($user["role"], ["owner", "technician"], true)) {
            http_response_code(403);
            render("error", "Piekļuve liegta", [
                "message" => "Šī darbība pieejama tikai darbnīcas darbiniekiem.",
            ]);
        }
        check_csrf();
        $status = text_param($_POST, "status");
        $summary = text_param($_POST, "summary");
        if (
            !in_array(
                $status,
                ["submitted", "received", "diagnostics", "repairing", "ready", "collected"],
                true,
            ) ||
            strlen($summary) > 2000
        ) {
            http_response_code(422);
            render("error", "Nepareizi dati", [
                "message" => "Pārbaudiet remonta statusu un piezīmes garumu.",
            ]);
        }
        $stmt = db()->prepare("UPDATE repairs SET status=?,summary=? WHERE id=?");
        $record = repair_record((int) text_param($_POST, "id"));
        $stmt->execute([$status, $summary, $record["id"]]);
        audit_event(
            "status_updated",
            (int) $record["id"],
            $record["reference"],
            status_label($status),
        );
        flash("Remonta informācija saglabāta.");
        redirect($user["role"] === "owner" ? "/admin" : "/mani-remonti");
    }
    if ($path === "/remonta-statuss") {
        $reference = text_param($_GET, "reference");
        $results = [];
        $error = "";
        if ($reference !== "") {
            // Laboratorija: reģistrjutīgs melnais saraksts. Mazie burti ļauj apiet filtru.
            if (
                preg_match(
                    "/\\b(SELECT|UNION|OR|AND|DROP|UPDATE|DELETE|INSERT)\\b|;|--|#/",
                    $reference,
                )
            ) {
                $error = "Meklēšanas pieprasījumā atrasti neatļauti vārdi vai simboli.";
            } elseif (strlen($reference) > 250) {
                $error = "Pieteikuma numurs ir pārāk garš.";
            } else {
                try {
                    // Apzināta SQL injekcija mācību uzdevumam; citi vaicājumi ir parametrizēti.
                    $results = db()
                        ->query(
                            "SELECT reference,device,status,summary,updated_at FROM repairs WHERE reference = '$reference'",
                        )
                        ->fetchAll();
                } catch (PDOException $errorObject) {
                    $error =
                        "Neizdevās apstrādāt meklēšanas pieprasījumu. Pārbaudiet pieteikuma numuru.";
                }
            }
        }
        $receipt = null;
        if (($_SESSION["repair_receipt"]["reference"] ?? null) === $reference) {
            $receipt = $_SESSION["repair_receipt"];
            unset($_SESSION["repair_receipt"]);
        }
        header("Cache-Control: no-store, private");
        header("Referrer-Policy: no-referrer");
        render(
            "status",
            "Remonta statuss",
            compact("reference", "results", "error", "receipt"),
        );
    }
    if ($path === "/pieteikt-remontu") {
        $values = [
            "name" => current_user()["name"] ?? "",
            "email" => current_user()["email"] ?? "",
            "type" => "",
            "model" => "",
            "description" => "",
        ];
        $errors = [];
        if ($method === "POST") {
            check_csrf();
            foreach ($values as $key => $_) {
                $values[$key] = text_param($_POST, $key);
            }
            if ($values["name"] === "" || strlen($values["name"]) > 120) {
                $errors[] = "Norādiet savu vārdu (līdz 120 rakstzīmēm).";
            }
            if (
                !filter_var($values["email"], FILTER_VALIDATE_EMAIL) ||
                strlen($values["email"]) > 190
            ) {
                $errors[] = "Norādiet derīgu e-pasta adresi.";
            }
            if (
                !in_array(
                    $values["type"],
                    [
                        "Portatīvais dators",
                        "Galda dators",
                        "Telefons",
                        "Planšete",
                        "Spēļu konsole",
                        "Tīkla iekārta",
                    ],
                    true,
                )
            ) {
                $errors[] = "Izvēlieties ierīces veidu.";
            }
            if ($values["model"] === "" || strlen($values["model"]) > 160) {
                $errors[] = "Norādiet ierīces modeli (līdz 160 rakstzīmēm).";
            }
            if (strlen($values["description"]) < 10 || strlen($values["description"]) > 4000) {
                $errors[] = "Aprakstiet bojājumu 10–4000 rakstzīmēs.";
            }
            $photos = [];
            if (filter_var($values["email"], FILTER_VALIDATE_EMAIL)) {
                $existingAccount = db()->prepare("SELECT role FROM users WHERE email=?");
                $existingAccount->execute([$values["email"]]);
                $existingRole = $existingAccount->fetchColumn();
                if ($existingRole && $existingRole !== "customer") {
                    $errors[] =
                        "Šī e-pasta adrese ir piesaistīta darbinieka kontam. Izmantojiet klienta e-pasta adresi.";
                }
            }
            if (!$errors) {
                try {
                    $photos = store_repair_photos($_FILES["photo"] ?? null);
                } catch (InvalidArgumentException $uploadError) {
                    $errors[] = $uploadError->getMessage();
                }
            }
            if (!$errors) {
                $reference = "RR-" . strtoupper(bin2hex(random_bytes(4)));
                $generatedPassword = null;
                db()->beginTransaction();
                try {
                    $lookup = db()->prepare("SELECT id,role FROM users WHERE email=?");
                    $lookup->execute([$values["email"]]);
                    $account = $lookup->fetch();
                    if (!$account) {
                        $candidate = rtrim(strtr(base64_encode(random_bytes(18)), "+/", "-_"), "=");
                        $create = db()->prepare(
                            "INSERT IGNORE INTO users (name,email,password_hash,role) VALUES (?,?,?,'customer')",
                        );
                        $create->execute([
                            $values["name"],
                            $values["email"],
                            password_hash($candidate, PASSWORD_DEFAULT),
                        ]);
                        if ($create->rowCount() === 1) {
                            $generatedPassword = $candidate;
                        }
                        $lookup->execute([$values["email"]]);
                        $account = $lookup->fetch();
                        if (!$account) {
                            throw new RuntimeException("Neizdevās izveidot klienta kontu.");
                        }
                    }
                    $stmt = db()->prepare(
                        "INSERT INTO repairs (reference,customer_id,customer_name,contact_email,device_type,device,description,summary,photo_path,photo_original,photos_json) VALUES (?,?,?,?,?,?,?,?,?,?,?)",
                    );
                    $stmt->execute([
                        $reference,
                        $account["role"] === "customer" ? $account["id"] : null,
                        $values["name"],
                        $values["email"],
                        $values["type"],
                        $values["model"],
                        $values["description"],
                        "Pieteikums saņemts. Gaidām ierīci darbnīcā.",
                        $photos[0]["path"],
                        $photos[0]["name"],
                        json_encode($photos, JSON_THROW_ON_ERROR),
                    ]);
                    audit_event(
                        "created",
                        (int) db()->lastInsertId(),
                        $reference,
                        $values["model"],
                    );
                    db()->commit();
                } catch (Throwable $errorObject) {
                    db()->rollBack();
                    foreach ($photos as $photo) {
                        remove_photo($photo["path"]);
                    }
                    throw $errorObject;
                }
                $_SESSION["repair_receipt"] = [
                    "reference" => $reference,
                    "email" => $values["email"],
                    "password" => $generatedPassword,
                ];
                flash("Pieteikums pieņemts. Saglabājiet savu pieteikuma numuru: " . $reference);
                redirect("/remonta-statuss?reference=" . rawurlencode($reference));
            }
        }
        render("request", "Pieteikt remontu", compact("values", "errors"));
    }
    $pages = [
        "/" => ["home", "Ierīču remonta serviss"],
        "/pakalpojumi" => ["services", "Pakalpojumi"],
        "/instrukcijas" => ["instructions", "Instrukcijas"],
        "/kontakti" => ["contact", "Kontakti"],
        "/chat" => ["chat", "Arhivēta sarakste"],
    ];
    if ($method === "GET" && isset($pages[$path])) {
        render($pages[$path][0], $pages[$path][1]);
    }
    http_response_code(404);
    render("error", "Lapa nav atrasta", [
        "message" => "Šādas lapas nav. Atgriezieties sākumlapā vai sazinieties ar darbnīcu.",
    ]);
} catch (Throwable $errorObject) {
    error_log((string) $errorObject);
    http_response_code(503);
    if ($path === "/health") {
        header("Content-Type: text/plain");
        echo "unavailable";
        exit();
    }
    render("error", "Īslaicīgs pārtraukums", [
        "message" =>
            "Pašlaik neizdodas sasniegt darbnīcas sistēmu. Lūdzu, mēģiniet vēlreiz pēc brīža.",
    ]);
}




