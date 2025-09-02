<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: index.php'); exit; }
$DB_FILE = __DIR__ . '/users.json';
function read_db(){
  global $DB_FILE;
  if (!file_exists($DB_FILE)) {
    file_put_contents($DB_FILE, json_encode(["users"=>[], "content"=>["series"=>[], "movies"=>[], "anime"=>[], "routines"=>[]], "activity"=>[]], JSON_PRETTY_PRINT));
  }
  $json = @file_get_contents($DB_FILE);
  $data = json_decode($json, true);
  if (!$data) $data = ["users"=>[], "content"=>["series"=>[], "movies"=>[], "anime"=>[], "routines"=>[]], "activity"=>[]];
  return $data;
}
function write_db($db){ global $DB_FILE; file_put_contents($DB_FILE, json_encode($db, JSON_PRETTY_PRINT)); }
function log_activity(&$db, $name, $action){ $db['activity'][]=["name"=>$name,"action"=>$action,"time"=>gmdate('c')]; }
$db = read_db();
$user = $_SESSION['user'];

if (isset($_GET['action']) && $_GET['action']==='logout') {
  log_activity($db, $user, "sign_out");
  write_db($db);
  session_destroy();
  header('Location: index.php'); exit;
}

log_activity($db, $user, "lobby_view");
write_db($db);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Lobby — welcome to web</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/style.css">
  <script defer src="assets/script.js"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg">
  <header class="site-header">
    <button class="hamburger" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
    <div class="brand">
      <!-- ensure logo visible -->
      <span>Welcome to Web</span>
    </div>
    <nav class="nav">
      <a href="lobby.php" class="nav-link active"><i class="fa-solid fa-house"></i> Home</a>
      <a href="admin.php" class="nav-link"><i class="fa-solid fa-user-cog"></i> Admin Panel</a>
      <a href="insta.php" class="nav-link"><i class="fa-brands fa-instagram"></i> Insta</a>
      <a href="?action=logout" class="nav-link"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
    </nav>
  </header>

  <main class="container fade-in">
    <section class="grid">
      <a class="card glass action-card" href="morning.php">
        <i class="fa-solid fa-sun icon"></i>
        <h3>Morning</h3>
        <p class="muted">Start your day with warmups and routines.</p>
        <button class="btn small"><i class="fa-solid fa-play"></i> Open</button>
      </a>
      <a class="card glass action-card" href="movies.php">
        <i class="fa-solid fa-film icon"></i>
        <h3>Movies</h3>
        <p class="muted">Best series, movies, and anime picks.</p>
        <button class="btn small"><i class="fa-solid fa-play"></i> Open</button>
      </a>
      <a class="card glass action-card" href="webapps.php">
        <i class="fa-solid fa-window-restore icon"></i>
        <h3>Web & Apps</h3>
        <p class="muted">Useful sites and app links.</p>
        <button class="btn small"><i class="fa-solid fa-play"></i> Open</button>
      </a>
      <a class="card glass action-card" href="insta.php">
        <i class="fa-brands fa-instagram icon"></i>
        <h3>Insta</h3>
        <p class="muted">Follow the featured accounts.</p>
        <button class="btn small"><i class="fa-solid fa-play"></i> Open</button>
      </a>
      <a class="card glass action-card" href="admin.php">
        <i class="fa-solid fa-database icon"></i>
        <h3>Admin Panel</h3>
        <p class="muted">Manage users, lists and activities.</p>
        <button class="btn small"><i class="fa-solid fa-gear"></i> Open</button>
      </a>
    </section>
  </main>

  <footer class="site-footer">
    <div class="muted small"><i class="fa-solid fa-envelope"></i> copyright@gmail.com</div>
  </footer>
</body>
</html>
