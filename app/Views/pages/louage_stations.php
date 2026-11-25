<!-- Main -->
<section id="main">
  <div class="container">
    <div class="row gtr-200">
      <div class="col-12">
        <section class="box">
          <h2>Louage stations</h2>
          <p>Find station names and location information.</p>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr><th>Station</th><th>City</th><th>Address / Location</th></tr>
              </thead>
              <tbody>
                <?php foreach ($stations as $s): ?>
                <tr>
                  <td><?= e($s->getNom()) ?></td>
                  <td><?= e($s->getVille()) ?></td>
                  <td>
                    <?= $s->getAdresse() !== null ? e($s->getAdresse()) : 'Address to be completed' ?>
                    <?php if ($s->getLatitude() !== null && $s->getLongitude() !== null): ?>
                      (<?= e($s->getLatitude() . ', ' . $s->getLongitude()) ?>)
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <p>GPS coordinates and an interactive map can be added when a map service is connected.</p>
        </section>
      </div>
    </div>
  </div>
</section>
