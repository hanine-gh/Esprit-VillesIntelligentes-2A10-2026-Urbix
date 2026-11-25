<!-- Main -->
<section id="main">
  <div class="container">
    <div class="row gtr-200">
      <div class="col-12">
        <section class="box">
          <h2>Available louages</h2>
          <?php if ($erreurs): ?>
            <div class="auth-alert auth-alert-error"><ul><?php foreach ($erreurs as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
          <?php endif; ?>
          <?php if (!$erreurs): ?>
            <p><?= e($recherche['departure']) ?> to <?= e($recherche['destination']) ?> — <?= e(date('d/m/Y', strtotime($recherche['date']))) ?> — <?= (int) $recherche['seats'] ?> passenger(s)</p>
          <?php endif; ?>
          <div class="row">
            <?php foreach ($resultats as $h): ?>
            <div class="col-6 col-12-medium">
              <section class="box feature">
                <h3><?= e($h->getTrajet()) ?></h3>
                <p>Departure: <?= e(date('H:i', strtotime($h->getDepartAt()))) ?> | Estimated arrival: <?= $h->getArriveeAt() ? e(date('H:i', strtotime($h->getArriveeAt()))) : '—' ?></p>
                <p>Price: <?= e(prixTnd($h->getPrix())) ?> | Available seats: <?= $h->getPlacesLibres() ?></p>
                <ul class="actions">
                  <li><a href="<?= BASE_URL ?>index.php?page=louage-details&amp;id=<?= $h->getId() ?>" class="button small">View details</a></li>
                  <li><a href="<?= BASE_URL ?>index.php?page=louage-reservation&amp;schedule=<?= $h->getId() ?>" class="button small alt">Reserve</a></li>
                </ul>
              </section>
            </div>
            <?php endforeach; ?>
          </div>
          <?php if (!$erreurs && !$resultats): ?>
            <p>No louage available for this search.</p>
          <?php endif; ?>
          <ul class="actions">
            <li><a href="<?= BASE_URL ?>index.php?page=louage-recherche" class="button alt">New search</a></li>
          </ul>
        </section>
      </div>
    </div>
  </div>
</section>
