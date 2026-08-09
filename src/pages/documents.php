<?php
// src/pages/documents.php - documents listing
$stmt = $pdo->prepare('SELECT id,title,summary,year,document_type,file_path FROM documents WHERE is_published=1 ORDER BY published_at DESC LIMIT 48');
$stmt->execute();
$docs = $stmt->fetchAll();
?>
<section>
  <h2>Documents</h2>
  <p class="lead">Latest published legislation, rules and guidance.</p>
  <div class="grid">
    <?php foreach ($docs as $d): $item = $d; $entity = 'document'; include __DIR__ . '/../views/components/card.php'; endforeach; ?>
  </div>
</section>
