<?php $isOwner=current_user()['role']==='owner'; ?>
<div class="detail-heading">
    <a class="back-link" href="<?= $isOwner ? '/admin' : '/mani-remonti' ?>">← Atpakaļ uz pieteikumiem</a>
    <div class="result-top">
        <div>
            <p class="eyebrow"><?= e($repair['reference']) ?></p>
            <h1><?= e($repair['device']) ?></h1>
            <p class="muted">Pieteikts <?= e(pretty_date($repair['created_at'])) ?> · <?= e($repair['device_type']) ?></p>
        </div>
        <span class="status-badge status-<?= e($repair['status']) ?>"><?= e(status_label($repair['status'])) ?></span>
    </div>
</div>
<div class="repair-detail-grid">
    <section>
        <h2>Pieteikuma informācija</h2>
        <?php if($errors): ?>
        <div class="notice error" role="alert">
            <ul>
                <?php foreach($errors as $error): ?>
                <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <form action="<?= $isOwner ? '/admin/saglabat' : '/darbnica/saglabat' ?>" method="post" data-submit>
            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>" /><input
                type="hidden"
                name="id"
                value="<?= (int)$repair['id'] ?>"
            />
            <?php if($isOwner): ?>
            <div class="form-grid">
                <div class="field">
                    <label for="customer_name">Klienta vārds</label
                    ><input
                        id="customer_name"
                        name="customer_name"
                        value="<?= e($repair['customer_name']) ?>"
                        required
                        maxlength="120"
                    />
                </div>
                <div class="field">
                    <label for="contact_email">E-pasts</label
                    ><input
                        id="contact_email"
                        name="contact_email"
                        type="email"
                        value="<?= e($repair['contact_email']) ?>"
                        required
                        maxlength="190"
                    />
                </div>
            </div>
            <div class="field">
                <label for="device">Ierīce</label
                ><input
                    id="device"
                    name="device"
                    value="<?= e($repair['device']) ?>"
                    required
                    maxlength="160"
                />
            </div>
            <div class="field">
                <label for="description">Bojājuma apraksts</label
                ><textarea
                    id="description"
                    name="description"
                    required
                    minlength="10"
                    maxlength="4000"
                    rows="3"
                >
<?= e($repair['description']) ?></textarea>
            </div>
            <?php else: ?>
            <p>
                <strong><?= e($repair['customer_name']) ?></strong> ·
                <a href="mailto:<?= e($repair['contact_email']) ?>"><?= e($repair['contact_email']) ?></a>
            </p>
            <p class="preserve-lines"><?= e($repair['description']) ?></p>
            <?php endif; ?>
            <div class="form-grid">
                <div class="field">
                    <label for="status">Remonta statuss</label
                    ><select id="status" name="status">
                        <?php foreach(status_options() as $option): ?>
                        <option value="<?= $option ?>" <?= $repair['status']===$option ? ' selected' : '' ?>>
                            <?= e(status_label($option)) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if($isOwner): ?>
                <div class="field">
                    <label for="assigned_to">Atbildīgais tehniķis</label
                    ><select id="assigned_to" name="assigned_to">
                        <option value="">Nav piešķirts</option>
                        <?php foreach($staff as $person): ?>
                        <option value="<?= (int)$person['id'] ?>" <?= (int)$repair['assigned_to']===(int)$person['id'] ? ' selected' : '' ?>>
                            <?= e($person['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <div class="field">
                <label for="summary">Tehniķa piezīme klientam</label
                ><textarea id="summary" name="summary" rows="4" maxlength="2000">
<?= e($repair['summary']) ?></textarea
                ><small class="muted">Šī piezīme būs redzama remonta statusa lapā.</small>
            </div>
            <?php if (!$isOwner): ?>
            <div class="field internal-note-editor">
                <label for="internal_note">Tehniķa iekšējā piezīme</label>
                <textarea id="internal_note" name="internal_note" rows="3" maxlength="2000"><?= e($repair["internal_note"] ?? "") ?></textarea>
                <small class="muted">Šo piezīmi redzēs īpašnieks pieteikuma skatā.</small>
            </div>
            <?php endif; ?>
            <button class="button" type="submit">Saglabāt izmaiņas</button>
        </form>
        <?php if ($isOwner && !empty($repair["internal_note"])): ?>
        <section class="internal-note-view">
            <p class="eyebrow">IEKŠĒJĀ PIEZĪME</p>
            <div><?= $repair["internal_note"] ?></div>
        </section>
        <?php endif; ?>
    </section>
    <aside class="detail-aside">
        <h2>Pieteikuma pielikumi</h2>
        <?php $attachments = repair_photos($repair); ?> <?php foreach ($attachments as $attachment): ?> <?php $photoUrl = $isOwner ? $attachment["path"] : "/darbnica/pielikums?id=" . (int) $repair["id"] . "&file=" . rawurlencode($attachment["path"]); ?><?php if (photo_is_image($attachment["path"])): ?>
        <a href="<?= e($photoUrl) ?>" target="_blank" rel="noopener">
            <img class="repair-photo" src="<?= e($photoUrl) ?>" alt="Defekta fotogrāfija" />
        </a>
        <?php endif; ?>
        <a class="attachment-link" href="<?= e($photoUrl) ?>" target="_blank" rel="noopener"
            ><?= e($attachment["name"]) ?> ↗</a
        >
        <?php endforeach; ?> <?php if (!$attachments): ?>
        <div class="photo-empty">Pielikumu nav</div>
        <?php endif; ?><?php if (!$isOwner): ?>
        <div class="aside-section">
            <h3>Diagnostika</h3>
            <p class="muted">Pievieno ierīces diagnostikas atskaiti XML formātā.</p>
            <a href="/diagnostika">Importēt atskaiti →</a>
        </div><?php endif; ?>
        <?php if($isOwner): ?>
        <div class="aside-section danger-section">
            <h3>Pieteikuma dzēšana</h3>
            <p class="muted">Dzēš arī foto un diagnostikas atskaites.</p>
            <a class="button danger-outline" href="/admin/dzest?id=<?= (int)$repair['id'] ?>"
                >Dzēst pieteikumu</a
            >
        </div>
        <?php endif; ?>
    </aside>
</div>
<?php if (!$isOwner): ?><section class="report-section">
    <div class="section-title">
        <h2>Diagnostikas atskaites <span class="count-label"><?= count($reports) ?></span></h2>
    </div>
    <?php if(!$reports): ?>
    <p class="muted">Šim pieteikumam vēl nav importētu atskaišu.</p>
    <?php else: foreach($reports as $report): ?>
    <details class="diagnostic-report">
        <summary><?= e($report['model']) ?> <span><?= e(pretty_date($report['created_at'])) ?></span></summary>
        <div>
            <p class="muted">Sērijas numurs: <?= e($report['serial'] ?: 'Nav norādīts') ?> · <?= e($report['importer']) ?></p>
            <pre><?= e($report['result']) ?></pre>
        </div>
    </details>
    <?php endforeach; endif; ?>
</section><?php endif; ?>


