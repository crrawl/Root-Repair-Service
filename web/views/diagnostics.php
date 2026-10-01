<section class="page-heading">
    <p class="eyebrow">DARBNĪCAS RĪKI</p>
    <h1>Diagnostikas imports</h1>
    <p>Ielādē diagnostikas rīka XML atskaiti un piesaisti rezultātus remonta pieteikumam.</p>
</section>
<?php require __DIR__.'/admin-nav.php'; ?>
<div class="diagnostic-layout">
    <section class="import-panel">
        <h2>Jauna atskaite</h2>
        <?php if($error): ?>
        <div class="notice error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form action="/diagnostika" method="post" enctype="multipart/form-data" data-submit>
            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>" />
            <div class="field">
                <label for="diagnostic">Diagnostikas XML fails</label
                ><input
                    id="diagnostic"
                    type="file"
                    name="diagnostic"
                    accept=".xml,application/xml,text/xml"
                    required
                    data-file-label="xml-file-name"
                /><small id="xml-file-name" class="muted">XML formāts · līdz 1 MB</small>
            </div>
            <button class="button" type="submit">Importēt atskaiti</button>
        </form>
    </section>
    <aside class="import-help">
        <h2>Kā sagatavot failu</h2>
        <p class="muted">
            Norādi esoša pieteikuma numuru, ierīces modeli, sērijas numuru un diagnostikas
            rezultātu.
        </p><pre>
&lt;diagnostics&gt;
  &lt;reference&gt;RR-1042&lt;/reference&gt;
  &lt;model&gt;ThinkPad T480&lt;/model&gt;
  &lt;serial&gt;RR-DEMO-001&lt;/serial&gt;
  &lt;result&gt;Akumulators jānomaina.&lt;/result&gt;
&lt;/diagnostics&gt;</pre>
    </aside>
</div>
<?php if($imported): ?>
<section class="import-result">
    <p class="eyebrow">IMPORTĒTĀ ATSKAITE · <?= e($imported['reference']) ?></p>
    <h2><?= e($imported['model']) ?></h2>
    <p class="muted">Sērijas numurs: <?= e($imported['serial'] ?: 'Nav norādīts') ?></p>
    <pre><?= e($imported['result']) ?></pre>
    <a href="<?= current_user()['role']==='owner' ? '/admin/remonts' : '/darbnica/remonts' ?>?id=<?= (int)$imported['repair_id'] ?>">Atvērt pieteikumu →</a>
</section>
<?php endif; ?>
<section class="report-section">
    <div class="section-title">
        <h2>Pēdējie importi</h2>
        <span class="muted">Līdz 20 atskaitēm</span>
    </div>
    <?php if(!$reports): ?>
    <div class="empty-state"><p>Vēl nav importētu diagnostikas atskaišu.</p></div>
    <?php else: ?>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Pieteikums</th>
                    <th>Ierīce</th>
                    <th>Importēja</th>
                    <th>Datums</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reports as $report): ?>
                <tr>
                    <td><a href="/diagnostika?report=<?= (int)$report['id'] ?>"><?= e($report['reference']) ?></a></td>
                    <td><?= e($report['model']) ?></td>
                    <td><?= e($report['importer']) ?></td>
                    <td><?= e(pretty_date($report['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
