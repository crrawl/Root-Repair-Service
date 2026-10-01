<?php
declare(strict_types=1);
require __DIR__ . "/../src/bootstrap.php";
$pdo = db();
foreach (explode(";", file_get_contents(__DIR__ . "/../database/schema.sql")) as $sql) {
    if (trim($sql) !== "") {
        $pdo->exec($sql);
    }
}
require __DIR__ . "/migrate.php";
if ($pdo->query("SELECT value FROM app_settings WHERE name='seed_complete_v3'")->fetchColumn() === "1") {
    echo "RootRepair shēma pārbaudīta. Esošie dati saglabāti.\n";
    return;
}
// 35b08bf594cd982c290937f638bc4f9631b9370fae8bf5ad096beec602f71059
$pdo->beginTransaction();
try {
    $accounts = [
        ["Juris Kalniņš", "juris.kalnins@rootrepair.lv", "R00tR3p4ir2000", "owner"],
        ["Inga Lapietiņa", "inga.lapietina@rootrepair.lv", "rakesh@suman@2024", "technician"],
        ["Rihards Zariņš", "rihards.zarins@rootrepair.lv", "Rihards2026!", "technician"],
        ["Elīna Bērziņa", "elina.berzina@google.com", "Elina2026!", "customer"],
        ["Oskars Liepa", "oskars.liepa@google.com", "Oskars2026!", "customer"],
        ["Marta Kalniņa", "marta.kalnina@google.com", "Marta2026!", "customer"],
        ["Pēteris Ozols", "peteris.ozols@google.com", "Peteris2026!", "customer"],
    ];
    $insertUser = $pdo->prepare("INSERT INTO users (name,email,password_hash,role,note) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),password_hash=VALUES(password_hash),role=VALUES(role),note=VALUES(note)");
    $ids = [];
    foreach ($accounts as [$name, $email, $password, $role]) {
        $note = match ($email) {
            "juris.kalnins@rootrepair.lv" => "https://discord.gg/hZJBGhu6mS",
            "inga.lapietina@rootrepair.lv" => "https://discord.gg/hZJBGhu6mS",
            default => null,
        };
        $insertUser->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $note]);
        $find = $pdo->prepare("SELECT id FROM users WHERE email=?");
        $find->execute([$email]);
        $ids[$email] = (int) $find->fetchColumn();
    }
    $repairs = [
        ["RR-1042", "elina.berzina@google.com", "Elīna Bērziņa", "Portatīvais dators", "Lenovo ThinkPad T480", "Dators neieslēdzas pēc uzlādes. Lādētāja indikators deg.", "diagnostics", "Pārbaudām barošanas ķēdi. Par izmaksām sazināsimies pirms remonta.", "inga.lapietina@rootrepair.lv", 2],
        ["RR-1043", "oskars.liepa@google.com", "Oskars Liepa", "Telefons", "Samsung Galaxy S22", "Pēc kritiena saplaisāja ekrāns, skārienvadība darbojas daļēji.", "repairing", "Ekrāna modulis pasūtīts; paredzamais izpildes laiks ir divas darba dienas.", "rihards.zarins@rootrepair.lv", 3],
        ["RR-1044", "marta.kalnina@google.com", "Marta Kalniņa", "Spēļu konsole", "PlayStation 5", "Konsole skaļi darbojas un pēc laika izslēdzas.", "ready", "Dzesēšanas sistēma iztīrīta un pārbaudīta slodzē. Ierīce gatava saņemšanai.", "inga.lapietina@rootrepair.lv", 5],
        ["RR-1045", "peteris.ozols@google.com", "Pēteris Ozols", "Tīkla iekārta", "ASUS RT-AX58U", "Wi-Fi savienojums periodiski pazūd pēc programmatūras atjauninājuma.", "received", "Ierīce pieņemta darbnīcā. Veiksim programmatūras un bezvadu moduļa pārbaudi.", "rihards.zarins@rootrepair.lv", 7],
        ["RR-1046", "elina.berzina@google.com", "Elīna Bērziņa", "Telefons", "iPhone 13", "Akumulators ātri izlādējas; korpuss nav bojāts.", "collected", "Akumulators nomainīts, uzlādes cikls un kameras pārbaudītas.", "inga.lapietina@rootrepair.lv", 20],
        ["RR-1047", "oskars.liepa@google.com", "Oskars Liepa", "Galda dators", "Custom PC / Ryzen 7", "Dators restartējas spēļu laikā.", "submitted", "Pieteikums saņemts. Gaidām ierīces nogādāšanu darbnīcā.", "rihards.zarins@rootrepair.lv", 1],
        ["RR-1048", "marta.kalnina@google.com", "Marta Kalniņa", "Planšete", "iPad Air 5", "Uzlādes ligzda kustas, kabelis neturas stabili.", "diagnostics", "Pārbaudām USB-C ligzdu un uzlādes kontrolieri.", "inga.lapietina@rootrepair.lv", 4],
        ["RR-1049", "peteris.ozols@google.com", "Pēteris Ozols", "Spēļu konsole", "Nintendo Switch OLED", "Kreisais Joy-Con dažreiz zaudē savienojumu.", "repairing", "Tiek pārbaudīta sliede un bezvadu savienojuma antena.", "rihards.zarins@rootrepair.lv", 2],
    ];
    $insertRepair = $pdo->prepare("INSERT INTO repairs (reference,customer_id,customer_name,contact_email,device_type,device,description,status,summary,assigned_to,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE reference=VALUES(reference)");
    foreach ($repairs as [$ref, $email, $customerName, $type, $device, $description, $status, $summary, $technicianEmail, $ageDays]) {
        $created = (new DateTimeImmutable("-$ageDays days"))->format("Y-m-d H:i:s");
        $insertRepair->execute([$ref, $ids[$email], $customerName, $email, $type, $device, $description, $status, $summary, $ids[$technicianEmail], $created, $created]);
    }
    $pdo->exec("INSERT INTO lab_notes (id,title,content) VALUES (1,'Statusa pārbaudes piezīme','Statusa pārbaudes testa ieraksts.') ON DUPLICATE KEY UPDATE content=VALUES(content)");
    $pdo->exec("INSERT INTO app_settings (name,value) VALUES ('seed_complete_v3','1') ON DUPLICATE KEY UPDATE value='1'");
    $pdo->commit();
    echo "RootRepair datubāze sagatavota ar 7 kontiem un 8 remonta pieteikumiem.\n";
} catch (Throwable $error) {
    $pdo->rollBack();
    throw $error;
}

