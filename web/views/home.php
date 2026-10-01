<section class="hero">
    <div class="hero-copy">
        <h1>Datoru un elektronikas remonts.</h1>
        <p class="hero-lead">Atrodam bojājumu. Izskaidrojam risinājumu. Salabojam.</p>
        <a class="button" href="/pieteikt-remontu">Pieteikt remontu</a>
        <p class="hero-types">Datori · Telefoni · Konsoles · Tīkla iekārtas</p>
    </div>
    <figure class="hero-photo">
        <img
            src="/assets/workshop.png"
            width="1536"
            height="1024"
            alt="Meistars ar precīzo skrūvgriezi remontē portatīvo datoru darbnīcā"
            fetchpriority="high"
        />
    </figure>
</section>
<form class="status-strip" action="/remonta-statuss" method="get">
    <label for="home-reference">Jau nodevi ierīci remontā?</label>
    <input
        id="home-reference"
        name="reference"
        placeholder="Pieteikuma numurs, piem., RR-1042"
        required
        maxlength="250"
        autocomplete="off"
        spellcheck="false"
    />
    <button class="button" type="submit">Pārbaudīt statusu</button>
</form>
<div class="home-bottom">
    <section>
        <h2>Ko remontējam</h2>
        <div class="service-list">
            <a href="/pakalpojumi#datori"
                ><strong>Datori un portatīvie</strong
                ><span>Diagnostika, tīrīšana, detaļu maiņa</span></a
            >
            <a href="/pakalpojumi#telefoni"
                ><strong>Telefoni un planšetes</strong
                ><span>Ekrāni, akumulatori, uzlādes ligzdas</span></a
            >
            <a href="/pakalpojumi#konsoles"
                ><strong>Spēļu konsoles</strong><span>Dzesēšana, HDMI, barošanas problēmas</span></a
            >
            <a href="/pakalpojumi#tikls"
                ><strong>Tīkla iekārtas</strong
                ><span>Maršrutētāji un savienojuma problēmas</span></a
            >
        </div>
    </section>
    <aside class="workshop">
        <h2>Darbnīca</h2>
        <p>P.–Pk. 09.00–18.00<br />Sestdien, svētdien — slēgts</p>
        <a href="mailto:juris.kalnins@rootrepair.lv">juris.kalnins@rootrepair.lv</a>
        <p class="muted">Pirms ierašanās piesaki remontu.</p>
    </aside>
</div>
