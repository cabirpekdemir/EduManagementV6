<?php
// themes/default/pages/announcements/view.php

<h2><?= htmlspecialchars($announcement['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
<p>Oluşturan: <?= htmlspecialchars($announcement['creator_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
<p>Tarih: <?= htmlspecialchars($announcement['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
<p><?= nl2br(htmlspecialchars($announcement['content'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>