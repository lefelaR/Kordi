<?php
$currentPage = $currentPage ?? '';
$navHome = $currentPage === 'home' ? '' : '/';
?>
<header>
    <div class="logo">
        <div class="logo-mark">
            <img src="/images/logo.jpeg" alt="Kordicraft Logo"></div>
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
    <nav id="site-navigation">
        <a href="<?= $navHome ?>#home">HOME</a>
        <a href="<?= $navHome ?>#who">WHO WE ARE</a>
        <a href="<?= $navHome ?>#what">WHAT WE DO</a>
        <a href="/calendar"<?= $currentPage === 'calendar' ? ' aria-current="page"' : '' ?>>CALENDAR</a>
        <a href="<?= $navHome ?>#contact">CONTACT</a>
        <a href="<?= $navHome ?>#contact" class="btn-black">GET A QUOTE</a>
    </nav>
</header>
