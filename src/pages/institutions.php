<?php
// src/pages/institutions.php - institutions listing (skeleton)
$stmt = $pdo->prepare('SELECT id,name AS title,description AS summary FROM institutions WHERE is_published=1 ORDER BY name LIMIT 48');
$stmt->execute();
$insts = $stmt->fetchAll();
?>
<section>
  <h2>Institutions</h2>
  <p class="lead">ADR institutions, tribunals and organisations.</p>
  <div class="grid">
    <?php foreach ($insts as $i): $item = [ 'id'=>$i['id'],'title'=>$i['title'],'summary'=>$i['summary'],'document_type'=>'Institution']; $entity='institution'; include __DIR__ . '/../views/components/card.php'; endforeach; ?>
  </div>
</section>
