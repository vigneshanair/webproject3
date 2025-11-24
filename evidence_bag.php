<?php
if (!isset($caseId)) {
    $caseId = isset($_GET['case']) ? (int)$_GET['case'] : 1;
}
$bag = get_evidence_for_case($caseId);
?>
<div class="evidence-bag">
  <h3>Evidence Bag</h3>
  <?php if (empty($bag)): ?>
    <p>No evidence collected yet.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($bag as $item): ?>
        <li><?= htmlspecialchars($item['label']) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
