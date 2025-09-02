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
$allowedNames = ['arman','admin'];
$hasAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$err = '';

if (!$hasAdmin) {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['op'] ?? '') === 'admin_login') {
    $an = strtolower(trim($_POST['admin_name'] ?? ''));
    $ap = trim($_POST['admin_pass'] ?? '');
    if (in_array($an, $allowedNames, true) && $ap === 'admin') {
      $_SESSION['is_admin'] = true;
      $_SESSION['admin_name'] = $an;
      log_activity($db, $user, "admin_login_success:$an");
      write_db($db);
      header('Location: admin.php'); exit;
    } else {
      $err = 'Invalid admin credentials.';
      log_activity($db, $user, "admin_login_failed");
      write_db($db);
    }
  }

  // Render admin login gate and exit
  ?>
  <!doctype html>
  <html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Admin Login — welcome to web</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="assets/style.css">
    <script defer src="assets/script.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  </head>
  <body class="bg">
    <header class="site-header">
      <button class="hamburger" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
      <div class="brand"><i class="fa-solid fa-user-cog"></i><span>Admin Panel</span></div>
      <nav class="nav">
        <a href="lobby.php" class="nav-link"><i class="fa-solid fa-house"></i> Home</a>
        <a href="lobby.php?action=logout" class="nav-link"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
        <span class="by-arm"><em>–by Arman</em></span>
      </nav>
    </header>
    <main class="container fade-in">
      <section class="card glass auth-card">
        <div class="auth-header">
          <h2><i class="fa-solid fa-lock"></i> Admin Access</h2>
          <p class="muted small">Only name “arman” or “admin” with password “admin”.</p>
        </div>
        <?php if ($err): ?>
          <div class="alert error"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($err); ?></div>
        <?php endif; ?>
        <form method="post" class="form">
          <input type="hidden" name="op" value="admin_login" />
          <div class="field">
            <label><i class="fa-solid fa-user"></i> Admin Name</label>
            <input name="admin_name" placeholder="arman or admin" required />
          </div>
          <div class="field">
            <label><i class="fa-solid fa-key"></i> Password</label>
            <input name="admin_pass" type="password" placeholder="admin" required />
          </div>
          <div class="buttons">
            <button class="btn primary" type="submit"><i class="fa-solid fa-right-to-bracket"></i> Enter</button>
            <a class="btn" href="lobby.php"><i class="fa-solid fa-arrow-left"></i> Back</a>
          </div>
        </form>
      </section>
    </main>
    <footer class="site-footer">
      <div class="muted small"><i class="fa-solid fa-envelope"></i> copyright@gmail.com</div>
    </footer>
  </body>
  </html>
  <?php
  exit;
}

// we are admin now
log_activity($db, $user, "admin_view");

// Handle user edits
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['op']) && $_POST['op']==='update_user') {
    $orig = $_POST['orig_name'] ?? '';
    $idx = find_user_index($db, $orig);
    if ($idx >= 0) {
      $db['users'][$idx]['name'] = trim($_POST['name'] ?? $db['users'][$idx]['name']);
      $db['users'][$idx]['age'] = intval($_POST['age'] ?? $db['users'][$idx]['age']);
      $db['users'][$idx]['sex'] = trim($_POST['sex'] ?? $db['users'][$idx]['sex']);
      log_activity($db, $user, "admin_user_updated:$orig");
    }
  }
  if (isset($_POST['op']) && $_POST['op']==='delete_user') {
    $del = $_POST['orig_name'] ?? '';
    $db['users'] = array_values(array_filter($db['users'], function($u) use ($del){ return strcasecmp($u['name'],$del)!==0; }));
    log_activity($db, $user, "admin_user_deleted:$del");
  }
  if (isset($_POST['op']) && $_POST['op']==='save_lists') {
    // Expect newline-separated lists
    $db['content']['series'] = array_values(array_filter(array_map('trim', explode("\n", $_POST['series'] ?? ""))));
    $db['content']['movies'] = array_values(array_filter(array_map('trim', explode("\n", $_POST['movies'] ?? ""))));
    $db['content']['anime']  = array_values(array_filter(array_map('trim', explode("\n", $_POST['anime'] ?? ""))));
    log_activity($db, $user, "admin_lists_saved");
  }
  if (isset($_POST['op']) && $_POST['op']==='save_routines') {
    // JSON inputs for routines to allow label/duration
    $azd = json_decode($_POST['azilanDaily'] ?? "[]", true); if (!$azd) $azd=[];
    $azs = json_decode($_POST['azilanSunday'] ?? "[]", true); if (!$azs) $azs=[];
    $ams = json_decode($_POST['amzhaSunday'] ?? "[]", true); if (!$ams) $ams=[];
    $db['content']['routines'] = [
      "azilanDaily" => $azd,
      "azilanSunday" => $azs,
      "amzhaSunday" => $ams
    ];
    log_activity($db, $user, "admin_routines_saved");
  }
  write_db($db);
  header('Location: admin.php'); exit;
}

