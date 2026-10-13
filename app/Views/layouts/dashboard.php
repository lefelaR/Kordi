<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/index.css">
</head>

<body class="dashboard-body">
    <div class="dashboard-shell">
        <?php require __DIR__ . '/../includes/dashboard-side-nav.php'; ?>
        <main class="dashboard-content">
            <?= $content ?>
        </main>
    </div>
</body>

</html>
