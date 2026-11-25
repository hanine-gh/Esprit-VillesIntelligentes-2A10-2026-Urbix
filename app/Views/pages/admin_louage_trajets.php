<?php $edition = $form['id'] > 0; ?>
      <div class="page-heading">
        <h3>Manage routes</h3>
        <p class="text-subtitle text-muted">Define departure and arrival stations for louage routes.</p>
      </div>
      <div class="page-content">
        <section class="section">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4><?= $edition ? 'Edit a route' : 'Add a route' ?></h4>
                </div>
                <div class="card-body">
                  <?php require __DIR__ . '/../admin/partials/messages.php'; ?>
                  <form action="<?= BASE_URL ?>index.php?page=admin-louage-trajets" method="post" data-validate="modules" novalidate>
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
                    <div class="row">
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="departure" class="form-label">Departure station</label>
                          <select class="form-select" id="departure" name="departure" data-v-required="Please choose the departure station.">
                            <option value="">Select a station</option>
                            <?php foreach ($stations as $s): ?>
                            <option value="<?= $s->getId() ?>"<?= selected($form['departure'], $s->getId()) ?>><?= e($s->getVille() . ' — ' . $s->getNom()) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="arrival" class="form-label">Arrival station</label>
                          <select class="form-select" id="arrival" name="arrival" data-v-required="Please choose the arrival station." data-v-different="departure" data-v-different-msg="Must be different from the departure station.">
                            <option value="">Select a station</option>
                            <?php foreach ($stations as $s): ?>
                            <option value="<?= $s->getId() ?>"<?= selected($form['arrival'], $s->getId()) ?>><?= e($s->getVille() . ' — ' . $s->getNom()) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="mt-3">
                      <button type="submit" class="btn btn-primary"><?= $edition ? 'Save changes' : 'Add route' ?></button>
                      <?php if ($edition): ?><a href="<?= BASE_URL ?>index.php?page=admin-louage-trajets" class="btn btn-light-secondary">Cancel</a><?php endif; ?>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="section">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4>Route list</h4>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr><th>Departure</th><th>Arrival</th><th>Actions</th></tr>
                      </thead>
                      <tbody>
                        <?php foreach ($routes as $r): ?>
                        <tr>
                          <td><?= e($r->getDepartVille()) ?> <small class="text-muted">(<?= e($r->getDepartNom()) ?>)</small></td>
                          <td><?= e($r->getArriveeVille()) ?> <small class="text-muted">(<?= e($r->getArriveeNom()) ?>)</small></td>
                          <td>
                            <a href="<?= BASE_URL ?>index.php?page=admin-louage-trajets&amp;edit=<?= $r->getId() ?>" class="btn btn-sm btn-success">Edit</a>
                            <form action="<?= BASE_URL ?>index.php?page=admin-louage-trajet-delete" method="post" class="d-inline" data-confirm="Delete this route?">
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                              <input type="hidden" name="id" value="<?= $r->getId() ?>">
                              <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$routes): ?><tr><td colspan="3">No route yet.</td></tr><?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