$series = implode("\n", $db['content']['series'] ?? []);
$movies = implode("\n", $db['content']['movies'] ?? []);
$anime  = implode("\n", $db['content']['anime'] ?? []);
$routines = $db['content']['routines'] ?? ["azilanDaily"=>[], "azilanSunday"=>[], "amzhaSunday"=>[]];
write_db($db);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Panel — welcome to web</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/style.css">
  <script defer src="assets/script.js"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg">
  <header class="site-header">
    <button class="hamburger" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
    <div class="brand"><i class="fa-solid fa-user-cog"></i><span>Admin Panel</span></div>
    <nav class="nav">
      <a href="lobby.php" class="nav-link"><i class="fa-solid fa-house"></i> Home</a>
      <a href="lobby.php?action=logout" class="nav-link"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
      <span class="by-arm"><em>–by Arman</em></span>
    </nav>
  </header>

  <main class="container fade-in admin">
    <section class="card glass">
      <h2><i class="fa-solid fa-database"></i> Registered Users</h2>
      <div class="table">
        <div class="row header">
          <div>Name</div><div>Age</div><div>Sex</div><div>Actions</div>
        </div>
        <?php foreach ($db['users'] as $u): ?>
          <form method="post" class="row">
            <input type="hidden" name="orig_name" value="<?php echo htmlspecialchars($u['name']); ?>">
            <input type="hidden" name="op" value="update_user">
            <div><input class="input-inline" name="name" value="<?php echo htmlspecialchars($u['name']); ?>"></div>
            <div><input class="input-inline" name="age" type="number" value="<?php echo intval($u['age']); ?>"></div>
            <div>
              <select name="sex" class="input-inline">
                <option <?php echo $u['sex']=='M'?'selected':''; ?>>M</option>
                <option <?php echo $u['sex']=='F'?'selected':''; ?>>F</option>
                <option <?php echo $u['sex']=='Other'?'selected':''; ?>>Other</option>
              </select>
            </div>
            <div class="actions">
              <button class="btn small"><i class="fa-solid fa-pen-to-square"></i> Save</button>
          </form>
              <form method="post" onsubmit="return confirm('Delete user <?php echo htmlspecialchars($u['name']); ?>?');">
                <input type="hidden" name="orig_name" value="<?php echo htmlspecialchars($u['name']); ?>">
                <input type="hidden" name="op" value="delete_user">
                <button class="btn danger small"><i class="fa-solid fa-trash"></i> Delete</button>
              </form>
            </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="card glass">
      <h2><i class="fa-solid fa-edit"></i> Edit Best Lists</h2>
      <form method="post" class="lists-form">
        <input type="hidden" name="op" value="save_lists">
        <div class="grid-2">
          <div>
            <label>Best Series (one per line)</label>
            <textarea name="series" rows="10"><?php echo htmlspecialchars($series); ?></textarea>
          </div>
          <div>
            <label>Best Movies (one per line)</label>
            <textarea name="movies" rows="10"><?php echo htmlspecialchars($movies); ?></textarea>
          </div>
        </div>
        <div>
          <label>Best Anime (one per line)</label>
          <textarea name="anime" rows="8"><?php echo htmlspecialchars($anime); ?></textarea>
        </div>
        <button class="btn primary"><i class="fa-solid fa-floppy-disk"></i> Save Lists</button>
      </form>
    </section>

    <section class="card glass">
      <h2><i class="fa-solid fa-list-check"></i> Edit Routines</h2>
      <p class="muted small">Provide JSON array: [{"label":"Task","duration":"2h"}]</p>
      <form method="post" class="lists-form">
        <input type="hidden" name="op" value="save_routines">
        <div class="grid-3">
          <div>
            <label>Azilan Daily (JSON)</label>
            <textarea name="azilanDaily" rows="10"><?php echo htmlspecialchars(json_encode($routines['azilanDaily'], JSON_PRETTY_PRINT)); ?></textarea>
          </div>
          <div>
            <label>Azilan Sunday (JSON)</label>
            <textarea name="azilanSunday" rows="10"><?php echo htmlspecialchars(json_encode($routines['azilanSunday'], JSON_PRETTY_PRINT)); ?></textarea>
          </div>
          <div>
            <label>Amzha Sunday (JSON)</label>
            <textarea name="amzhaSunday" rows="10"><?php echo htmlspecialchars(json_encode($routines['amzhaSunday'], JSON_PRETTY_PRINT)); ?></textarea>
          </div>
        </div>
        <button class="btn primary"><i class="fa-solid fa-floppy-disk"></i> Save Routines</button>
      </form>
    </section>

    <section class="card glass">
      <h2><i class="fa-solid fa-clipboard-list"></i> Activity Log</h2>
      <div class="log">
        <?php foreach (array_reverse($db['activity']) as $a): ?>
          <div class="log-item">
            <span class="tag"><?php echo htmlspecialchars($a['name']); ?></span>
            <span class="muted"><?php echo htmlspecialchars($a['action']); ?></span>
            <span class="time"><?php echo htmlspecialchars($a['time']); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="muted small"><i class="fa-solid fa-envelope"></i> copyright@gmail.com</div>
  </footer>
</body>
</html>
