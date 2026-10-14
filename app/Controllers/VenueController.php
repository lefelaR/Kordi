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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->readVenueInput();
            $error = $input['error'];
            $name = $input['name'];
            $location = $input['location'];
            $price = $input['price'];
            $capacity = $input['capacity'];

            if ($error === '') {
                $this->venues->create($name, $input['storedLocation'], $input['storedPrice'], $input['storedCapacity']);
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->readVenueInput();
            $error = $input['error'];
            $name = $input['name'];
            $location = $input['location'];
            $price = $input['price'];
            $capacity = $input['capacity'];

            if ($error === '') {
                $this->venues->update($id, $name, $input['storedLocation'], $input['storedPrice'], $input['storedCapacity']);
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
        $error = '';
        $storedPrice = null;
        $storedCapacity = null;

        if ($name === '' || strlen($name) > 180) {
            $error = 'Enter a venue name of 180 characters or fewer.';
        } elseif (strlen($location) > 255) {
            $error = 'Enter a location of 255 characters or fewer.';
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

        return [
            'error' => $error,
            'name' => $name,
            'location' => $location,
            'price' => $price,
            'capacity' => $capacity,
            'storedLocation' => $location === '' ? null : $location,
            'storedPrice' => $storedPrice,
            'storedCapacity' => $storedCapacity,
        ];
    }
}
