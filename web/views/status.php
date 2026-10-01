<!-- "/**\\**&#x62;(SELECT|UNION|OR|AND|DROP|UPDATE|DELETE|INSERT)**\\**&#x62;|;|--|#/", -->
<?php if ($receipt): ?>
<section class="notice" aria-label="Pieteikuma apstiprinājums">
    <h2><?= $receipt["password"] !== null ? "Pieteikums un klienta konts izveidots" : "Pieteikums saņemts" ?></h2>
    <p>Pieteikuma numurs: <strong><?= e($receipt["reference"]) ?></strong></p>
    <?php if ($receipt["password"] !== null): ?>
    <p>Saglabā piekļuves datus. Parole redzama tikai šajā reizē un netiek nosūtīta e-pastā.</p>
    <div class="field">
        <label for="created-email">E-pasts</label>
        <input id="created-email" value="<?= e($receipt["email"]) ?>" readonly autocomplete="off" />
    </div>
    <div class="field">
        <label for="created-password">Ģenerētā parole</label>
        <input
            id="created-password"
            value="<?= e($receipt["password"]) ?>"
            readonly
            autocomplete="off"
            spellcheck="false"
        />
    </div>
    <?php else: ?>
    <p>Norādītajam e-pastam konts jau pastāv. Piesakies ar savu līdzšinējo paroli.</p>
    <?php endif; ?>
    <a class="button" href="/pieteikties">Pieteikties kontā</a>
</section>
<?php endif; ?>
<section class="page-heading">
    <p class="eyebrow">SEKO REMONTA GAITAI</p>
    <h1>Remonta statuss</h1>
    <p>Ievadi numuru, ko saņēmi, piesakot remontu. Te redzēsi jaunāko darbnīcas informāciju.</p>
</section>
<form class="search-form" method="get" action="/remonta-statuss">
    <div class="field">
        <label for="reference">Pieteikuma numurs</label
        ><input
            id="reference"
            name="reference"
            value="<?= e($reference) ?>"
            placeholder="Piemēram, RR-1042"
            required
            maxlength="250"
            autocomplete="off"
            spellcheck="false"
        />
    </div>
    <button class="button" type="submit">Pārbaudīt statusu</button>
</form>
<?php if ($error): ?>
<div class="notice error" role="alert"><?= e($error) ?></div>
<?php elseif ($reference !== '' && !$results): ?>
<div class="empty-state">
    <h2>Pieteikums nav atrasts.</h2>
    <p>
        Pārbaudi numuru un mēģini vēlreiz. Ja nepieciešama palīdzība,
        <a href="/kontakti">sazinies ar darbnīcu</a>.
    </p>
</div>
<?php elseif ($results): foreach ($results as $repair): ?>
<article class="repair-result">
    <div class="result-top">
        <div>
            <p class="eyebrow"><?= e($repair['reference']) ?></p>
            <h2><?= e($repair['device']) ?></h2>
        </div>
        <span class="status-badge"><?= e(status_label($repair['status'])) ?></span>
    </div>
    <?php $steps=['submitted','received','diagnostics','repairing','ready','collected']; $position=array_search($repair['status'],$steps,true); if($position!==false): ?>
    <ol class="progress-steps" aria-label="Remonta gaita">
        <?php foreach($steps as $i=>$step): ?>
        <li class="<?= $i<=$position ? 'complete' : '' ?>" <?= $i===$position ? ' aria-current="step"' : '' ?>>
            <span><?= $i+1 ?></span><?= e(status_label($step)) ?>
        </li>
        <?php endforeach; ?>
    </ol>
    <?php endif; ?>
    <div class="result-note">
        <h3>Tehniķa piezīme</h3>
        <p><?= e($repair['summary']) ?></p>
        <small>Atjaunots: <?= e(pretty_date($repair['updated_at'])) ?></small>
    </div>
</article>
<?php endforeach; else: ?>
<p class="muted helper">
    Pieteikuma numurs sākas ar RR-. Tas redzams remonta pieteikuma apstiprinājumā.
</p>
<?php endif; ?>
