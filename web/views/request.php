<section class="page-heading">
    <p class="eyebrow">JAUNS PIETEIKUMS</p>
    <h1>Kas noticis ar ierīci?</h1>
    <p>Aizpildi pieteikumu un saglabā tā numuru. Pēc tam nogādā ierīci darbnīcā.</p>
</section>
<div class="request-layout">
    <form
        method="post"
        action="/pieteikt-remontu"
        enctype="multipart/form-data"
        class="repair-form"
        data-submit
    >
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>" />
        <?php if ($errors): ?>
        <div class="notice error" role="alert">
            <ul>
                <?php foreach($errors as $error): ?>
                <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <h2>Kontaktinformācija</h2>
        <div class="form-grid">
            <div class="field">
                <label for="name">Vārds, uzvārds</label
                ><input
                    id="name"
                    name="name"
                    value="<?= e($values['name']) ?>"
                    required
                    maxlength="120"
                    autocomplete="name"
                />
            </div>
            <div class="field">
                <label for="email">E-pasta adrese</label
                ><input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= e($values['email']) ?>"
                    required
                    maxlength="190"
                    autocomplete="email"
                />
            </div>
        </div>
        <h2>Ierīce un bojājums</h2>
        <div class="form-grid">
            <div class="field">
                <label for="type">Ierīces veids</label
                ><select id="type" name="type" required>
                    <option value="">Izvēlies ierīci</option>
                    <?php foreach(['Portatīvais dators','Galda dators','Telefons','Planšete','Spēļu konsole','Tīkla iekārta'] as $type): ?>
                    <option <?= $values['type']===$type ? ' selected' : '' ?>><?= e($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="model">Ražotājs un modelis</label
                ><input
                    id="model"
                    name="model"
                    value="<?= e($values['model']) ?>"
                    placeholder="Piemēram, Lenovo ThinkPad T480"
                    required
                    maxlength="160"
                />
            </div>
        </div>
        <div class="field">
            <label for="description">Bojājuma apraksts</label
            ><textarea
                id="description"
                name="description"
                rows="5"
                minlength="10"
                maxlength="4000"
                required
                placeholder="Kas nedarbojas? Kad problēma sākās?"
            >
<?= e($values['description']) ?></textarea>
        </div>
        <div class="field photo-upload">
            <label for="photo"
                >Defekta fotoattēli <span class="optional-label">obligāti</span></label
            >
            <div class="upload-box">
                <span class="upload-symbol" aria-hidden="true">＋</span>
                <p>Parādi, kas ir bojāts.</p>
                <input
                    id="photo"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    name="photo[]"
                    required
                    multiple
                    data-file-label="photo-file-name"
                    data-preview="photo-preview"
                /><small id="photo-file-name" class="muted"
                    >1–5 faili · JPG, PNG vai WebP attēli · līdz 5 MB katrs</small
                ><img
                    id="photo-preview"
                    class="upload-preview"
                    hidden
                    alt="Izvēlētā attēla priekšskatījums"
                />
            </div>
        </div>
        <button class="button" type="submit">Nosūtīt pieteikumu</button>
        <p class="form-note">
            Ja šim e-pastam vēl nav konta, izveidosim to un nākamajā lapā vienreiz parādīsim nejauši
            ģenerētu paroli. Esošā konta parole nemainīsies.
        </p>
    </form>
    <aside class="request-aside">
        <span class="eyebrow">KĀ TAS NOTIEK</span>
        <ol class="numbered-list">
            <li>Aizpildi pieteikumu un pievieno 1–5 defekta fotoattēlus.</li>
            <li>Saglabā pieteikuma numuru un jaunā konta paroli.</li>
            <li>Nogādā ierīci darbnīcā.</li>
            <li>Saņem diagnostiku un izmaksu piedāvājumu.</li>
            <li>Pēc remonta saņem ierīci.</li>
        </ol>
        <a href="/instrukcijas">Sagatavo ierīci remontam →</a>
    </aside>
</div>

