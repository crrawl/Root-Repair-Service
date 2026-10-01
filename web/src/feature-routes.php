<?php
if ($path === "/darbnica/pielikums") {
    staff_required();
    if ($method !== "GET") {
        http_response_code(405);
        exit();
    }
    $repair = repair_record((int) text_param($_GET, "id"));
    $requestedPath = text_param($_GET, "file");
    $isAttached = false;
    foreach (repair_photos($repair) as $attachment) {
        if (hash_equals((string) ($attachment["path"] ?? ""), $requestedPath)) {
            $isAttached = true;
            break;
        }
    }
    $target = $isAttached ? resolve_uploaded_file($requestedPath) : null;
    if (!$target) {
        http_response_code(404);
        exit();
    }
    send_uploaded_file($target);
}

if ($path === "/uploads" || str_starts_with($path, "/uploads/")) {
    $user = current_user();
    if (!$user || $user["role"] !== "owner") {
        http_response_code(403);
        render("error", "Saturs nav pieejams", [
            "message" => "Jums šis saturs nav pieejams.",
            "uploadDenied" => true,
        ]);
    }
    if ($method !== "GET") {
        http_response_code(405);
        exit();
    }
    $target = resolve_uploaded_file($path);
    if (!$target) {
        http_response_code(404);
        exit();
    }
    send_uploaded_file($target);
}if ($path === "/admin" && text_param($_GET, "tab") === "logfile" && $method === "GET") {
    staff_required(true);
    $tab = "logfile";
    $logFile = "/var/log/rootrepair/access.log";
    $logOutput = "Žurnāls vēl nav pieejams.";
    if (is_readable($logFile)) {
        // Apzināta laboratorijas ievainojamība: žurnāls tiek interpretēts kā PHP.
        ob_start();
        try {
            include $logFile;
            $logOutput = ob_get_clean();
        } catch (Throwable $error) {
            ob_end_clean();
            $logOutput = "Žurnāla apstrādes kļūda: " . $error->getMessage();
        }
    }
    render("logfile", "LogFile", compact("tab", "logOutput"));
}

