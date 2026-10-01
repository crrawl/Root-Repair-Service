<div class="dashboard-heading">
    <section class="page-heading">
        <p class="eyebrow">DARBNĪCAS PĀRVALDĪBA</p>
        <h1>Viss darbs. Vienā vietā.</h1>
        <p><?= e(current_user()['name']) ?> <span class="role-label">Īpašnieks</span></p>
    </section>
    <div class="heading-actions">
        <a class="button" href="/pieteikt-remontu">Jauns pieteikums</a>
        <form action="/izrakstities" method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>" /><button
                class="button secondary"
                type="submit"
            >
                Izrakstīties
            </button>
        </form>
    </div>
</div>
<?php require __DIR__.'/admin-nav.php'; ?>
<div class="stats-strip" aria-label="Darbnīcas kopsavilkums">
    <?php foreach(['total'=>'Kopā pieteikumi','fresh'=>'Gaida pieņemšanu','active'=>'Darbā','ready'=>'Gatavi saņemšanai'] as $key=>$label): ?>
    <div><strong><?= (int)$stats[$key] ?></strong><span><?= e($label) ?></span></div>
    <?php endforeach; ?>
</div>
<?php if($tab==='dashboard'): ?>
<div class="admin-overview-grid">
    <section>
        <div class="section-title">
            <h2>Jaunākie pieteikumi</h2>
            <a href="/admin.php?page=pieteikumi">Visi pieteikumi →</a>
        </div>
        <?php if (!$repairs): ?>
        <p class="muted">Pieteikumu vēl nav.</p>
        <?php else: ?>
        <div class="table-scroll">
            <table>
                <thead><tr><th>Pieteikums</th><th>Klients</th><th>Statuss</th></tr></thead>
                <tbody>
                    <?php foreach (array_slice($repairs, 0, 5) as $repair): ?>
                    <tr>
                        <td><a href="/admin/remonts?id=<?= (int) $repair['id'] ?>"><?= e($repair['reference']) ?></a><span class="cell-subtitle"><?= e($repair['device']) ?></span></td>
                        <td><?= e($repair['customer_name']) ?></td>
                        <td><span class="status-badge status-<?= e($repair['status']) ?>"><?= e(status_label($repair['status'])) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>
    <section>
        <div class="section-title">
            <h2>Pēdējās darbības</h2>
            <a href="/admin?tab=activity">Visa vēsture →</a>
        </div>
        <?php if (!$activity): ?>
        <p class="muted">Darbību vēl nav.</p>
        <?php else: ?>
        <ul class="activity-preview">
            <?php foreach (array_slice($activity, 0, 5) as $event): ?>
            <li><strong><?= e($event['reference']) ?></strong><span><?= e($event['details']) ?></span><small><?= e($event['actor'] ?: 'Klients') ?> · <?= e(pretty_date($event['created_at'])) ?></small></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </section>
</div>
<?php elseif($tab==='repairs'): ?>
<div class="section-title">
    <h2>Remonta pieteikumi <span class="count-label"><?= $total ?></span></h2>
    <span class="muted">Jaunākie vispirms</span>
