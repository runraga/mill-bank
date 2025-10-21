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
  <?php foreach ($images as $i => $img):
    $imgUrl = $webItemsPath . '/' . rawurlencode($slug) . '/' . rawurlencode(basename($img));
  ?>
    <button type="button"
            class="thumb"
            data-index="<?php echo $i; ?>"
            data-src="<?php echo htmlspecialchars($imgUrl); ?>"
            aria-label="Open image <?php echo $i + 1; ?>">
      <img src="<?php echo htmlspecialchars($imgUrl); ?>"
           alt="<?php echo htmlspecialchars($title); ?>"
           loading="lazy">
    </button>
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
<!-- Lightbox -->
<div id="lightbox" class="lightbox hidden" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="lb-backdrop" data-close="1"></div>

  <div class="lb-content" role="document">
    <button class="lb-close" aria-label="Close (Esc)" data-close="1">&times;</button>

    <button class="lb-nav lb-prev" aria-label="Previous image">&#10094;</button>

    <img id="lb-image" alt="Expanded item image">

    <button class="lb-nav lb-next" aria-label="Next image">&#10095;</button>

    <div class="lb-counter" aria-live="polite" aria-atomic="true"></div>
  </div>
</div>

<script>
(function(){
  const thumbs = Array.from(document.querySelectorAll('.gallery .thumb'));
  if (!thumbs.length) return;

  const lb = document.getElementById('lightbox');
  const imgEl = document.getElementById('lb-image');
  const btnPrev = lb.querySelector('.lb-prev');
  const btnNext = lb.querySelector('.lb-next');
  const btnClose = lb.querySelector('.lb-close');
  const counter = lb.querySelector('.lb-counter');
  const backdrop = lb.querySelector('.lb-backdrop');

  const sources = thumbs.map(b => b.dataset.src);
  let idx = 0;
  let lastFocus = null;

  function show(i){
    idx = (i + sources.length) % sources.length;
    imgEl.src = sources[idx];
    counter.textContent = (sources.length > 1)
      ? ( (idx+1) + ' / ' + sources.length )
      : '';
  }

  function open(i){
    lastFocus = document.activeElement;
    show(i);
    lb.classList.remove('hidden');
    lb.setAttribute('aria-hidden', 'false');
    document.body.classList.add('no-scroll');
    btnClose.focus();
    document.addEventListener('keydown', onKey);
  }

  function close(){
    lb.classList.add('hidden');
    lb.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('no-scroll');
    document.removeEventListener('keydown', onKey);
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  function onKey(e){
    if (e.key === 'Escape') close();
    else if (e.key === 'ArrowRight') show(idx+1);
    else if (e.key === 'ArrowLeft') show(idx-1);
  }

  thumbs.forEach(b=>{
    b.addEventListener('click', ()=> open(Number(b.dataset.index)||0));
  });

  btnNext.addEventListener('click', ()=> show(idx+1));
  btnPrev.addEventListener('click', ()=> show(idx-1));
  btnClose.addEventListener('click', close);
  backdrop.addEventListener('click', close);
})();
</script>
<footer class="site-footer">
  <small><a href="index.php">All items</a></small>
</footer>
</body>
</html>
