<!-- Banner -->
<section id="banner">
    <div class="content">
        <h2>Bienvenue sur Urbix</h2>
        <p>La mobilité intelligente de votre ville, au même endroit</p>
        <a href="<?= url('covoiturage') ?>" class="button">Trouver un covoiturage</a>
        <a href="#main" class="button scrolly">Découvrir les services</a>
    </div>
</section>

<!-- Main -->
<section id="main">
    <div class="container">
        <div class="row gtr-200">
            <div class="col-12">

                <!-- Highlight -->
                <section class="box highlight">
                    <ul class="special">
                        <li><a href="<?= url('covoiturage') ?>" class="icon solid fa-search-location"><span class="label">Rechercher</span></a></li>
                        <li><a href="<?= url('module', ['nom' => 'bus']) ?>" class="icon solid fa-bus"><span class="label">Bus</span></a></li>
                        <li><a href="<?= url('module', ['nom' => 'train']) ?>" class="icon solid fa-train"><span class="label">Train</span></a></li>
                        <li><a href="<?= url('module', ['nom' => 'reparation']) ?>" class="icon solid fa-tools"><span class="label">Réparation</span></a></li>
                    </ul>
                    <header>
                        <h2>Se déplacer plus simplement</h2>
                        <p>Suivi en temps réel, horaires, réservation et covoiturage intelligent</p>
                    </header>
                    <p>
                        Urbix utilise la géolocalisation et des recommandations intelligentes pour vous proposer
                        le meilleur moyen de transport : bus, métro, train, louage, location ou covoiturage.
                    </p>
                </section>

            </div>
            <div class="col-12">

                <!-- Features -->
                <section class="box features">
                    <h2 class="major"><span>Nos services</span></h2>
                    <div>
                        <div class="row">
                            <div class="col-3 col-6-medium col-12-small">
                                <section class="box feature">
                                    <a href="<?= url('covoiturage') ?>" class="image featured"><img src="<?= ASSETS_URL ?>images/pic01.jpg" alt="Covoiturage" /></a>
                                    <h3><a href="<?= url('covoiturage') ?>">Covoiturage</a></h3>
                                    <p>Saisissez votre destination, comparez les conducteurs (prix, date, note) et acceptez le trajet qui vous convient.</p>
                                </section>
                            </div>
                            <div class="col-3 col-6-medium col-12-small">
                                <section class="box feature">
                                    <a href="<?= url('module', ['nom' => 'bus']) ?>" class="image featured"><img src="<?= ASSETS_URL ?>images/pic02.jpg" alt="Bus" /></a>
                                    <h3><a href="<?= url('module', ['nom' => 'bus']) ?>">Bus urbain &amp; à la demande</a></h3>
                                    <p>Lignes, horaires, temps d'arrivée estimé et prise en charge à domicile.</p>
                                </section>
                            </div>
                            <div class="col-3 col-6-medium col-12-small">
                                <section class="box feature">
                                    <a href="<?= url('module', ['nom' => 'train']) ?>" class="image featured"><img src="<?= ASSETS_URL ?>images/pic03.jpg" alt="Train" /></a>
                                    <h3><a href="<?= url('module', ['nom' => 'train']) ?>">Métro &amp; Train</a></h3>
                                    <p>Gares, horaires et billets du métro et des trains de la ville.</p>
                                </section>
                            </div>
                            <div class="col-3 col-6-medium col-12-small">
                                <section class="box feature">
                                    <a href="<?= url('module', ['nom' => 'reparation']) ?>" class="image featured"><img src="<?= ASSETS_URL ?>images/pic04.jpg" alt="Réparation" /></a>
                                    <h3><a href="<?= url('module', ['nom' => 'reparation']) ?>">Location &amp; Réparation</a></h3>
                                    <p>Louez un véhicule ou trouvez un garage pour réparer le vôtre.</p>
                                </section>
                            </div>
                            <div class="col-12">
                                <ul class="actions">
                                    <li><a href="<?= url('covoiturage') ?>" class="button large">Rechercher un trajet</a></li>
                                    <li><a href="<?= url('module', ['nom' => 'louage']) ?>" class="button alt large">Voir les louages</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>
</section>
