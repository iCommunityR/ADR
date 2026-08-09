<?php
// src/views/components/card.php
// Expects $item (array) and optional $entity (string) provided by caller
$entity = $entity ?? 'document';
if (!isset($item) || !is_array($item)) return;

function esc($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$title = $item['title'] ?? ($item['name'] ?? 'Untitled');
$summary = $item['summary'] ?? $item['profile_summary'] ?? '';
$year = $item['year'] ?? null;
$type = $item['document_type'] ?? $entity;
$id = (int)($item['id'] ?? 0);
$file = $item['file_path'] ?? null;

$cardHref = '/?p=' . ($entity === 'document' ? 'document&id=' . $id : ($entity === 'country' ? 'countries' : $entity));
?>
<article class="card" data-href="<?= $cardHref ?>">
  <div class="card-body">
    <div class="card-meta">
      <span class="badge"><?= esc($type) ?></span>
      <?php if ($year): ?><span class="muted"><?= esc($year) ?></span><?php endif; ?>
    </div>
    <h3 class="card-title"><?= esc($title) ?></h3>
    <p class="card-summary"><?= esc(mb_strimwidth($summary, 0, 220, '...')) ?></p>
    <div class="card-cta">
      <a class="btn btn-outline" href="<?= $cardHref ?>">View</a>
      <?php if ($file && $entity === 'document'): ?>
        <a class="btn btn-primary" href="/public/download.php?id=<?= $id ?>">Download</a>
      <?php endif; ?>
    </div>
  </div>
</article>
