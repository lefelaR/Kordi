<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Kordicraft Creations</title>
    <link rel="stylesheet" href="/assets/css/index.css">
</head>

<body class="dashboard-page">
    <header class="dashboard-header">
        <a class="dashboard-brand" href="/">KORDICRAFT CREATIONS</a>
        <a class="dashboard-logout" href="/logout">LOG OUT</a>
    </header>
    <main class="dashboard-content">
        <p class="auth-eyebrow">EVENT PLANNING</p>
        <h1>Dashboard</h1>
        <p class="dashboard-welcome">Signed in as <?= htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8') ?>.</p>
        <section class="dashboard-grid" aria-label="Dashboard overview">
            <article class="dashboard-panel">
                <span class="dashboard-number">01</span>
                <h2>Enquiries</h2>
                <p>Review and manage new event enquiries.</p>
            </article>
            <article class="dashboard-panel">
                <span class="dashboard-number">02</span>
                <h2>Events</h2>
                <p>Keep upcoming celebrations and functions organized.</p>
            </article>
            <article class="dashboard-panel">
                <span class="dashboard-number">03</span>
                <h2>Services</h2>
                <p>Manage the services presented to your clients.</p>
            </article>
        </section>
    </main>
</body>

</html>