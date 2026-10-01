<?php $isStaff=current_user()['role']!=='customer'; ?>
<div class="dashboard-heading">
    <section class="page-heading">
        <p class="eyebrow"><?= e(current_user()['name']) ?></p>
        <h1><?= $isStaff ? 'Darba panelis' : 'Mani remonti' ?></h1>
        <p><?= $isStaff ? 'Pieteikumi un klientam redzamā remonta gaita.' : 'Aktuālā informācija par tavām ierīcēm.' ?></p>
    </section>
        <a class="button secondary" href="/profils/<?= (int)current_user()["id"] ?>">Mans profils</a>
    <form action="/izrakstities" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>" /><button
            class="button secondary"
            type="submit"
        >
            Izrakstīties
        </button>
    </form>
</div>
<?php if($isStaff) require __DIR__.'/admin-nav.php'; ?>
<div class="section-title">
    <h2>Remonta pieteikumi <span class="count-label"><?= count($repairs) ?></span></h2>
</div>
<?php if(!$repairs): ?>
<div class="empty-state">
    <h2>Vēl nav pieteiktu remontu.</h2>
    <p>Piesaki savu ierīci — šeit varēsi sekot remonta gaitai.</p>
    <a class="button" href="/pieteikt-remontu">Pieteikt remontu</a>
</div>
<?php else: ?>
<div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th>Pieteikums / ierīce</th>
                <th>Klients</th>
                <th>Statuss</th>
                <th>Atjaunots</th>
                <th><span class="sr-only">Darbības</span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($repairs as $repair): ?>
            <tr>
                <td>
                    <a class="table-reference" href="<?= $isStaff ? '/darbnica/remonts?id='.(int)$repair['id'] : '/remonta-statuss?reference='.rawurlencode($repair['reference']) ?>"><?= e($repair['reference']) ?></a
                    ><span class="cell-subtitle"><?= e($repair['device']) ?><?= $repair['photo_path'] ? ' · Foto' : '' ?></span>
                </td>
                <td><?= e($repair['customer_name']) ?></td>
                <td>
                    <span class="status-badge status-<?= e($repair['status']) ?>"><?= e(status_label($repair['status'])) ?></span>
                </td>
                <td class="nowrap"><?= e(pretty_date($repair['updated_at'])) ?></td>
                <td><a href="<?= $isStaff ? '/darbnica/remonts?id='.(int)$repair['id'] : '/remonta-statuss?reference='.rawurlencode($repair['reference']) ?>">Atvērt →</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

