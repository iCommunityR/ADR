<?php
// src/pages/cases.php - cases listing (skeleton)
$stmt = $pdo->prepare('SELECT id,case_name AS title,summary,year FROM cases WHERE is_published=1 ORDER BY decision_date DESC LIMIT 48');
$stmt->execute();
$cases = $stmt->fetchAll();
?>
<section>
  <h2>Cases</h2>
  <p class="lead">Notable case law and decisions.</p>
  <div class="grid">
    <?php foreach ($cases as $c): $item = [ 'id'=>$c['id'],'title'=>$c['title'],'summary'=>$c['summary'],'year'=>$c['year'],'document_type'=>'Case']; $entity='case'; include __DIR__ . '/../views/components/card.php'; endforeach; ?>
  </div>
</section>
