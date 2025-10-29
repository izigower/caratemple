<?php
/**
 * Flash message renderer.
 *
 * Outputs session-based flash notifications in a consistent wrapper.
 *
 * @package CaraTemple\Includes
 */

declare(strict_types=1);

$flashMessages = get_flash_messages();

if ($flashMessages === []) {
    return;
}
?>
<div class="flash-messages" role="status" aria-live="polite">
    <?php foreach ($flashMessages as $type => $messages) : ?>
        <?php foreach ($messages as $message) : ?>
            <div class="flash flash--<?= htmlspecialchars($type); ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
