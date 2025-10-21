<?php
$itemsDir = __DIR__ . '/items';
$webItemsPath = 'items';

// Validate item param: allow simple folder names only
$slug = $_GET['item'] ?? '';
if (!preg_match('/^[A-Za-z0-9_\-]+$/', $slug)) {
    http_response_code(404); exit('Not found');
}

$itemPath = $itemsDir . '/' . $slug;
if (!is_dir($itemPath)) {
    http_response_code(404); exit('Not found');
}

function read_text($path) {
    return file_exists($path) ? trim(file_get_contents($path)) : '';
}

function all_images($dir) {
    $files = glob($dir . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
    sort($files, SORT_NATURAL);
    return $files;
}

function status_for($itemPath) {
    $p = $itemPath . '/status.txt';
    if (!file_exists($p)) return 'available';
    $v = strtolower(trim(file_get_contents($p)));
    return ($v === 'taken') ? 'taken' : 'available';
}
$status = status_for($itemPath);

$title = read_text($itemPath . '/title.txt');
if ($title === '') {
    $descFirst = strtok(read_text($itemPath . '/description.txt'), "\r\n");
    $title = $descFirst ? $descFirst : 'Item';
}
$description = read_text($itemPath . '/description.txt');
$images = all_images($itemPath);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo htmlspecialchars($title); ?> – Free Stuff</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<header class="site-header">
  <a href="index.php" class="back">&larr; Back</a>
  <h1><?php echo htmlspecialchars($title); ?></h1>
  <?php if ($status === 'taken'): ?>
  <span class="badge-line">TAKEN</span>
<?php endif; ?>
</header>

<main class="item-layout">
  <section class="gallery">
    <?php if ($images): ?>
      <?php foreach ($images as $img):
        $imgUrl = $webItemsPath . '/' . rawurlencode($slug) . '/' . rawurlencode(basename($img));
      ?>
        <a href="<?php echo htmlspecialchars($imgUrl); ?>" target="_blank" rel="noopener">
          <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($title); ?>" loading="lazy">
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="placeholder large">No images</div>
    <?php endif; ?>
  </section>

  <section class="description">
    <?php if ($description): ?>
      <?php foreach (preg_split('/\R\R+/', $description) as $para): ?>
        <p><?php echo nl2br(htmlspecialchars(trim($para))); ?></p>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No description provided.</p>
    <?php endif; ?>
    <div class="cta">
      <p><strong>Interested?</strong> Reply to my WhatsApp message with the item title.</p>
    </div>
  </section>
</main>

<footer class="site-footer">
  <small><a href="index.php">All items</a></small>
</footer>
</body>
</html>
