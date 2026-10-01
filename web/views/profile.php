<section class="profile-page">
    <div class="profile-card">
        <header class="profile-header">
            <div class="profile-mark" aria-hidden="true">RR</div>
            <div class="profile-identity">
                <p class="eyebrow">ROOTREPAIR · KONTA</p>
                <h1><?= e($profile['name']) ?></h1>
                <p class="profile-caption">Lietotāja profils un konta informācija</p>
            </div>
            <span class="profile-role profile-role-<?= e($profile['role']) ?>">
                <?= e(['customer' => 'Klients', 'technician' => 'Tehniķis', 'owner' => 'Īpašnieks'][$profile['role']] ?? $profile['role']) ?>
            </span>
        </header>

        <div class="profile-divider"></div>

        <dl class="profile-details">
            <div>
                <dt>E-pasta adrese</dt>
                <dd><?= e($profile['email']) ?></dd>
            </div>
            <div>
                <dt>Konta ID</dt>
                <dd class="profile-id">#<?= (int) $profile['id'] ?></dd>
            </div>
            <div>
                <dt>Reģistrējies</dt>
                <dd><?= e(pretty_date($profile['created_at'])) ?></dd>
            </div>
        </dl>

        <?php if (!empty($profile["note"])): ?>
        <section class="profile-note">
            <h2>Piezīme</h2>
            <p><?php if (str_starts_with((string) $profile["note"], "https://discord.gg/")): ?><a href="<?= e($profile["note"]) ?>" target="_blank" rel="noopener noreferrer">Pievienoties mūsu Discord grupai</a><?php else: ?><?= e($profile["note"]) ?><?php endif; ?></p>
        </section>
        <?php endif; ?>

        <footer class="profile-footer">
            <span><i aria-hidden="true"></i> RootRepair lietotājs</span>
            <a class="button secondary" href="/mani-remonti">Atpakaļ uz kontu <span aria-hidden="true">→</span></a>
        </footer>
    </div>
</section>

