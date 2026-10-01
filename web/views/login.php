<div class="auth-layout">
    <section>
        <p class="eyebrow"><?= $adminOnly ? 'ĪPAŠNIEKA PIEKĻUVE' : 'ROOTREPAIR KONTS' ?></p>
        <h1><?= $adminOnly ? 'Administrācija' : 'Laipni atpakaļ.' ?></h1>
        <p class="muted"><?= $adminOnly ? 'Pieraksties, lai pārvaldītu darbnīcas darbu.' : 'Tavi pieteikumi un remonta informācija vienuviet. Vienota piekļuve klientiem un tehniķiem.' ?></p>
        <a class="text-link" href="/remonta-statuss">Pārbaudīt remonta statusu bez konta →</a>
    </section>
    <section class="form-panel" aria-label="Pieteikšanās forma">
        <h2>Pieteikties</h2>
        <?php if ($error): ?>
        <div class="notice error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= $adminOnly ? '/admin' : '/pieteikties' ?>" data-submit>
            <div class="field">
                <label for="email">E-pasta adrese</label
                ><input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= e($email) ?>"
                    placeholder="tavs@epasts.lv"
                    autocomplete="username"
                    required
                    maxlength="190"
                />
            </div>
            <div class="field">
                <label for="password">Parole</label>
                <div class="password-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                        maxlength="255"
                    /><button
                        type="button"
                        class="show-password"
                        aria-controls="password"
                        aria-pressed="false"
                    >
                        Rādīt
                    </button>
                </div>
            </div>
            <button class="button full-width" type="submit">Pieteikties</button>
        </form>
        <p class="form-note">Par piekļuvi kontam sazinies ar <a href="/kontakti">darbnīcu</a>.</p>
    </section>
</div>
