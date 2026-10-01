<section class="page-heading">
    <p class="eyebrow">DARBNĪCAS PAKALPOJUMI</p>
    <h1>Salabosim to, kas vēl var kalpot.</h1>
    <p>Vispirms noskaidrojam bojājumu. Remontu sākam tikai pēc izmaksu saskaņošanas.</p>
</section>
<div class="services-detail">
    <?php foreach ([ ['datori','01','Datori un portatīvie','No skaļa ventilatora līdz datoram, kas vairs
    neieslēdzas.','Diagnostika un barošanas remonts|Dzesēšanas sistēmas tīrīšana|SSD, atmiņas un citu detaļu maiņa'],
    ['telefoni','02','Telefoni un planšetes','Palīdzam, ja ierīce neuzlādējas, ekrāns ir saplaisājis vai akumulators
    vairs netur.','Ekrānu un akumulatoru maiņa|Uzlādes ligzdu remonts|Bojājumu diagnostika pēc saskares ar mitrumu'],
    ['konsoles','03','Spēļu konsoles','Atgriežam spēles uz ekrāna un klusumu istabā.','Dzesēšanas sistēmas apkope|HDMI
    ligzdas un attēla problēmas|Barošanas bloka diagnostika'], ['tikls','04','Tīkla iekārtas','Pārbaudām maršrutētājus,
    komutatorus un bezvadu piekļuves punktus.','Barošanas un savienojuma diagnostika|Programmaparatūras
    atjaunošana|Tīkla konfigurācijas pārbaude'] ] as [$id,$number,$name,$description,$items]): ?>

    <section class="service-detail" id="<?= $id ?>">
        <span class="row-number"><?= $number ?></span>
        <div class="row-descr">
            <h2><?= e($name) ?></h2>
            <p><?= e($description) ?></p>
        </div>
        <ul class="plain-list">
            <?php foreach(explode('|',$items) as $item): ?>
            <li><?= e($item) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endforeach; ?>
</div>
<div class="bottom-action">
    <div>
        <h2>Nezini, kas tieši salūzis?</h2>
        <p>Apraksti simptomus — sāksim ar diagnostiku.</p>
    </div>
    <a class="button" href="/pieteikt-remontu">Pieteikt remontu</a>
</div>
