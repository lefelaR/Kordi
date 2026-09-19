<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Kordicraft Creations</title>
    <link rel="stylesheet" href="/assets/css/index.css">
</head>

<body class="auth-page">
    <main class="auth-card">
        <a class="auth-logo" href="/" aria-label="Back to Kordicraft Creations home">
            <img src="/images/logo.jpeg" alt="Kordicraft Creations">
        </a>
        <p class="auth-eyebrow">KORDICRAFT CREATIONS</p>
        <h1>Welcome back</h1>
        <p class="auth-intro">Sign in to access your event planning dashboard.</p>

        <?php if ($error !== ''): ?>
            <p class="form-message error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form class="auth-form" method="post" action="/login">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" autocomplete="username" required>
            <label for="password">Password</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>
            <button class="btn-black" type="submit">SIGN IN</button>
        </form>
        <a class="auth-back" href="/">Back to website</a>
    </main>
</body>

</html>