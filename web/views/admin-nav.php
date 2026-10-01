<?php if ((current_user()["role"] ?? null) !== "owner") { return; } $currentAdminPage = text_param($_GET, "page") ?: (text_param($_GET, "tab") ?: "dashboard"); ?>
<nav class="admin-nav" aria-label="Administrācijas izvēlne">
    <!-- admin.php?page=dashboard -->
    <a href="/admin.php?page=dashboard" <?= $currentAdminPage === "dashboard" ? 'aria-current="page"' : "" ?>>Sākums</a>
    <a href="/admin.php?page=pieteikumi" <?= in_array($currentAdminPage, ["pieteikumi", "repairs"], true) ? 'aria-current="page"' : "" ?>>Pieteikumi</a>
    <a href="/admin.php?page=komanda" <?= in_array($currentAdminPage, ["komanda", "team"], true) ? 'aria-current="page"' : "" ?>>Komanda</a>
    <a href="/admin.php?page=logfile" <?= $currentAdminPage === "logfile" ? 'aria-current="page"' : "" ?>>LogFile</a>
    <!-- Komentārs HTML avotā: -->
    <!-- Moduļu sistēma: katra sadaļa ir /pages/<page>.php -->
    <!-- Lai pievienotu jaunu sadaļu, ieliec failu /pages/ mapē -->
</nav>

