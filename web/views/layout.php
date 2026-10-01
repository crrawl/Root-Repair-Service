<!doctype html>
<html lang="lv">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <meta
            name="description"
            content="RootRepair — datoru, telefonu, konsoļu un tīkla iekārtu remonts Rīgā."
        />
        <meta name="theme-color" content="#202020" />
        <title><?= e($title) ?> · RootRepair</title>
        <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml" />
        <link rel="stylesheet" href="/assets/style.css" />
        <script src="/assets/app.js" defer></script>
    </head>
    <body>
        <a class="skip-link" href="#saturs">Pāriet uz saturu</a>
        <div class="shell">
            <header class="header">
                <a class="brand" href="/" aria-label="RootRepair sākumlapa"
                    >Root<span>Repair</span><i aria-hidden="true"></i
                ></a>
                <button
                    class="menu-toggle"
                    type="button"
                    aria-expanded="false"
                    aria-controls="navigation"
                >
                    Izvēlne <span aria-hidden="true">☰</span>
                </button>
                <nav id="navigation" class="navigation" aria-label="Galvenā navigācija">
                    <?php foreach (['/'=>'Sākums','/pakalpojumi'=>'Pakalpojumi','/remonta-statuss'=>'Remonta statuss','/instrukcijas'=>'Instrukcijas','/kontakti'=>'Kontakti'] as $url=>$label): ?>
                    <a href="<?= $url ?>" <?= $active===$url ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
                    <?php endforeach; ?> <?php if (current_user()): ?>
                    <a class="nav-login" href="<?= current_user()['role']==='owner' ? '/admin' : '/mani-remonti' ?>">Mans konts</a>
                    <?php else: ?><a class="nav-login" href="/pieteikties" <?= $active==='/pieteikties' ? ' aria-current="page"' : '' ?>
                        >Pieteikties</a
                    ><?php endif; ?>
                </nav>
            </header>
            <main id="saturs">
                <?php if ($notice): ?>
                <div class="notice" role="status"><?= e($notice) ?></div>
                <?php endif; ?> <?php require __DIR__ . '/' . $view . '.php'; ?>
            </main>
            <footer class="footer">
                <span>© <?= date('Y') ?> RootRepair</span
                ><span>Ierīču remonta serviss · Rīga</span><a href="/kontakti">Sazināties</a>
            </footer>
        </div>
    </body>
</html>
