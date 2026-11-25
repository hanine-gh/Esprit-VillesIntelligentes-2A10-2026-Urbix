<!-- Main -->
<section id="main">
  <div class="container">
    <div class="row gtr-200">
      <div class="col-12">
        <section class="box">
          <h2>Track your louage</h2>
          <p>View the vehicle's current position when GPS tracking is available.</p>
          <?php if ($horaire === null): ?>
            <p>You have no upcoming trip. <a href="<?= BASE_URL ?>index.php?page=louage-recherche">Search for a louage</a>.</p>
          <?php else: ?>
          <ul class="divided">
            <li>Trip: <?= e($horaire->getTrajet()) ?> — <?= e(dateHeure($horaire->getDepartAt())) ?></li>
            <li>Vehicle: <?= $horaire->getModele() ? e($horaire->getModele() . ' (' . $horaire->getImmatriculation() . ')') : 'Not assigned yet' ?></li>
            <li>Current position: GPS location not connected yet.</li>
            <li>Estimated arrival time: <?= e(dateHeure($horaire->getArriveeAt())) ?></li>
          </ul>
          <?php endif; ?>
          <p>A map can be embedded here using a mapping provider.</p>
        </section>
      </div>
    </div>
  </div>
</section>
