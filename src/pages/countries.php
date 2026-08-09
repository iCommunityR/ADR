<?php
// src/pages/countries.php - list countries using cards
$stmt = $pdo->query('SELECT id,code,name,region,profile_summary FROM countries ORDER BY name');
countries = $stmt->fetchAll();
?>
<section>
  <h2>Countries</h2>
  <p class="lead">Explore country profiles and legal frameworks across Africa.</p>
  <div class="grid">
    <?php foreach ($countries as $c): $item = [
        'id' => $c['id'],
        'name' => $c['name'],
        'summary' => $c['profile_summary'],
        'document_type' => $c['region']
    ]; $entity = 'country'; include __DIR__ . '/../views/components/card.php'; endforeach; ?>
  </div>
</section>
