      <div class="page-heading">
        <h3>Manage ticket prices</h3>
        <p class="text-subtitle text-muted">Set and update the price for each route.</p>
      </div>
      <div class="page-content">
        <section class="section">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4>Set a price</h4>
                </div>
                <div class="card-body">
                  <?php require __DIR__ . '/../admin/partials/messages.php'; ?>
                  <form action="<?= BASE_URL ?>index.php?page=admin-louage-prix" method="post" data-validate="modules" novalidate>
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
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
                          <label for="price" class="form-label">Price (TND)</label>
                          <input type="number" class="form-control" id="price" name="price" data-v-required="Price is required." data-v-number="0,1000" data-v-msg="Price: 0 to 1000 TND." value="<?= e($form['price']) ?>">
                        </div>
                      </div>
                    </div>
                    <div class="mt-3">
                      <button type="submit" class="btn btn-primary">Save price</button>
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
                  <h4>Price list</h4>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr><th>Route</th><th>Price</th><th>Actions</th></tr>
                      </thead>
                      <tbody>
                        <?php foreach ($avecPrix as $r): ?>
                        <tr>
                          <td><?= e($r->getTrajet()) ?></td><td><?= e(prixTnd($r->getPrix())) ?></td>
                          <td>
                            <a href="<?= BASE_URL ?>index.php?page=admin-louage-prix&amp;edit=<?= $r->getId() ?>" class="btn btn-sm btn-success">Edit</a>
                            <form action="<?= BASE_URL ?>index.php?page=admin-louage-prix-delete" method="post" class="d-inline" data-confirm="Delete this price?">
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                              <input type="hidden" name="id" value="<?= $r->getId() ?>">
                              <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$avecPrix): ?><tr><td colspan="3">No price set yet.</td></tr><?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
