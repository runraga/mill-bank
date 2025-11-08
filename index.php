<?php
// config
$itemsDir = __DIR__ . '/items';
$webItemsPath = 'items';

// find item folders
$dirs = array_values(array_filter(scandir($itemsDir), function ($d) use ($itemsDir) {
    return $d[0] !== '.' && is_dir($itemsDir . '/' . $d);
}));

function read_text($path) {
    return file_exists($path) ? trim(file_get_contents($path)) : '';
}

function first_image_in($dir) {
    $files = glob($dir . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
    sort($files, SORT_NATURAL);
    return $files ? $files[0] : null;
}

function title_for($itemPath) {
    $t = read_text($itemPath . '/title.txt');
    if ($t !== '') return $t;
    $desc = read_text($itemPath . '/description.txt');
    if ($desc !== '') {
        $firstLine = strtok($desc, "\r\n");
        return $firstLine !== false ? trim($firstLine) : 'Untitled';
    }
    return 'Untitled';
}
function status_for($itemPath) {
    $p = $itemPath . '/status.txt';
    if (!file_exists($p)) return 'available';
    $v = strtolower(trim(file_get_contents($p)));
    return ($v === 'taken') ? 'taken' : 'available';
}

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Stuff</title>
<link rel="stylesheet" href="styles.css">
<link rel="icon" type="image/png" href="../favicon.png">
</head>
<body>
<header class="site-header">
  <h1>Items for rehoming</h1>
  <p>All items are free but please consider a small donation to the Mill Bank fund or the Community Centre fund.</p>
  <p>Details on how to make a donation available on request.</p>
  <h2>Collection</h2>
  <p>We'll be home 3.30pm onwards on weekdays, Saturday 8th 5pm - 7pm and Sunday 9th between midday and 4pm.</p>
  <p>
</header>

<main class="grid">
<?php foreach ($dirs as $slug):
    $safeSlug = basename($slug);
    $itemPath = $itemsDir . '/' . $safeSlug;
    $thumb = first_image_in($itemPath);
    $title = htmlspecialchars(title_for($itemPath));
    $thumbUrl = $thumb ? htmlspecialchars($webItemsPath . '/' . rawurlencode($safeSlug) . '/' . rawurlencode(basename($thumb))) : null;
    $status = status_for($itemPath);
?>
  <a class="card <?php echo $status === 'taken' ? 'is-taken' : ''; ?>" href="item.php?item=<?php echo urlencode($safeSlug); ?>">
    <?php if ($thumbUrl): ?>
      <div class="thumb-wrap">
        <img src="<?php echo $thumbUrl; ?>" alt="<?php echo $title; ?>" loading="lazy">
        <?php if ($status === 'taken'): ?>
          <span class="badge badge-taken" aria-label="Taken">TAKEN</span>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="placeholder">No image</div>
    <?php endif; ?>
    <div class="card-body">
      <h2><?php echo $title; ?></h2>
    </div>
  </a>
<?php endforeach; ?>
</main>

<footer class="site-footer">
  <?php
  date_default_timezone_set('Europe/London');
  ?>
  <small>Last updated: <?php echo htmlspecialchars(date('Y-m-d H:i')); ?> (UK time)</small>
</footer>
</body>
</html>