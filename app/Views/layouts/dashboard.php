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
    <header class="dashboard-topnav">
        <a class="dashboard-brand" href="/dashboard">Kordicraft</a>
        <?php require __DIR__ . '/../includes/dashboard-nav.php'; ?>
    </header>
    <main class="dashboard-content">
        <?= $content ?>
    </main>
</body>

</html>
