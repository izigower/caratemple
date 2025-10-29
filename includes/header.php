<?php
/**
 * Global header component.
 *
 * Generates the HTML document head section with meta tags and favicon configuration.
 *
 * @package CaraTemple\Includes
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="CaraTemple est le forum francophone dédié aux disciples de Carapuce : rejoignez la communauté, échangez et partagez vos découvertes aquatiques." />
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL; ?>/assets/images/favicon.svg" />
    <link rel="apple-touch-icon" href="<?= BASE_URL; ?>/assets/images/favicon.svg" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/main.css" />
    <title>CaraTemple · Le temple des fans de Carapuce</title>
</head>
<body id="top">
<header class="site-header">
    <?php require __DIR__ . '/navigation.php'; ?>
</header>
