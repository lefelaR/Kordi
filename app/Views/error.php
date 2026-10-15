<?php
/** @var string $errorClass */
/** @var string $errorMessage */
/** @var int $errorLine */
/** @var string $relativeFile */
/** @var string $codeBody */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error | Kordicraft Creations</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/index.css">
</head>

<body class="error-page">
    <main class="error-shell">
        <header class="error-intro">
            <p class="error-eyebrow">Application error</p>
            <h1>Something went wrong</h1>
            <p class="error-summary"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
        </header>

        <article class="code-card code-card-destructive" aria-label="Error details">
            <header class="code-card-header">
                <span class="code-card-title"><?= htmlspecialchars($errorClass, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="code-card-meta"><?= htmlspecialchars($relativeFile . ':' . $errorLine, ENT_QUOTES, 'UTF-8') ?></span>
            </header>
            
            <pre class="code-card-content" tabindex="0">
                <code><?= htmlspecialchars($codeBody, ENT_QUOTES, 'UTF-8') ?></code>
            </pre>

        </article>
    </main>
</body>

</html>
