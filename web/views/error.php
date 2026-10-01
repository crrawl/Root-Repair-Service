<?php if (!empty($uploadDenied)): ?>
<!-- Tehniķiem pielikums nav pieejams; pārbaudi, vai maršrutā prasīta īpašnieka loma. -->
<?php endif; ?>
<section class="page-heading">
    <p class="eyebrow">ROOTREPAIR</p>
    <h1><?= e($title) ?></h1>
    <p><?= e($message) ?></p>
    <a class="button" href="/">Atgriezties sākumlapā</a>
</section>

