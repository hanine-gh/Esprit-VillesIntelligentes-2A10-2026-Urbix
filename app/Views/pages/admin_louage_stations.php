<?php $edition = $form['id'] > 0; ?>
      <div class="page-heading">
        <h3>Manage stations</h3>
        <p class="text-subtitle text-muted">Add and update louage station information.</p>
      </div>
      <div class="page-content">
        <section class="section">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4><?= $edition ? 'Edit a station' : 'Add a station' ?></h4>
                </div>
                <div class="card-body">
                  <?php require __DIR__ . '/../admin/partials/messages.php'; ?>
                  <form action="<?= BASE_URL ?>index.php?page=admin-louage-stations" method="post" data-validate="modules" novalidate>
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
                    <div class="row">
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="name" class="form-label">Station name</label>
                          <input type="text" class="form-control" id="name" name="name" data-v-required="Station name is required." data-v-maxlength="80" data-v-msg="80 characters max." value="<?= e($form['name']) ?>">
                        </div>
                      </div>
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="city" class="form-label">City</label>
                          <input type="text" class="form-control" id="city" name="city" data-v-required="City is required." data-v-pattern="^\p{L}[\p{L} '.-]{1,59}$" data-v-msg="City: letters only." value="<?= e($form['city']) ?>">
                        </div>
                      </div>
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="address" class="form-label">Address</label>
                          <input type="text" class="form-control" id="address" name="address" data-v-maxlength="150" data-v-msg="150 characters max." value="<?= e($form['address']) ?>">
                        </div>
                      </div>
                      <div class="col-md-3 col-12">
                        <div class="form-group">
                          <label for="latitude" class="form-label">Latitude</label>
                          <input type="text" class="form-control" id="latitude" name="latitude" data-v-number="-90,90" data-v-msg="Number between -90 and 90." value="<?= e($form['latitude']) ?>">
                        </div>
                      </div>
                      <div class="col-md-3 col-12">
                        <div class="form-group">
                          <label for="longitude" class="form-label">Longitude</label>
                          <input type="text" class="form-control" id="longitude" name="longitude" data-v-number="-180,180" data-v-msg="Number between -180 and 180." value="<?= e($form['longitude']) ?>">
                        </div>
                      </div>
                    </div>
                    <div class="mt-3">
                      <button type="submit" class="btn btn-primary"><?= $edition ? 'Save changes' : 'Add station' ?></button>
                      <?php if ($edition): ?><a href="<?= BASE_URL ?>index.php?page=admin-louage-stations" class="btn btn-light-secondary">Cancel</a><?php endif; ?>
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
                  <h4>Station list</h4>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr><th>Station</th><th>City</th><th>Actions</th></tr>
                      </thead>
                      <tbody>
                        <?php foreach ($stations as $s): ?>
                        <tr>
                          <td><?= e($s->getNom()) ?></td><td><?= e($s->getVille()) ?></td>
                          <td>
                            <a href="<?= BASE_URL ?>index.php?page=admin-louage-stations&amp;edit=<?= $s->getId() ?>" class="btn btn-sm btn-success">Edit</a>
                            <form action="<?= BASE_URL ?>index.php?page=admin-louage-station-delete" method="post" class="d-inline" data-confirm="Delete this station?">
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                              <input type="hidden" name="id" value="<?= $s->getId() ?>">
                              <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$stations): ?><tr><td colspan="3">No station yet.</td></tr><?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
