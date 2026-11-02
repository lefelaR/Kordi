<?php

namespace App\Controllers;

use App\Models\Auth;
use App\Models\Venue;

class VenueController
{
    public function __construct(private Auth $auth, private Venue $venues)
    {
    }

    public function add(): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        $error = '';
        $name = '';
        $location = '';
        $price = '';
        $capacity = '';
        $details = '';
        $latitude = '';
        $longitude = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->readVenueInput();
            $error = $input['error'];
            $name = $input['name'];
            $location = $input['location'];
            $price = $input['price'];
            $capacity = $input['capacity'];
            $details = $input['details'];
            $latitude = $input['latitude'];
            $longitude = $input['longitude'];

            if ($error === '') {
                $this->venues->create(
                    $name,
                    $input['storedLocation'],
                    $input['storedPrice'],
                    $input['storedCapacity'],
                    $input['storedDetails'],
                    $input['storedLatitude'],
                    $input['storedLongitude']
                );
                header('Location: ' . $this->venuesLocation());
                exit;
            }
        }

        $pageTitle = 'Add venue | Kordicraft Creations';
        $currentSection = 'venues';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'href' => '/dashboard'],
            ['label' => 'Venues', 'href' => '/dashboard/venues'],
            ['label' => 'Add venue'],
        ];
        $venuePage = $this->requestedPage();
        $venueSearch = $this->requestedSearch();
        $formAction = '/venues/add';
        $heading = 'Add venue';
        $summary = 'Save a venue to the venues list.';
        $extraStylesheets = ['/assets/leaflet/leaflet.css'];
        $extraScripts = [
            '/assets/leaflet/leaflet.js',
            '/assets/js/venue-map.js',
            '/assets/js/wysiwyg.js',
        ];

        ob_start();
        require __DIR__ . '/../Views/dashboard/venues/form.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }

    public function edit(int $id): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        $venue = $this->venues->find($id);
        if ($venue === null) {
            $this->notFound();
            return;
        }

        $error = '';
        $name = (string) $venue['name'];
        $location = (string) ($venue['location'] ?? '');
        $price = $venue['price'] === null ? '' : (string) $venue['price'];
        $capacity = $venue['capacity'] === null ? '' : (string) $venue['capacity'];
        $details = (string) ($venue['details'] ?? '');
        $latitude = $venue['latitude'] === null ? '' : (string) $venue['latitude'];
        $longitude = $venue['longitude'] === null ? '' : (string) $venue['longitude'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->readVenueInput();
            $error = $input['error'];
            $name = $input['name'];
            $location = $input['location'];
            $price = $input['price'];
            $capacity = $input['capacity'];
            $details = $input['details'];
            $latitude = $input['latitude'];
            $longitude = $input['longitude'];

            if ($error === '') {
                $this->venues->update(
                    $id,
                    $name,
                    $input['storedLocation'],
                    $input['storedPrice'],
                    $input['storedCapacity'],
                    $input['storedDetails'],
                    $input['storedLatitude'],
                    $input['storedLongitude']
                );
                header('Location: ' . $this->venuesLocation());
                exit;
            }
        }

        $pageTitle = 'Edit venue | Kordicraft Creations';
        $currentSection = 'venues';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'href' => '/dashboard'],
            ['label' => 'Venues', 'href' => '/dashboard/venues'],
            ['label' => 'Edit venue'],
        ];
        $venuePage = $this->requestedPage();
        $venueSearch = $this->requestedSearch();
        $formAction = '/venues/' . $id . '/edit';
        $heading = 'Edit venue';
        $summary = 'Update this venue.';
        $actionIcons = true;
        $extraStylesheets = ['/assets/leaflet/leaflet.css'];
        $extraScripts = [
            '/assets/leaflet/leaflet.js',
            '/assets/js/venue-map.js',
            '/assets/js/wysiwyg.js',
        ];

        ob_start();
        require __DIR__ . '/../Views/dashboard/venues/form.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }

    public function delete(int $id): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->venues->delete($id);
        }

        header('Location: ' . $this->venuesLocation());
        exit;
    }

    private function notFound(): void
    {
        http_response_code(404);
        $pageTitle = 'Not found | Kordicraft Creations';
        $currentSection = 'venues';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'href' => '/dashboard'],
            ['label' => 'Not found'],
        ];

        ob_start();
        require __DIR__ . '/../Views/dashboard/not-found.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }

    private function requestedPage(): int
    {
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
        if (!is_int($page) || $page < 1) {
            $page = filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT);
        }

        return is_int($page) && $page > 0 ? $page : 1;
    }

    private function requestedSearch(): string
    {
        $search = trim((string) ($_GET['q'] ?? $_POST['q'] ?? ''));
        if (strlen($search) > 120) {
            return substr($search, 0, 120);
        }

        return $search;
    }

    private function venuesLocation(): string
    {
        $page = $this->requestedPage();
        $search = $this->requestedSearch();
        $query = [];
        if ($search !== '') {
            $query['q'] = $search;
        }
        if ($page > 1) {
            $query['page'] = (string) $page;
        }

        if ($query === []) {
            return '/dashboard/venues';
        }

        return '/dashboard/venues?' . http_build_query($query);
    }

    private function readVenueInput(): array
    {
        $name = trim($_POST['name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $capacity = trim($_POST['capacity'] ?? '');
        $rawDetails = trim($_POST['details'] ?? '');
        $details = strlen($rawDetails) > 50000 ? '' : $this->sanitizeRichText($rawDetails);
        $latitude = trim($_POST['latitude'] ?? '');
        $longitude = trim($_POST['longitude'] ?? '');
        $error = '';
        $storedPrice = null;
        $storedCapacity = null;
        $storedLatitude = null;
        $storedLongitude = null;

        if ($name === '' || strlen($name) > 180) {
            $error = 'Enter a venue name of 180 characters or fewer.';
        } elseif (strlen($location) > 255) {
            $error = 'Enter a location of 255 characters or fewer.';
        } elseif (strlen($rawDetails) > 50000 || $this->plainTextLength($details) > 5000) {
            $error = 'Enter details of 5000 characters or fewer.';
        } elseif ($price !== '') {
            if (!preg_match('/^\d+(\.\d{1,2})?$/', $price)) {
                $error = 'Enter a valid price with up to two decimal places.';
            } else {
                $storedPrice = $price;
            }
        }

        if ($error === '' && $capacity !== '') {
            if (!ctype_digit($capacity) || (int) $capacity < 1) {
                $error = 'Enter a capacity of 1 or more.';
            } else {
                $storedCapacity = (int) $capacity;
            }
        }

        if ($error === '') {
            $hasLatitude = $latitude !== '';
            $hasLongitude = $longitude !== '';
            if ($hasLatitude !== $hasLongitude) {
                $error = 'Set both map coordinates, or clear the pin.';
            } elseif ($hasLatitude && $hasLongitude) {
                if (!preg_match('/^-?\d+(\.\d+)?$/', $latitude) || !preg_match('/^-?\d+(\.\d+)?$/', $longitude)) {
                    $error = 'Enter valid map coordinates.';
                } else {
                    $lat = (float) $latitude;
                    $lng = (float) $longitude;
                    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                        $error = 'Enter valid map coordinates.';
                    } else {
                        $storedLatitude = number_format($lat, 7, '.', '');
                        $storedLongitude = number_format($lng, 7, '.', '');
                    }
                }
            }
        }

        return [
            'error' => $error,
            'name' => $name,
            'location' => $location,
            'price' => $price,
            'capacity' => $capacity,
            'details' => $details,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'storedLocation' => $location === '' ? null : $location,
            'storedPrice' => $storedPrice,
            'storedCapacity' => $storedCapacity,
            'storedDetails' => $details === '' ? null : $details,
            'storedLatitude' => $storedLatitude,
            'storedLongitude' => $storedLongitude,
        ];
    }

    private function sanitizeRichText(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"><body>' . $html . '</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $document->getElementsByTagName('body')->item(0);
        if ($loaded !== true || !$body instanceof \DOMElement) {
            return $this->plainParagraph($html);
        }

        $this->sanitizeChildren($body);
        $result = '';
        foreach ($body->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }
        $result = trim($result);
        if ($result === '' || $this->plainTextLength($result) === 0) {
            return '';
        }

        return $result;
    }

    private function sanitizeChildren(\DOMNode $node): void
    {
        $allowed = [
            'p' => [],
            'br' => [],
            'strong' => [],
            'b' => [],
            'em' => [],
            'i' => [],
            'u' => [],
            'h2' => [],
            'ul' => [],
            'ol' => [],
            'li' => [],
            'blockquote' => [],
            'a' => ['href'],
        ];
        $drop = [
            'script', 'style', 'iframe', 'object', 'embed', 'link', 'meta',
            'svg', 'math', 'form', 'textarea', 'input', 'button',
        ];
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child instanceof \DOMText) {
                continue;
            }
            if (!$child instanceof \DOMElement || $child->parentNode === null) {
                $child->parentNode?->removeChild($child);
                continue;
            }

            $tag = strtolower($child->tagName);
            if (in_array($tag, $drop, true)) {
                $child->parentNode->removeChild($child);
                continue;
            }

            $heading = preg_match('/^h[1-6]$/', $tag) === 1;
            if ($tag === 'div' || ($heading && $tag !== 'h2')) {
                $replacement = $child->ownerDocument->createElement($tag === 'div' ? 'p' : 'h2');
                while ($child->firstChild !== null) {
                    $replacement->appendChild($child->firstChild);
                }
                $child->parentNode->replaceChild($replacement, $child);
                $this->sanitizeChildren($replacement);
                continue;
            }

            if (!array_key_exists($tag, $allowed)) {
                $this->sanitizeChildren($child);
                $this->unwrap($child);
                continue;
            }

            $this->filterAttributes($child, $allowed[$tag]);
            if ($tag === 'a' && !$child->hasAttribute('href')) {
                $this->sanitizeChildren($child);
                $this->unwrap($child);
                continue;
            }

            $this->sanitizeChildren($child);
        }
    }

    /**
     * @param  list<string>  $allowed
     */
    private function filterAttributes(\DOMElement $element, array $allowed): void
    {
        $remove = [];
        if ($element->attributes !== null) {
            foreach ($element->attributes as $attribute) {
                if (!in_array(strtolower($attribute->name), $allowed, true)) {
                    $remove[] = $attribute->name;
                }
            }
        }
        foreach ($remove as $name) {
            $element->removeAttribute($name);
        }

        if (strtolower($element->tagName) !== 'a') {
            return;
        }

        $href = html_entity_decode(trim($element->getAttribute('href')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $href = preg_replace('/[\x00-\x1F\x7F]/', '', $href) ?? $href;
        if (!$this->isSafeHref($href)) {
            $element->removeAttribute('href');
            return;
        }

        $element->setAttribute('href', $href);
        $element->setAttribute('rel', 'noopener noreferrer');
        if (preg_match('/^https?:/i', $href) === 1) {
            $element->setAttribute('target', '_blank');
        }
    }

    private function unwrap(\DOMNode $node): void
    {
        $parent = $node->parentNode;
        if ($parent === null) {
            return;
        }

        while ($node->firstChild !== null) {
            $parent->insertBefore($node->firstChild, $node);
        }
        $parent->removeChild($node);
    }

    private function isSafeHref(string $href): bool
    {
        return preg_match('/^(https?:\/\/|mailto:)/i', $href) === 1
            && preg_match('/^(javascript|data|vbscript):/i', $href) !== 1;
    }

    private function plainParagraph(string $html): string
    {
        $text = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($text === '') {
            return '';
        }

        return '<p>' . htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
    }

    private function plainTextLength(string $html): int
    {
        $withBreaks = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $html) ?? $html;
        $withBreaks = preg_replace('/<\/\s*(p|div|h[1-6]|li|blockquote)\s*>/i', "\n", $withBreaks) ?? $withBreaks;
        $text = html_entity_decode(strip_tags($withBreaks), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(str_replace("\xc2\xa0", ' ', $text));
        if (function_exists('mb_strlen')) {
            return mb_strlen($text, 'UTF-8');
        }

        $count = preg_match_all('/./us', $text, $matches);

        return $count === false ? strlen($text) : $count;
    }
}
