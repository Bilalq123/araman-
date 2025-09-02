<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: index.php'); exit; }
$DB_FILE = __DIR__ . '/users.json';
function read_db(){ global $DB_FILE; return json_decode(file_get_contents($DB_FILE), true); }
function write_db($db){ global $DB_FILE; file_put_contents($DB_FILE, json_encode($db, JSON_PRETTY_PRINT)); }
function log_activity(&$db, $name, $action){ $db['activity'][]=["name"=>$name,"action"=>$action,"time"=>gmdate('c')]; }
function find_user_index($db, $name){ foreach ($db['users'] as $i=>$u){ if (strcasecmp($u['name'],$name)===0) return $i; } return -1; }

$db = read_db();
$user = $_SESSION['user'];
if (isset($_GET['action']) && strpos($_GET['action'], 'clicked_') === 0) {
  log_activity($db, $user, $_GET['action']);
  write_db($db);
  header('Location: morning.php'); exit;
}
log_activity($db, $user, "morning_view");

$idx = find_user_index($db, $user);
$createdAt = $db['users'][$idx]['createdAt'] ?? date('Y-m-d');
$daysSince = max(0, (new DateTime())->diff(new DateTime($createdAt))->days);
$todayTarget = 5 + $daysSince;
write_db($db);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Morning — welcome to web</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/style.css">
  <script defer src="assets/script.js"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg">
  <header class="site-header">
    <button class="hamburger" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
    <div class="brand"><i class="fa-solid fa-globe"></i><span>Welcome to Web – by Arman</span></div>
    <nav class="nav">
      <a href="lobby.php" class="nav-link"><i class="fa-solid fa-house"></i> Home</a>
      <a href="admin.php" class="nav-link"><i class="fa-solid fa-user-cog"></i> Admin Panel</a>
      <a href="insta.php" class="nav-link"><i class="fa-brands fa-instagram"></i> Insta</a>
      <a href="lobby.php?action=logout" class="nav-link"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
      <span class="by-arm"><em>–by Arman</em></span>
    </nav>
  </header>

  <main class="container fade-in">
    <section class="clock glass">
      <div class="clock-row">
        <div class="time"><i class="fa-regular fa-clock"></i> <span id="clock">--:--:--</span></div>
        <div class="date"><i class="fa-regular fa-calendar"></i> <span id="date">--</span></div>
      </div>
    </section>

    <section class="grid">
      <div class="card glass">
        <h3><i class="fa-solid fa-dumbbell"></i> Morning Warmup</h3>
        <ul class="list">
          <li><a class="nav-link" href="?action=clicked_health_drink"><i class="fa-solid fa-heartbeat"></i> Health Drink & Health Food</a></li>
          <li><a class="nav-link" href="?action=clicked_running"><i class="fa-solid fa-person-running"></i> Running</a></li>
          <li><a class="nav-link" href="?action=clicked_stretching"><i class="fa-solid fa-person-walking"></i> Stretching</a></li>
          <li><a class="nav-link" href="?action=clicked_pushups"><i class="fa-solid fa-hand-fist"></i> Push-ups</a></li>
        </ul>
      </div>

      <div class="card glass">
        <h3><i class="fa-solid fa-hand-fist"></i> Push-up Tracker</h3>
        <p class="muted">Base starts at 5 and increases by 1 each day since account creation.</p>
        <div class="tracker">
          <div>Today's Target: <strong><?php echo intval($todayTarget); ?></strong></div>
          <div>Created at: <strong><?php echo htmlspecialchars($createdAt); ?></strong></div>
          <div>Days since: <strong><?php echo intval($daysSince); ?></strong></div>
        </div>
      </div>

      <div class="card glass">
        <h3><i class="fa-solid fa-list-check"></i> Azilan Daily Routine</h3>
        <ul class="checklist" data-routine="azilanDaily" data-user="<?php echo htmlspecialchars($user); ?>"></ul>
      </div>

      <div class="card glass" data-sunday-only="true" id="azilan-sunday-card">
        <h3><i class="fa-solid fa-sun"></i> Azilan Sunday Routine <span class="muted small">(Sunday only)</span></h3>
        <ul class="checklist" data-routine="azilanSunday" data-user="<?php echo htmlspecialchars($user); ?>"></ul>
      </div>

      <div class="card glass" data-sunday-only="true" id="amzha-sunday-card">
        <h3><i class="fa-solid fa-leaf"></i> Amzha Sunday Routine <span class="muted small">(Sunday only)</span></h3>
        <ul class="checklist" data-routine="amzhaSunday" data-user="<?php echo htmlspecialchars($user); ?>"></ul>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="muted small"><i class="fa-solid fa-envelope"></i> copyright@gmail.com</div>
  </footer>

  <script>
    // Inject routines from PHP (from users.json)
    window.__ROUTINES__ = <?php echo json_encode($db['content']['routines'] ?? []); ?>;
  </script>
</body>
</html>
