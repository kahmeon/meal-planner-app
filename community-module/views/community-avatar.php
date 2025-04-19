<?php
require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../models/UserCommunity.php");
global $con;
?>
<?php if (!empty($avatar)): ?>
    <img src="<?= htmlspecialchars($avatar) ?>" class="user-avatar me-2" alt="User Avatar" />
<?php else: ?>
    <p>Pic error</p>
<?php endif; ?>
