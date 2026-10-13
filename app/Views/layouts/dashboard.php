<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/index.css">
</head>

<body>
    <header>
        <div class="logo">
            <div class="logo-mark">
                <img src="/images/logo.jpeg" alt="Kordicraft Logo">
            </div>
            <div class="logo-text">
                <h1>KORDICRAFT CREATIONS</h1>
                <p>Where Vision Meets Excellence, Effortlessly</p>
            </div>
        </div>
        <button class="menu-toggle" type="button" aria-label="Open navigation menu" aria-expanded="false" aria-controls="site-navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <?php require __DIR__ . '/../includes/dashboard-nav.php'; ?>
    </header>
    <div class="dashboard-shell">
        <?php require __DIR__ . '/../includes/dashboard-side-nav.php'; ?>
        <div class="dashboard-main">
            <?= $content ?>
        </div>
    </div>
    <script src="/assets/js/index.js"></script>
</body>

</html>
