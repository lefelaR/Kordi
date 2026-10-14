<section class="dashboard-page">
    <header class="dashboard-page-header">
        <h1><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($summary, ENT_QUOTES, 'UTF-8') ?></p>
    </header>
    <?php if ($error !== ''): ?>
        <p class="dashboard-form-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php
    $venueCancelQuery = http_build_query(array_filter([
        'q' => ($venueSearch ?? '') !== '' ? $venueSearch : null,
        'page' => ($venuePage ?? 1) > 1 ? (string) $venuePage : null,
    ]));
    $venueCancelHref = '/dashboard/venues' . ($venueCancelQuery !== '' ? '?' . $venueCancelQuery : '');
    ?>
    <form class="dashboard-form dashboard-form-with-details" method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" data-venue-form>
        <input type="hidden" name="page" value="<?= (int) ($venuePage ?? 1) ?>">
        <?php if (($venueSearch ?? '') !== ''): ?>
            <input type="hidden" name="q" value="<?= htmlspecialchars($venueSearch, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
        <input type="hidden" name="latitude" value="<?= htmlspecialchars($latitude, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="longitude" value="<?= htmlspecialchars($longitude, ENT_QUOTES, 'UTF-8') ?>">
        <div class="dashboard-form-fields">
            <label>
                Name
                <input type="text" name="name" maxlength="180" required value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Location
                <div class="dashboard-location-field">
                    <input type="text" name="location" maxlength="255" value="<?= htmlspecialchars($location, ENT_QUOTES, 'UTF-8') ?>">
                    <button class="dashboard-location-picker" type="button" data-venue-map-open aria-haspopup="dialog" aria-controls="venue-map-modal" aria-label="Pick location on map">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z" />
                        </svg>
                    </button>
                </div>
            </label>
            <label>
                Price
                <input type="text" name="price" inputmode="decimal" maxlength="15" value="<?= htmlspecialchars($price, ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Capacity
                <input type="number" name="capacity" min="1" step="1" value="<?= htmlspecialchars($capacity, ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <div class="dashboard-form-actions">
                <button class="dashboard-button<?= !empty($actionIcons) ? ' dashboard-button-with-icon' : '' ?>" type="submit">
                    <?php if (!empty($actionIcons)): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                    <?php endif; ?>
                    Save
                </button>
                <a class="dashboard-button dashboard-button-secondary<?= !empty($actionIcons) ? ' dashboard-button-with-icon' : '' ?>" href="<?= htmlspecialchars($venueCancelHref, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!empty($actionIcons)): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    <?php endif; ?>
                    Cancel
                </a>
            </div>
        </div>
        <label class="dashboard-form-details">
            Details
            <textarea name="details" rows="12" maxlength="5000"><?= htmlspecialchars($details, ENT_QUOTES, 'UTF-8') ?></textarea>
        </label>
    </form>

    <dialog class="dashboard-map-modal" id="venue-map-modal" data-venue-map-modal>
        <div class="dashboard-map-modal-card">
            <header class="dashboard-map-modal-header">
                <div>
                    <h2>Pick location</h2>
                    <p>Click the map to set the address.</p>
                </div>
                <button class="dashboard-button dashboard-button-secondary dashboard-icon-button" type="button" data-venue-map-close aria-label="Close map">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            </header>
            <div class="dashboard-map-modal-canvas" data-venue-map-modal-canvas role="img" aria-label="Choose venue location on map"></div>
            <p class="dashboard-map-modal-address" data-venue-map-modal-address>Click a point on the map.</p>
            <div class="dashboard-map-modal-actions">
                <button class="dashboard-button" type="button" data-venue-map-apply disabled>Use location</button>
                <button class="dashboard-button dashboard-button-secondary" type="button" data-venue-map-close>Cancel</button>
            </div>
        </div>
    </dialog>
</section>
