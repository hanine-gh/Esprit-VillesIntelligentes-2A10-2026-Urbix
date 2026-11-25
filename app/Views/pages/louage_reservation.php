<!-- Main -->
<section id="main">
  <div class="container">
    <div class="row gtr-200">
      <div class="col-12">
        <section class="box">
          <h2>Reserve your seat</h2>
          <p>Enter the passenger and trip details to request a reservation.</p>
          <?php if ($erreurs): ?>
            <div class="auth-alert auth-alert-error"><ul><?php foreach ($erreurs as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
          <?php endif; ?>
          <form action="<?= BASE_URL ?>index.php?page=louage-reservation" method="post" data-validate="modules" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <div class="row gtr-uniform">
              <div class="col-12">
                <label for="trip">Trip</label>
                <select id="trip" name="trip" data-v-required="Please choose a trip.">
                  <?php foreach ($horaires as $h): ?>
                  <option value="<?= $h->getId() ?>"<?= selected($form['trip'], $h->getId()) ?>><?= e($h->getTrajet()) ?> — <?= e(dateHeure($h->getDepartAt())) ?> (<?= $h->getPlacesLibres() ?> seats left)</option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-6 col-12-small">
                <label for="passenger">Passenger full name</label>
                <input id="passenger" name="passenger" type="text" value="<?= e($form['passenger']) ?>" data-v-required="Passenger name is required." data-v-pattern="^\p{L}[\p{L} '.-]{1,79}$" data-v-msg="Full name: 2 to 80 letters.">
              </div>
              <div class="col-6 col-12-small">
                <label for="phone">Phone number</label>
                <input id="phone" name="phone" type="tel" value="<?= e($form['phone']) ?>" data-v-required="Phone number is required." data-v-pattern="^\+?[0-9 ]{8,15}$" data-v-msg="Phone number: 8 to 15 digits.">
              </div>
              <div class="col-6 col-12-small">
                <label for="number-seats">Number of seats</label>
                <input id="number-seats" name="number_seats" type="number" value="<?= e($form['number_seats']) ?>" data-v-required="Number of seats is required." data-v-int="1,8" data-v-msg="Number of seats: 1 to 8.">
              </div>
              <div class="col-12">
                <ul class="actions">
                  <li><input type="submit" value="Confirm reservation request" class="button large" /></li>
                </ul>
                <p>Your request will be confirmed by the Urbix team.</p>
              </div>
            </div>
          </form>
        </section>
      </div>
    </div>
  </div>
</section>
