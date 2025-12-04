<?php
session_start();
require_once __DIR__ . '/classes/Scenario.php';
require_once __DIR__ . '/classes/Game.php';

$scenario = new Scenario(__DIR__ . '/data/scenario.json');
$game = new Game($scenario);

if (isset($_SESSION['game_state'])) {
    $game->setState($_SESSION['game_state']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $inputVal = $_POST['user_input'] ?? '';
    $game->processAction($action, $inputVal);
    $_SESSION['game_state'] = $game->getState();
    header("Location: index.php");
    exit;
}

$state = $game->getState();
$node = $game->getCurrentNodeData();
$inventory = $game->getInventory();
$feedback = $game->getFeedbackMessage();
$current_node_name = $state['current_node'];
$item_image = $_SESSION['item_image'] ?? null;
if (isset($_SESSION['item_image'])) unset($_SESSION['item_image']);

?><!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Detective Game</title>
  <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@500;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="main-header">
  <div class="header-title">🔎 DETECTIVE GAME</div>
  <nav class="nav-menu">
    <?php if ($current_node_name !== 'pre_start'): ?>
      <form method="post" style="display:inline;">
        <button type="submit" name="action" value="restart" class="reset-btn">Restart Kasus</button>
      </form>
    <?php endif; ?>
  </nav>
</header>

<div class="game-container <?= ($current_node_name === 'pre_start') ? 'home-page' : '' ?>">
  <main class="story-box">
    <h1 class="scene-title"><?= htmlspecialchars($node['title']) ?></h1>

    <?php if (!empty($node['image'])): ?>
      <div class="scene-image-wrapper">
        <img src="assets/images/<?= htmlspecialchars($node['image']) ?>" alt="Scene">
      </div>
    <?php endif; ?>

    <div class="scene-text"><p><?= nl2br(htmlspecialchars($node['description'])) ?></p></div>

    <?php if ($feedback && !in_array($current_node_name, ['end_good_final','end_neutral_final','end_bad_final'])): ?>
      <div class="feedback-msg"><strong>Hint: </strong> <?= htmlspecialchars($feedback) ?></div>
    <?php endif; ?>

    <div class="interaction-area">
      <?php if (!empty($node['options'])): ?>
        <div class="choices">
          <?php foreach ($node['options'] as $act => $label): ?>
            <form method="post">
              <button type="submit" name="action" value="<?= $act ?>" class="<?= ($act === 'start_game') ? 'start-btn' : 'scene-choice-btn' ?>">
                <?= htmlspecialchars($label) ?>
              </button>
            </form>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($node['input'])): ?>
        <div class="puzzle-box">
          <form method="post">
            <label>🔑 <?= htmlspecialchars($node['input']['label'] ?? 'Masukkan') ?></label>
            <div style="display:flex; gap:1%;">
              <input type="text" name="user_input" required autofocus>
              <button type="submit" name="action" value="<?= htmlspecialchars($node['input']['action'] ?? '') ?>" class="start-btn">KIRIM</button>
            </div>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <?php if ($current_node_name !== 'pre_start'): ?>
    <aside class="inventory">
      <h3>💼 Inventori</h3>
      <div class="inventory-list">
        <?php if (empty($inventory)): ?>
          <p class="muted" style="color:#999; font-style:italic;">(Inventori Kosong)</p>
        <?php else: foreach ($inventory as $it): ?>
          <form method="post">
            <input type="hidden" name="action" value="use::<?= htmlspecialchars($it) ?>">
            <button class="choice-btn" type="submit"><?= htmlspecialchars($it) ?></button>
          </form>
        <?php endforeach; endif; ?>
      </div>

      <?php if ($item_image): ?>
        <div class="item-detail-box">
          <p style="color:var(--accent); font-weight:bold; margin-bottom:10px;">🔍 Detail Item:</p>
          <img src="assets/images/<?= htmlspecialchars($item_image) ?>" alt="Item Detail Image">
        </div>
      <?php endif; ?>
    </aside>
  <?php endif; ?>
</div>

<footer class="main-footer">
  <p>&copy; 2025 Detective Game: Menghilangnya Profesor Alaric | Abel Hidayat Kelas B</p>
</footer>

</body>
</html>
