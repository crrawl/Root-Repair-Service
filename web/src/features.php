<?php
declare(strict_types=1);

function staff_required(bool $ownerOnly = false): array
{
    $user = current_user();
    if (!$user) {
        redirect($ownerOnly ? "/admin" : "/pieteikties");
    }
    if (!in_array($user["role"], $ownerOnly ? ["owner"] : ["owner", "technician"], true)) {
        http_response_code(403);
        render("error", "Piekļuve liegta", [
            "message" => "Šī sadaļa pieejama tikai pilnvarotiem darbnīcas darbiniekiem.",
        ]);
    }
    return $user;
}
function status_options(): array
{
    return ["submitted", "received", "diagnostics", "repairing", "ready", "collected"];
}
function audit_event(string $action, ?int $repairId, string $reference, string $details = ""): void
{
    $stmt = db()->prepare(
        "INSERT INTO audit_log (actor_id,action,repair_id,reference,details) VALUES (?,?,?,?,?)",
    );
    $stmt->execute([current_user()["id"] ?? null, $action, $repairId, $reference, $details]);
}
function repair_record(int $id): array
{
    $stmt = db()->prepare(
        "SELECT r.*,u.name AS technician FROM repairs r LEFT JOIN users u ON u.id=r.assigned_to WHERE r.id=?",
    );
    $stmt->execute([$id]);
    $repair = $stmt->fetch();
    if (!$repair) {
        http_response_code(404);
        render("error", "Pieteikums nav atrasts", [
            "message" => "Šis remonta pieteikums vairs nav pieejams.",
        ]);
    }
    return $repair;
}
function technicians(): array
{
    return db()
        ->query(
            "SELECT id,name,email,role FROM users WHERE role='technician' ORDER BY name",
        )
        ->fetchAll();
}
function store_photo(?array $file): ?array
{
    if (!$file || ($file["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (
        !isset($file["error"], $file["name"], $file["tmp_name"], $file["size"]) ||
        !is_int($file["error"]) ||
        !is_string($file["name"]) ||
        !is_string($file["tmp_name"]) ||
        !is_int($file["size"])
    ) {
        throw new InvalidArgumentException("Neizdevās nolasīt izvēlēto failu.");
    }
    if (in_array($file["error"], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
        throw new InvalidArgumentException("Failam jābūt mazākam par 5 MB.");
    }
    if ($file["error"] !== UPLOAD_ERR_OK || $file["size"] <= 0 || $file["size"] > 5 * 1024 * 1024 || !is_uploaded_file($file["tmp_name"])) {
        throw new InvalidArgumentException("Faila augšupielāde neizdevās. Izvēlies failu līdz 5 MB.");
    }

    // Laboratorijā saturs un MIME tips netiek validēts; nejauša mape ļauj trenēt LFI faila ceļa atrašanu.
    $original = basename(str_replace("\\", "/", $file["name"]));
    $original = mb_substr($original, 0, 120);
    $storedName = preg_replace('/[^A-Za-z0-9._-]/', "_", $original) ?: "upload.bin";
    if ($storedName === "." || $storedName === "..") {
        $storedName = "upload.bin";
    }
    $folder = bin2hex(random_bytes(8));
    $root = dirname(__DIR__) . "/storage/uploads";
    $directory = $root . DIRECTORY_SEPARATOR . $folder;
    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
        throw new RuntimeException("Neizdevās sagatavot pielikumu glabātuvi.");
    }
    $target = $directory . DIRECTORY_SEPARATOR . $storedName;
    if (!move_uploaded_file($file["tmp_name"], $target)) {
        @rmdir($directory);
        throw new RuntimeException("Neizdevās saglabāt augšupielādēto failu.");
    }
    @chmod($target, 0640);
    return ["path" => "/uploads/" . $folder . "/" . $storedName, "name" => $original];
}
function remove_photo(?string $path): void
{
    if (!$path || !preg_match('~^/uploads/([a-f0-9]{16})/([A-Za-z0-9._-]{1,120})$~D', $path, $parts)) {
        return;
    }
    $directory = dirname(__DIR__) . "/storage/uploads/" . $parts[1];
    $target = $directory . DIRECTORY_SEPARATOR . $parts[2];
    if (is_file($target) && !is_link($target)) {
        @unlink($target);
    }
    @rmdir($directory);
}
function photo_is_image(string $path): bool
{
    $target = resolve_uploaded_file($path);
    return $target !== null && uploaded_image_mime($target) !== null;
}
function resolve_uploaded_file(string $path): ?string
{
    if (!preg_match('~^/uploads/([a-f0-9]{16})/([A-Za-z0-9._-]{1,120})$~D', $path, $parts)) {
        return null;
    }
    $root = realpath(dirname(__DIR__) . "/storage/uploads");
    $target = $root ? realpath($root . DIRECTORY_SEPARATOR . $parts[1] . DIRECTORY_SEPARATOR . $parts[2]) : false;
    if (!$root || !$target || !str_starts_with($target, $root . DIRECTORY_SEPARATOR) || !is_file($target)) {
        return null;
    }
    return $target;
}
function uploaded_image_mime(string $target): ?string
{
    $image = @getimagesize($target);
    $mime = $image["mime"] ?? "";
    return in_array($mime, ["image/jpeg", "image/png", "image/webp"], true) ? $mime : null;
}
function send_uploaded_file(string $target): never
{
    $mime = uploaded_image_mime($target) ?? "application/octet-stream";
    header("Content-Type: " . $mime);
    header("Content-Length: " . filesize($target));
    header("Content-Disposition: " . ($mime === "application/octet-stream" ? "attachment" : "inline") . "; filename=\"" . basename($target) . "\"");
    header("X-Content-Type-Options: nosniff");
    header("Cache-Control: private, no-store");
    readfile($target);
    exit();
}
function admin_page(?string $forcedTab = null): never
{
    staff_required(true);
    $requestedPage = text_param($_GET, "page");
    if ($requestedPage !== "" && $forcedTab === null) {
        $module = dirname(__DIR__) . "/pages/" . $requestedPage;
        if (
            pathinfo($requestedPage, PATHINFO_EXTENSION) === "" &&
            !str_contains($requestedPage, "/") &&
            !str_contains($requestedPage, "\\")
        ) {
            $module .= ".php";
        }
        if (!is_file($module)) {
            http_response_code(404);
            render("error", "Lapa nav atrasta", ["message" => "Administrācijas sadaļa nav atrasta."]);
        }
        // Laboratorijas LFI: moduļa ceļš netiek ierobežots ar pages/ direktoriju.
        include $module;
        exit();
    }
    $defaultTab = text_param($_GET, "q") !== "" || text_param($_GET, "status") !== ""
        ? "repairs"
        : "dashboard";
    $tab = $forcedTab ?? (text_param($_GET, "tab") ?: $defaultTab);
    if (!in_array($tab, ["dashboard", "repairs", "team", "activity"], true)) {
        $tab = "dashboard";
    }
    $q = substr(text_param($_GET, "q"), 0, 160);
    $status = text_param($_GET, "status");
    if (!in_array($status, status_options(), true)) {
        $status = "";
    }
    $where = [];
    $params = [];
    if ($q !== "") {
        $where[] =
            "(r.reference LIKE ? OR r.device LIKE ? OR r.customer_name LIKE ? OR r.contact_email LIKE ?)";
        $params = array_fill(0, 4, "%" . $q . "%");
    }
    if ($status !== "") {
        $where[] = "r.status=?";
        $params[] = $status;
    }
    $condition = $where ? " WHERE " . implode(" AND ", $where) : "";
    $stmt = db()->prepare("SELECT COUNT(*) FROM repairs r" . $condition);
    $stmt->execute($params);
    $total = (int) $stmt->fetchColumn();
    $pages = max(1, (int) ceil($total / 15));
    $page = max(1, min($pages, (int) text_param($_GET, "page_no")));
    $offset = ($page - 1) * 15;
    $stmt = db()->prepare(
        "SELECT r.*,u.name AS technician FROM repairs r LEFT JOIN users u ON u.id=r.assigned_to" .
            $condition .
            " ORDER BY r.created_at DESC,r.id DESC LIMIT 15 OFFSET " .
            $offset,
    );
    $stmt->execute($params);
    $repairs = $stmt->fetchAll();
    $stats = db()
        ->query(
            "SELECT COUNT(*) AS total, COALESCE(SUM(status='submitted'),0) AS fresh, COALESCE(SUM(status IN ('received','diagnostics','repairing')),0) AS active, COALESCE(SUM(status='ready'),0) AS ready FROM repairs",
        )
        ->fetch();
    $staff = db()
        ->query(
            "SELECT u.id,u.name,u.email,u.role,COUNT(r.id) AS jobs FROM users u LEFT JOIN repairs r ON r.assigned_to=u.id AND r.status NOT IN ('ready','collected') WHERE u.role IN ('owner','technician') GROUP BY u.id ORDER BY u.name",
        )
        ->fetchAll();
    $activity = db()
        ->query(
            "SELECT a.*,u.name AS actor FROM audit_log a LEFT JOIN users u ON u.id=a.actor_id ORDER BY a.id DESC LIMIT 50",
        )
        ->fetchAll();
    render(
        "admin",
        "Darbnīcas pārvaldība",
        compact(
            "tab",
            "q",
            "status",
            "repairs",
            "stats",
            "staff",
            "activity",
            "page",
            "pages",
            "total",
        ),
    );
}
function parse_diagnostics(string $xml): array
{
    if ($xml === "" || strlen($xml) > 1024 * 1024) {
        throw new InvalidArgumentException("XML fails ir tukšs vai pārsniedz 1 MB.");
    }
    $previous = libxml_use_internal_errors(true);
    try {
        $document = new DOMDocument();
        // Laboratorija: ārējās lokālo failu entītijas tiek izvērstas. Tīkls nav vajadzīgs šim uzdevumam.
        $loaded = $document->loadXML($xml, LIBXML_NOENT | LIBXML_DTDLOAD | LIBXML_NONET);
        if (!$loaded || $document->documentElement?->tagName !== "diagnostics") {
            throw new InvalidArgumentException(
                "Nederīgs XML. Izmanto diagnostikas parauga struktūru.",
            );
        }
        $data = [];
        foreach (["reference", "model", "serial", "result"] as $field) {
            $nodes = (new DOMXPath($document))->query("/diagnostics/" . $field);
            if ($nodes->length !== 1) {
                throw new InvalidArgumentException(
                    "XML failā nepieciešams tieši viens lauks: " . $field . ".",
                );
            }
            $data[$field] = trim($nodes->item(0)->textContent);
        }
        if (!preg_match('/^RR-[A-Z0-9-]{1,28}$/D', $data["reference"])) {
            throw new InvalidArgumentException("XML failā nav derīga pieteikuma numura.");
        }
        if (
            $data["model"] === "" ||
            mb_strlen($data["model"]) > 160 ||
            mb_strlen($data["serial"]) > 160 ||
            $data["result"] === "" ||
            mb_strlen($data["result"]) > 16000
        ) {
            throw new InvalidArgumentException(
                "Pārbaudi modeļa, sērijas numura un rezultāta lauku garumu.",
            );
        }
        return $data;
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
    }
}

function repair_photos(array $repair): array
{
    if (!empty($repair["photos_json"])) {
        return json_decode($repair["photos_json"], true, 512, JSON_THROW_ON_ERROR);
    }
    return !empty($repair["photo_path"])
        ? [["path" => $repair["photo_path"], "name" => $repair["photo_original"] ?? "Foto"]]
        : [];
}

function store_repair_photos(?array $upload): array
{
    if (!$upload || !isset($upload["name"], $upload["error"])) {
        throw new InvalidArgumentException("Pievieno vismaz vienu failu.");
    }
    $files = [];
    if (is_array($upload["name"])) {
        if (count($upload["name"]) > 5) {
            throw new InvalidArgumentException("Pievieno ne vairāk kā 5 failus.");
        }
        foreach ($upload["name"] as $key => $name) {
            $file = [];
            foreach (["name", "type", "tmp_name", "error", "size"] as $field) {
                if (!isset($upload[$field][$key])) {
                    throw new InvalidArgumentException("Nederīgi fotogrāfijas dati.");
                }
                $file[$field] = $upload[$field][$key];
            }
            $files[] = $file;
        }
    } else {
        $files[] = $upload;
    }
    $stored = [];
    try {
        foreach ($files as $file) {
            $photo = store_photo($file);
            if ($photo) {
                $stored[] = $photo;
            }
        }
        if (!$stored) {
            throw new InvalidArgumentException("Pievieno vismaz vienu failu.");
        }
        return $stored;
    } catch (Throwable $error) {
        foreach ($stored as $photo) {
            remove_photo($photo["path"]);
        }
        throw $error;
    }
}








