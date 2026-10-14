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
        $details = trim($_POST['details'] ?? '');
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
        } elseif (strlen($details) > 5000) {
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
}
