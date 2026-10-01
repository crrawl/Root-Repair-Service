<section class="delete-page">
    <a class="back-link" href="/admin/remonts?id=<?= (int)$repair['id'] ?>">← Atpakaļ uz pieteikumu</a>
    <p class="eyebrow">PIETEIKUMA PĀRVALDĪBA</p>
    <h1>Dzēst šo pieteikumu?</h1>
    <p class="muted">
        Šo darbību nevar atsaukt. Pieteikums, pievienotais foto un diagnostikas atskaites tiks
        neatgriezeniski izdzēsti. Darbība paliks audita vēsturē.
    </p>
    <dl class="delete-summary">
        <div>
            <dt>Pieteikums</dt>
            <dd><?= e($repair['reference']) ?></dd>
        </div>
        <div>
            <dt>Ierīce</dt>
            <dd><?= e($repair['device']) ?></dd>
        </div>
        <div>
            <dt>Klients</dt>
            <dd><?= e($repair['customer_name']) ?></dd>
        </div>
    </dl>
    <form method="post" action="/admin/dzest" data-submit>
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>" /><input
            type="hidden"
            name="id"
            value="<?= (int)$repair['id'] ?>"
        /><label class="checkbox-label"
            ><input type="checkbox" name="confirm" value="delete" required /> Saprotu, ka pieteikums
            un pielikumi tiks izdzēsti.</label
        >
        <div class="heading-actions">
            <button class="button" type="submit">Neatgriezeniski dzēst</button
            ><a class="button secondary" href="/admin/remonts?id=<?= (int)$repair['id'] ?>">Atcelt</a>
        </div>
    </form>
</section>
