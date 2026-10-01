<section class="page-heading">
    <p class="eyebrow">DARBNĪCAS PĀRVALDĪBA</p>
    <h1>LogFile</h1>
    <p>Apache piekļuves žurnāls — access.log</p>
</section>
<?php require __DIR__.'/admin-nav.php'; ?>
<p><a class="button secondary" href="/admin.php?page=logfile">Atjaunot žurnālu</a></p>
<pre
    style="
        max-height: 65vh;
        max-width: 100%;
        overflow: auto;
        white-space: pre;
        font-size: 13px;
        padding: 20px;
        background: #171719;
        border: 1px solid #353538;
        border-radius: 8px;
    "
>
<?= e($logOutput) ?></pre>