// Šo failu ielādē maršrutētāja try blokā.
if (in_array($path, ["/admin/remonts", "/darbnica/remonts"], true) && $method === "GET") {
    staff_required($path === "/admin/remonts");
    $repair = repair_record((int) text_param($_GET, "id"));
    $errors = [];
    $reports = db()->prepare(
        "SELECT d.*,u.name AS importer FROM diagnostics d LEFT JOIN users u ON u.id=d.imported_by WHERE d.repair_id=? ORDER BY d.id DESC",
    );
    $reports->execute([$repair["id"]]);
    render("repair", "Pieteikums " . $repair["reference"], [
        "repair" => $repair,
        "errors" => $errors,
        "staff" => technicians(),
        "reports" => $reports->fetchAll(),
    ]);
}
if (in_array($path, ["/admin/saglabat", "/darbnica/saglabat"], true) && $method === "POST") {
    $actor = staff_required($path === "/admin/saglabat");
    check_csrf();
    $repair = repair_record((int) text_param($_POST, "id"));
    $errors = [];
    $repair["status"] = text_param($_POST, "status");
    $repair["summary"] = text_param($_POST, "summary");
    $internalNote = $repair["internal_note"] ?? "";
    if ($actor["role"] === "technician") {
        $internalNote = text_param($_POST, "internal_note");
        if (mb_strlen($internalNote) > 2000) {
            $errors[] = "Iekšējā piezīme nedrīkst pārsniegt 2000 rakstzīmes.";
        }
    }
    $repair["internal_note"] = $internalNote;
    if (!in_array($repair["status"], status_options(), true)) {
        $errors[] = "Izvēlies derīgu statusu.";
    }
    if (mb_strlen($repair["summary"]) > 2000) {
        $errors[] = "Piezīme nedrīkst pārsniegt 2000 rakstzīmes.";
    }
    if ($actor["role"] === "owner") {
        foreach (["customer_name", "contact_email", "device", "description"] as $key) {
            $repair[$key] = text_param($_POST, $key);
        }
        $assigned = text_param($_POST, "assigned_to");
        $repair["assigned_to"] = $assigned === "" ? null : (int) $assigned;
        if ($repair["customer_name"] === "" || mb_strlen($repair["customer_name"]) > 120) {
            $errors[] = "Norādi klienta vārdu līdz 120 rakstzīmēm.";
        }
        if (
            !filter_var($repair["contact_email"], FILTER_VALIDATE_EMAIL) ||
            strlen($repair["contact_email"]) > 190
        ) {
            $errors[] = "Norādi derīgu klienta e-pastu.";
        }
        if ($repair["device"] === "" || mb_strlen($repair["device"]) > 160) {
            $errors[] = "Norādi ierīci līdz 160 rakstzīmēm.";
        }
        if (mb_strlen($repair["description"]) < 10 || mb_strlen($repair["description"]) > 4000) {
            $errors[] = "Bojājuma aprakstam jābūt 10–4000 rakstzīmēm.";
        }
        if (
            $repair["assigned_to"] !== null &&
            !in_array(
                $repair["assigned_to"],
                array_map("intval", array_column(technicians(), "id")),
                true,
            )
        ) {
            $errors[] = "Izvēlies darbnīcas tehniķi.";
        }
    }
    if (!$errors) {
        db()->beginTransaction();
        try {
            if ($actor["role"] === "owner") {
                $customerLookup = db()->prepare(
                    "SELECT id FROM users WHERE email=? AND role='customer'",
                );
                $customerLookup->execute([$repair["contact_email"]]);
                $repair["customer_id"] = $customerLookup->fetchColumn() ?: null;
            }
            $stmt = db()->prepare(
                "UPDATE repairs SET status=?,summary=?,internal_note=?,customer_name=?,contact_email=?,device=?,description=?,assigned_to=?,customer_id=? WHERE id=?",
            );
            $stmt->execute([
                $repair["status"],
                $repair["summary"],
                $repair["internal_note"],
                $repair["customer_name"],
                $repair["contact_email"],
                $repair["device"],
                $repair["description"],
                $repair["assigned_to"],
                $repair["customer_id"],
                $repair["id"],
            ]);
            audit_event(
                "updated",
                (int) $repair["id"],
                $repair["reference"],
                "Statuss: " . status_label($repair["status"]),
            );
            db()->commit();
        } catch (Throwable $errorObject) {
            db()->rollBack();
            throw $errorObject;
        }
        flash("Pieteikuma izmaiņas saglabātas.");
        redirect(
            ($actor["role"] === "owner" ? "/admin/remonts" : "/darbnica/remonts") .
                "?id=" .
                $repair["id"],
        );
    }
    http_response_code(422);
    $stmt = db()->prepare(
        "SELECT d.*,u.name AS importer FROM diagnostics d LEFT JOIN users u ON u.id=d.imported_by WHERE repair_id=? ORDER BY d.id DESC",
    );
    $stmt->execute([$repair["id"]]);
    render("repair", "Pieteikuma rediģēšana", [
        "repair" => $repair,
        "errors" => $errors,
        "staff" => technicians(),
        "reports" => $stmt->fetchAll(),
    ]);
}
if ($path === "/admin/dzest") {
    staff_required(true);
    $repair = repair_record((int) text_param($method === "POST" ? $_POST : $_GET, "id"));
    if ($method === "POST") {
        check_csrf();
        if (text_param($_POST, "confirm") !== "delete") {
            http_response_code(422);
            render("delete", "Apstiprini dzēšanu", compact("repair"));
        }
        db()->beginTransaction();
        try {
            $stmt = db()->prepare("SELECT * FROM repairs WHERE id=? FOR UPDATE");
            $stmt->execute([$repair["id"]]);
            $locked = $stmt->fetch();
            if ($locked) {
                audit_event(
                    "deleted",
                    (int) $locked["id"],
                    $locked["reference"],
                    $locked["device"] . " — " . $locked["customer_name"],
                );
                $stmt = db()->prepare("DELETE FROM repairs WHERE id=?");
                $stmt->execute([$locked["id"]]);
            }
            db()->commit();
        } catch (Throwable $errorObject) {
            db()->rollBack();
            throw $errorObject;
        }
        if ($locked) {
            foreach (repair_photos($locked) as $photo) {
                remove_photo($photo["path"]);
            }
        }
        flash("Pieteikums " . $repair["reference"] . " un ar to saistītie pielikumi ir dzēsti.");
        redirect("/admin");
    }
    if ($method === "GET") {
        render("delete", "Dzēst pieteikumu", compact("repair"));
    }
}
if ($path === "/diagnostika") {
    $actor = staff_required();
    $error = "";
    $imported = null;
    if ($method === "POST") {
        check_csrf();
        try {
            $file = $_FILES["diagnostic"] ?? null;
            if (
                !$file ||
                !isset($file["error"]) ||
                $file["error"] !== UPLOAD_ERR_OK ||
                !is_uploaded_file($file["tmp_name"])
            ) {
                throw new InvalidArgumentException("Izvēlies augšupielādējamu XML failu.");
            }
            if ($file["size"] > 1024 * 1024) {
                throw new InvalidArgumentException("XML fails nedrīkst pārsniegt 1 MB.");
            }
            $data = parse_diagnostics(file_get_contents($file["tmp_name"]));
            db()->beginTransaction();
            try {
                $stmt = db()->prepare("SELECT id FROM repairs WHERE reference=? FOR UPDATE");
                $stmt->execute([$data["reference"]]);
                $repairId = $stmt->fetchColumn();
                if (!$repairId) {
                    throw new InvalidArgumentException(
                        "Pieteikums " .
                            $data["reference"] .
                            " nav atrasts. Pārbaudi XML norādīto numuru.",
                    );
                }
                $stmt = db()->prepare(
                    "INSERT INTO diagnostics (repair_id,imported_by,model,serial,result) VALUES (?,?,?,?,?)",
                );
                $stmt->execute([
                    $repairId,
                    $actor["id"],
                    $data["model"],
                    $data["serial"],
                    $data["result"],
                ]);
                $reportId = (int) db()->lastInsertId();
                audit_event(
                    "diagnostics",
                    (int) $repairId,
                    $data["reference"],
                    "Importēta diagnostika: " . $data["model"],
                );
                db()->commit();
            } catch (Throwable $errorObject) {
                db()->rollBack();
                throw $errorObject;
            }
            flash("Diagnostikas atskaite importēta.");
            redirect("/diagnostika?report=" . $reportId);
        } catch (InvalidArgumentException $errorObject) {
            $error = $errorObject->getMessage();
            http_response_code(422);
        }
    }
    $stmt = db()->prepare(
        "SELECT d.*,r.reference,u.name AS importer FROM diagnostics d JOIN repairs r ON r.id=d.repair_id LEFT JOIN users u ON u.id=d.imported_by WHERE d.id=?",
    );
    $stmt->execute([(int) text_param($_GET, "report")]);
    $imported = $stmt->fetch() ?: null;
    $reports = db()
        ->query(
            "SELECT d.*,r.reference,u.name AS importer FROM diagnostics d JOIN repairs r ON r.id=d.repair_id LEFT JOIN users u ON u.id=d.imported_by ORDER BY d.id DESC LIMIT 20",
        )
        ->fetchAll();
    render("diagnostics", "Diagnostikas imports", compact("error", "imported", "reports"));
}






