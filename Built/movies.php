<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: index.php'); exit; }
$DB_FILE = __DIR__ . '/users.json';
function read_db(){ global $DB_FILE; return json_decode(file_get_contents($DB_FILE), true); }
function write_db($db){ global $DB_FILE; file_put_contents($DB_FILE, json_encode($db, JSON_PRETTY_PRINT)); }
function log_activity(&$db, $name, $action){ $db['activity'][]=["name"=>$name,"action"=>$action,"time"=>gmdate('c')]; }
function sanitize_title($s){
  $s = preg_replace('/[\x{0B80}-\x{0BFF}]+/u', '', $s); // Tamil block
  return trim($s);
}

$db = read_db();
$user = $_SESSION['user'];
log_activity($db, $user, "movies_view");
$series = $db['content']['series'] ?? [];
$movies = $db['content']['movies'] ?? [];
$anime = $db['content']['anime'] ?? [];
write_db($db);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Movies & Series — welcome to web</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/style.css">
  <script defer src="assets/script.js"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg">
  <header class="site-header">
    <button class="hamburger" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
    <div class="brand"><i class="fa-solid fa-film"></i><span>Best Picks</span></div>
    <nav class="nav">
      <a href="lobby.php" class="nav-link"><i class="fa-solid fa-house"></i> Home</a>
      <a href="admin.php" class="nav-link"><i class="fa-solid fa-user-cog"></i> Admin Panel</a>
      <a href="insta.php" class="nav-link"><i class="fa-brands fa-instagram"></i> Insta</a>
      <a href="lobby.php?action=logout" class="nav-link"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
      <span class="by-arm"><em>–by Arman</em></span>
    </nav>
  </header>

  <main class="container fade-in">
    <section class="card glass">
      <h2><i class="fa-solid fa-clapperboard"></i> Best Series (Top 15)</h2>
      <ol class="pill-list">
        <?php foreach ($series as $i => $title): $t = sanitize_title($title); ?>
          <li class="pill"><span class="num"><?php echo $i+1; ?></span> <span><?php echo htmlspecialchars($t); ?></span></li>
        <?php endforeach; ?>
      </ol>
    </section>

    <section class="card glass">
      <h2><i class="fa-solid fa-video"></i> Best Movies (Top 15)</h2>
      <ol class="pill-list">
        <?php foreach ($movies as $i => $title): $t = sanitize_title($title); ?>
          <li class="pill"><span class="num"><?php echo $i+1; ?></span> <span><?php echo htmlspecialchars($t); ?></span></li>
        <?php endforeach; ?>
      </ol>
    </section>

    <section class="card glass">
      <h2><i class="fa-solid fa-dragon"></i> Best Anime (Top 15)</h2>
      <ol class="pill-list">
        <?php foreach ($anime as $i => $title): $t = sanitize_title($title); ?>
          <li class="pill"><span class="num"><?php echo $i+1; ?></span> <span><?php echo htmlspecialchars($t); ?></span></li>
        <?php endforeach; ?>
      </ol>
    </section>
  </main>

  <footer class="site-footer">
    <div class="muted small"><i class="fa-solid fa-envelope"></i> copyright@gmail.com</div>
  </footer>
</body>
</html>