</div>
<form class="admin-filter" method="get" action="/admin">
    <input type="hidden" name="tab" value="repairs" />
    <div class="field">
        <label for="q">Meklēt pieteikumu</label
        ><input
            id="q"
            name="q"
            value="<?= e($q) ?>"
            placeholder="Numurs, ierīce, klients vai e-pasts"
            maxlength="160"
        />
    </div>
    <div class="field">
        <label for="filter-status">Statuss</label
        ><select id="filter-status" name="status">
            <option value="">Visi statusi</option>
            <?php foreach(status_options() as $option): ?>
            <option value="<?= $option ?>" <?= $status===$option ? ' selected' : '' ?>><?= e(status_label($option)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="button secondary" type="submit">Atlasīt</button><?php if($q!=='' || $status!==''): ?><a
        class="clear-filter"
        href="/admin?tab=repairs"
        >Notīrīt</a
    ><?php endif; ?>
</form>
<?php if(!$repairs): ?>
<div class="empty-state">
    <h2>Nav atbilstošu pieteikumu.</h2>
    <p>Pamēģini citu meklējumu vai izveido jaunu pieteikumu.</p>
</div>
<?php else: ?>
<div class="table-scroll admin-table">
    <table>
        <thead>
            <tr>
                <th>Pieteikums / ierīce</th>
                <th>Klients</th>
                <th>Statuss</th>
                <th>Atbildīgais</th>
                <th><span class="sr-only">Darbības</span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($repairs as $repair): ?>
            <tr>
                <td>
                    <a class="table-reference" href="/admin/remonts?id=<?= (int)$repair['id'] ?>"
                        ><?= e($repair['reference']) ?></a
                    ><span class="cell-subtitle"><?= e($repair['device']) ?><?= $repair['photo_path'] ? ' · Foto' : '' ?></span>
                </td>
                <td><?= e($repair['customer_name']) ?><span class="cell-subtitle"><?= e($repair['contact_email']) ?></span></td>
                <td>
                    <span class="status-badge status-<?= e($repair['status']) ?>"><?= e(status_label($repair['status'])) ?></span>
                </td>
                <td><?= e($repair['technician'] ?: 'Nav piešķirts') ?></td>
                <td class="row-actions">
                    <a href="/admin/remonts?id=<?= (int)$repair['id'] ?>">Atvērt →</a
                    ><a class="delete-link" href="/admin/dzest?id=<?= (int)$repair['id'] ?>">Dzēst</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<nav class="pagination" aria-label="Pieteikumu lapas">
    <span>Lapa <?= $page ?> no <?= $pages ?></span>
    <div>
        <?php if($page>1): ?><a href="/admin?<?= e(http_build_query(['tab'=>'repairs','q'=>$q,'status'=>$status,'page_no'=>$page-1])) ?>">← Iepriekšējā</a
        ><?php endif; ?><?php if($page<$pages): ?><a href="/admin?<?= e(http_build_query(['tab'=>'repairs','q'=>$q,'status'=>$status,'page_no'=>$page+1])) ?>">Nākamā →</a
        ><?php endif; ?>
    </div>
</nav>
<?php endif; ?> <?php elseif($tab==='team'): ?>
<div class="section-title">
    <h2>Darbnīcas komanda</h2>
    <span class="muted">Piešķir darbus pieteikuma skatā</span>
</div>
<div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th>Darbinieks</th>
                <th>Loma</th>
                <th>Aktīvie darbi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($staff as $person): ?>
            <tr>
                <td>
                    <strong><?= e($person['name']) ?></strong
                    ><span class="cell-subtitle"><?= e($person['email']) ?></span>
                </td>
                <td><?= $person['role']==='owner' ? 'Īpašnieks' : 'Tehniķis' ?></td>
                <td><?= (int)$person['jobs'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="section-title">
    <h2>Darbību vēsture</h2>
    <span class="muted">Pēdējās 50 darbības</span>
</div>
<?php if(!$activity): ?>
<div class="empty-state"><p>Vēl nav reģistrētu darbību.</p></div>
<?php else: ?>
<div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th>Laiks</th>
                <th>Darbība</th>
                <th>Pieteikums</th>
                <th>Darbinieks</th>
                <th>Informācija</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($activity as $event): ?>
            <tr>
                <td class="nowrap"><?= e(date('d.m.Y. H:i',strtotime($event['created_at']))) ?></td>
                <td><?= e(['created'=>'Izveidots','updated'=>'Rediģēts','status_updated'=>'Mainīts statuss','deleted'=>'Dzēsts','diagnostics'=>'XML imports'][$event['action']] ?? $event['action']) ?></td>
                <td><?= e($event['reference']) ?></td>
                <td><?= e($event['actor'] ?: 'Klients') ?></td>
                <td><?= e($event['details']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; endif; ?>
<p class="audit-note">Darbības tiek ierakstītas audita žurnālā.</p>



