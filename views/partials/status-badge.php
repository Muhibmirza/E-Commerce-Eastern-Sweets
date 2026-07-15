<?php
$status = (string)($status ?? 'pending');
$label = ucwords(str_replace('_', ' ', $status));
?>
<span class="status status-<?= h($status) ?>"><?= h($label) ?></span>
