<?php
// Papildina esošo shēmu, nedzēšot lietotājus un pieteikumus.
foreach (
    [
        "photos_json" => "JSON NULL",
        "photo_path" => "VARCHAR(255) NULL",
        "photo_original" => "VARCHAR(255) NULL",
        "assigned_to" => "INT UNSIGNED NULL",
        "internal_note" => "TEXT NULL",
    ]
    as $column => $type
) {
    $check = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?",
    );
    $check->execute(["repairs", $column]);
    if (!(int) $check->fetchColumn()) {
        $pdo->exec("ALTER TABLE repairs ADD COLUMN " . $column . " " . $type);
    }
}

$check = $pdo->prepare(
    "SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='repairs' AND COLUMN_NAME='assigned_to' AND REFERENCED_TABLE_NAME='users'",
);
$check->execute();
if (!(int) $check->fetchColumn()) {
    $pdo->exec(
        "ALTER TABLE repairs ADD CONSTRAINT fk_repairs_assigned_to FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL",
    );
}

$check = $pdo->prepare(
    "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='note'",
);
$check->execute();
if (!(int) $check->fetchColumn()) {
    $pdo->exec("ALTER TABLE users ADD COLUMN note TEXT NULL");
}

