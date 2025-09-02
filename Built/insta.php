<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: index.php'); exit; }
$DB_FILE = __DIR__ . '/users.json';
function read_db(){ global $DB_FILE; return json_decode(file_get_contents($DB_FILE), true); }
function write_db($db){ global $DB_FILE; file_put_contents($DB_FILE, json_encode($db, JSON_PRETTY_PRINT)); }
function log_activity(&$db, $name, $action){ $db['activity'][]=["name"=>$name,"action"=>$action,"time"=>gmdate('c')]; }
$db = read_db();
$user = $_SESSION['user'];
log_activity($db, $user, "insta_view");
write_db($db);

$profiles = [
  ["handle" => "Unknown_black_me", "img" => "assets/images/insta1.png"],
  ["handle" => "Azi_the_gost_of_uchiha", "img" => "assets/images/insta2.png"],
  ["handle" => "._.Amzu._.official._", "img" => "assets/images/insta3.png"],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Insta — welcome to web</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/style.css">
  <script defer src="assets/script.js"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg">
  <header class="site-header">
    <button class="hamburger" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
    <div class="brand"><i class="fa-brands fa-instagram"></i><span>Insta List</span></div>
    <nav class="nav">
      <a href="lobby.php" class="nav-link"><i class="fa-solid fa-house"></i> Home</a>
      <a href="admin.php" class="nav-link"><i class="fa-solid fa-user-cog"></i> Admin Panel</a>
      <a href="lobby.php?action=logout" class="nav-link"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
      <span class="by-arm"><em>–by Arman</em></span>
    </nav>
  </header>

  <main class="container fade-in">
    <section class="grid">
      <?php foreach ($profiles as $p): ?>
        <div class="card glass profile-card">
          <img src="<?php echo htmlspecialchars($p['img']); ?>" class="avatar" alt="<?php echo htmlspecialchars($p['handle']); ?>" />
          <h3><?php echo htmlspecialchars($p['handle']); ?></h3>
          <div class="links">
            <a class="btn small" href="https://instagram.com/<?php echo urlencode(str_replace('@','',$p['handle'])); ?>" target="_blank" rel="noopener">
              <i class="fa-brands fa-instagram"></i> View
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </section>
  </main>

  <footer class="site-footer">
    <div class="muted small"><i class="fa-solid fa-envelope"></i> copyright@gmail.com</div>
  </footer>
</body>
</html>
