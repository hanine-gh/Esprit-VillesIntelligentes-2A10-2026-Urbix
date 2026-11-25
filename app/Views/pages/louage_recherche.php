<!-- Main -->
<section id="main">
  <div class="container">
    <div class="row gtr-200">
      <div class="col-12">
        <section class="box">
          <h2>Search for a louage</h2>
          <p>Choose your departure, destination and travel date.</p>
          <form action="<?= BASE_URL ?>index.php" method="get" data-validate="modules" novalidate>
            <input type="hidden" name="page" value="louage-resultats">
            <div class="row gtr-uniform">
              <div class="col-6 col-12-small">
                <label for="departure">Departure city</label>
                <select id="departure" name="departure" data-v-required="Please choose a departure city.">
                  <option value="">Select departure</option>
                  <?php foreach ($villes as $v): ?><option><?= e($v) ?></option><?php endforeach; ?>
                </select>
              </div>
              <div class="col-6 col-12-small">
                <label for="destination">Destination</label>
                <select id="destination" name="destination" data-v-required="Please choose a destination." data-v-different="departure" data-v-different-msg="The destination must be different from the departure.">
                  <option value="">Select destination</option>
                  <?php foreach ($villes as $v): ?><option><?= e($v) ?></option><?php endforeach; ?>
                </select>
              </div>
              <div class="col-6 col-12-small">
                <label for="date">Travel date</label>
                <input type="date" id="date" name="date" data-v-required="Please choose a travel date." data-v-date data-v-msg="Invalid date.">
              </div>
              <div class="col-6 col-12-small">
                <label for="seats">Number of passengers</label>
                <input type="number" id="seats" name="seats" value="1" data-v-required="Number of passengers is required." data-v-int="1,8" data-v-msg="1 to 8 passengers.">
              </div>
              <div class="col-12">
                <ul class="actions">
                  <li><input type="submit" value="Search" class="button large" /></li>
                  <li><input type="reset" value="Reset" class="button alt large" /></li>
                </ul>
              </div>
            </div>
          </form>
        </section>
      </div>
    </div>
  </div>
</section>
