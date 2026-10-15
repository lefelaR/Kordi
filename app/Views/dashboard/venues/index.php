<?php
$venueListQuery = static function (int $page = 1) use ($venueSearch): string {
    $query = [];
    if ($venueSearch !== '') {
        $query['q'] = $venueSearch;
    }
    if ($page > 1) {
        $query['page'] = (string) $page;
    }

    return $query === [] ? '/dashboard/venues' : '/dashboard/venues?' . http_build_query($query);
};
$venueReturnQuery = http_build_query(array_filter([
    'q' => $venueSearch !== '' ? $venueSearch : null,
    'page' => $venuePage > 1 ? (string) $venuePage : null,
]));
$venueMapPoints = [];
foreach ($venues as $venue) {
    $venueMapPoints[] = [
        'name' => (string) ($venue['name'] ?? ''),
        'location' => (string) ($venue['location'] ?? ''),
        'latitude' => $venue['latitude'] ?? null,
        'longitude' => $venue['longitude'] ?? null,
    ];
}
?>
<section class="dashboard-page">
    <div class="dashboard-page-bar">
        <header class="dashboard-page-header">
            <h1>Venues</h1>
            <p>Places where events are held.</p>
        </header>
        <a class="dashboard-button" href="/venues/add">Add</a>
    </div>
    <div class="dashboard-panel">
        <form class="dashboard-table-toolbar" method="get" action="/dashboard/venues" role="search" data-venue-search>
            <div class="dashboard-search-field">
                <svg class="dashboard-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
                <label class="dashboard-visually-hidden" for="venue-search">Search venues</label>
                <input id="venue-search" type="search" name="q" maxlength="120" placeholder="Search..." value="<?= htmlspecialchars($venueSearch, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off">
                <kbd class="dashboard-search-shortcut" aria-hidden="true"><span>⌘</span>K</kbd>
            </div>
        </form>
        <div data-venue-results>
            <div hidden data-venues-map-points><?= htmlspecialchars(json_encode($venueMapPoints, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP), ENT_NOQUOTES, 'UTF-8') ?></div>
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Capacity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($venues === []): ?>
                        <tr>
                            <td class="dashboard-empty" colspan="5"><?= $venueSearch !== '' ? 'No venues match your search.' : 'No venues yet.' ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($venues as $venue): ?>
                            <?php
                            $priceLabel = '—';
                            if ($venue['price'] !== null && $venue['price'] !== '') {
                                $priceLabel = 'R ' . number_format((float) $venue['price'], 2, '.', ',');
                            }
                            $capacityLabel = $venue['capacity'] === null || $venue['capacity'] === ''
                                ? '—'
                                : (string) (int) $venue['capacity'];
                            $editHref = '/venues/' . (int) $venue['id'] . '/edit' . ($venueReturnQuery !== '' ? '?' . $venueReturnQuery : '');
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($venue['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($capacityLabel, ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="dashboard-actions">
                                        <a class="dashboard-button dashboard-button-secondary dashboard-icon-button" href="<?= htmlspecialchars($editHref, ENT_QUOTES, 'UTF-8') ?>" aria-label="Edit <?= htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8') ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                            </svg>
                                        </a>
                                        <form method="post" action="/venues/<?= (int) $venue['id'] ?>/delete" onsubmit="return confirm('Delete this venue?')">
                                            <input type="hidden" name="page" value="<?= (int) $venuePage ?>">
                                            <?php if ($venueSearch !== ''): ?>
                                                <input type="hidden" name="q" value="<?= htmlspecialchars($venueSearch, ENT_QUOTES, 'UTF-8') ?>">
                                            <?php endif; ?>
                                            <button class="dashboard-button dashboard-button-destructive dashboard-icon-button" type="submit" aria-label="Delete <?= htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8') ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M19 6l-1 14H6L5 6" />
                                                    <path d="M10 11v6" />
                                                    <path d="M14 11v6" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ($venueCount > 0): ?>
                <nav class="dashboard-pagination" aria-label="Venues pages">
                    <p>Showing <?= (int) $venueRangeStart ?>–<?= (int) $venueRangeEnd ?> of <?= (int) $venueCount ?></p>
                    <div class="dashboard-pagination-links">
                        <?php if ($venuePage > 1): ?>
                            <a href="<?= htmlspecialchars($venueListQuery($venuePage - 1), ENT_QUOTES, 'UTF-8') ?>">Previous</a>
                        <?php else: ?>
                            <span aria-disabled="true">Previous</span>
                        <?php endif; ?>
                        <?php foreach ($venuePageLinks as $page): ?>
                            <?php if ($page === null): ?>
                                <span aria-hidden="true">…</span>
                            <?php elseif ($page === $venuePage): ?>
                                <a href="<?= htmlspecialchars($venueListQuery((int) $page), ENT_QUOTES, 'UTF-8') ?>" aria-current="page"><?= (int) $page ?></a>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($venueListQuery((int) $page), ENT_QUOTES, 'UTF-8') ?>"><?= (int) $page ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if ($venuePage < $venuePageCount): ?>
                            <a href="<?= htmlspecialchars($venueListQuery($venuePage + 1), ENT_QUOTES, 'UTF-8') ?>">Next</a>
                        <?php else: ?>
                            <span aria-disabled="true">Next</span>
                        <?php endif; ?>
                    </div>
                </nav>
            <?php endif; ?>
        </div>
    </div>
    <div class="dashboard-panel dashboard-venues-map-panel">
        <div class="dashboard-venues-map" data-venues-map role="img" aria-label="Map of venue locations in Cape Town"></div>
    </div>
    <script>
        (() => {
            const form = document.querySelector('[data-venue-search]');
            const input = document.getElementById('venue-search');
            const results = document.querySelector('[data-venue-results]');
            if (!form || !input || !results) {
                return;
            }

            let debounceId = 0;
            let requestId = 0;

            const searchUrl = (query) => {
                const trimmed = query.trim();
                return trimmed === ''
                    ? '/dashboard/venues'
                    : '/dashboard/venues?' + new URLSearchParams({ q: trimmed }).toString();
            };

            const updateResults = async (query) => {
                const url = searchUrl(query);
                const currentRequest = ++requestId;
                try {
                    const response = await fetch(url, {
                        headers: { Accept: 'text/html' },
                        credentials: 'same-origin',
                    });
                    if (!response.ok || currentRequest !== requestId) {
                        return;
                    }
                    const html = await response.text();
                    if (currentRequest !== requestId) {
                        return;
                    }
                    const parsed = new DOMParser().parseFromString(html, 'text/html');
                    const nextResults = parsed.querySelector('[data-venue-results]');
                    if (!nextResults) {
                        return;
                    }
                    results.innerHTML = nextResults.innerHTML;
                    history.replaceState(null, '', url);
                    document.dispatchEvent(new Event('venues:results-updated'));
                } catch (error) {
                    if (currentRequest === requestId) {
                        window.location.assign(url);
                    }
                }
            };

            input.addEventListener('input', () => {
                window.clearTimeout(debounceId);
                debounceId = window.setTimeout(() => {
                    updateResults(input.value);
                }, 250);
            });

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                window.clearTimeout(debounceId);
                updateResults(input.value);
            });

            document.addEventListener('keydown', (event) => {
                if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
                    event.preventDefault();
                    input.focus();
                    input.select();
                }
            });
        })();
    </script>
</section>
