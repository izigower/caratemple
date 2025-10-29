<?php
/**
 * Global header component.
 *
 * Generates the HTML document head section with meta tags and favicon configuration.
 *
 * @package CaraTemple\Includes
 */

declare(strict_types=1);

$rawTitle = trim($page_title ?? 'Le temple des fans de Carapuce');
if (stripos($rawTitle, 'CaraTemple') === false) {
    $rawTitle .= ' · CaraTemple';
}

$pageTitle = $rawTitle;
$bodyClass = $body_class ?? '';
$pageDescription = trim($page_description ?? 'CaraTemple est le forum francophone des passionnés de Carapuce : découvre les discussions, partage tes stratégies aquatiques et échange avec la communauté.');
$metaRobots = $meta_robots ?? 'index,follow';
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$pageUrl = $page_url ?? rtrim(BASE_URL, '/') . $currentPath;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="<?= htmlspecialchars($pageDescription); ?>" />
    <meta name="robots" content="<?= htmlspecialchars($metaRobots); ?>" />
    <link rel="canonical" href="<?= htmlspecialchars($pageUrl); ?>" />
    <meta property="og:locale" content="fr_FR" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle); ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription); ?>" />
    <meta property="og:url" content="<?= htmlspecialchars($pageUrl); ?>" />
    <meta property="og:site_name" content="CaraTemple" />
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL; ?>/assets/images/favicon.svg" />
    <link rel="apple-touch-icon" href="<?= BASE_URL; ?>/assets/images/favicon.svg" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/main.css" />
    <title><?= htmlspecialchars($pageTitle); ?></title>
</head>
<body id="top" class="<?= htmlspecialchars($bodyClass); ?>">
<a class="skip-link" href="#main-content">Aller au contenu principal</a>
<header class="site-header">
    <?php require __DIR__ . '/navigation.php'; ?>
</header>
