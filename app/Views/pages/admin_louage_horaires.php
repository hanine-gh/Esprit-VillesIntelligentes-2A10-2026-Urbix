<?php $edition = $form['id'] > 0; ?>
      <div class="page-heading">
        <h3>Manage schedules and ETA</h3>
        <p class="text-subtitle text-muted">Set planned departure and estimated arrival times.</p>
      </div>
      <div class="page-content">
        <section class="section">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4><?= $edition ? 'Edit a schedule' : 'Add a schedule' ?></h4>
                </div>
                <div class="card-body">
                  <?php require __DIR__ . '/../admin/partials/messages.php'; ?>
                  <form action="<?= BASE_URL ?>index.php?page=admin-louage-horaires" method="post" data-validate="modules" novalidate>
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
                    <div class="row">
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="route" class="form-label">Route</label>
                          <select class="form-select" id="route" name="route" data-v-required="Please choose a route.">
                            <option value="">Select a route</option>
                            <?php foreach ($routes as $r): ?>
                            <option value="<?= $r->getId() ?>"<?= selected($form['route'], $r->getId()) ?>><?= e($r->getTrajet()) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="louage" class="form-label">Louage registration</label>
                          <select class="form-select" id="louage" name="louage">
                            <option value="">Not assigned yet</option>
                            <?php foreach ($vehicules as $v): ?>
                            <option value="<?= $v->getId() ?>"<?= selected($form['louage'], $v->getId()) ?>><?= e($v->getImmatriculation() . ' — ' . $v->getModele()) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="departure" class="form-label">Departure time</label>
                          <input type="datetime-local" class="form-control" id="departure" name="departure_time" data-v-required="Departure time is required." value="<?= e($form['departure_time']) ?>">
                        </div>
                      </div>
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="arrival" class="form-label">Estimated arrival</label>
                          <input type="datetime-local" class="form-control" id="arrival" name="arrival_time" data-v-after="departure_time" data-v-after-msg="Must be after the departure time." value="<?= e($form['arrival_time']) ?>">
                        </div>
                      </div>
                    </div>
                    <div class="mt-3">
                      <button type="submit" class="btn btn-primary"><?= $edition ? 'Save changes' : 'Save schedule' ?></button>
                      <?php if ($edition): ?><a href="<?= BASE_URL ?>index.php?page=admin-louage-horaires" class="btn btn-light-secondary">Cancel</a><?php endif; ?>
                    </div>
                  </form>
                  <p class="text-muted mt-3 mb-0">Live ETA requires GPS coordinates and route-duration calculations.</p>
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
                  <h4>Schedule list</h4>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr><th>Route</th><th>Louage</th><th>Departure</th><th>Estimated arrival</th><th>Seats booked</th><th>Actions</th></tr>
                      </thead>
                      <tbody>
                        <?php foreach ($horaires as $h): ?>
                        <tr>
                          <td><?= e($h->getTrajet()) ?></td>
                          <td><?= $h->getImmatriculation() !== null ? e($h->getImmatriculation()) : '—' ?></td>
                          <td><?= e(dateHeure($h->getDepartAt())) ?></td>
                          <td><?= e(dateHeure($h->getArriveeAt())) ?></td>
                          <td><?= $h->getPlacesReservees() ?> / <?= $h->getCapacite() ?></td>
                          <td>
                            <a href="<?= BASE_URL ?>index.php?page=admin-louage-horaires&amp;edit=<?= $h->getId() ?>" class="btn btn-sm btn-success">Edit</a>
                            <form action="<?= BASE_URL ?>index.php?page=admin-louage-horaire-delete" method="post" class="d-inline" data-confirm="Delete this schedule and its reservations?">
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                              <input type="hidden" name="id" value="<?= $h->getId() ?>">
                              <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$horaires): ?><tr><td colspan="6">No schedule yet.</td></tr><?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
