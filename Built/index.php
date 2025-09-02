<?php
session_start();
$DB_FILE = __DIR__ . '/users.json';

function read_db() {
  global $DB_FILE;
  if (!file_exists($DB_FILE)) {
    file_put_contents($DB_FILE, json_encode(["users"=>[], "content"=>["series"=>[], "movies"=>[], "anime"=>[], "routines"=>[]], "activity"=>[]], JSON_PRETTY_PRINT));
  }
  $json = @file_get_contents($DB_FILE);
  $data = json_decode($json, true);
  if (!$data) $data = ["users"=>[], "content"=>["series"=>[], "movies"=>[], "anime"=>[], "routines"=>[]], "activity"=>[]];
  return $data;
}
function write_db($data) {
  global $DB_FILE;
  $fp = @fopen($DB_FILE, 'c+');
  if ($fp) {
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
  } else {
    file_put_contents($DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
  }
}
function find_user(&$db, $name) {
  foreach ($db['users'] as $idx => $u) {
    if (strcasecmp($u['name'], $name) === 0) return $idx;
  }
  return -1;
}
function log_activity(&$db, $name, $action) {
  $db['activity'][] = ["name" => $name, "action" => $action, "time" => gmdate('c')];
}

$name = '';
$age = '';
$sex = '';

$db = read_db();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $age = trim($_POST['age'] ?? '');
  $sex = trim($_POST['sex'] ?? '');
  $action = $_POST['action'] ?? '';

  if ($name === '' || !is_numeric($age) || intval($age) <= 0 || $sex === '') {
    $error = 'Please fill all fields with valid data.';
  } else {
    if ($action === 'create') {
      $idx = find_user($db, $name);
      if ($idx >= 0) {
        $error = 'User already exists. Try signing in.';
      } else {
        $db['users'][] = [
          "name" => $name,
          "age" => intval($age),
          "sex" => $sex,
          "createdAt" => date('Y-m-d'),
          "progress" => []
        ];
        log_activity($db, $name, "account_created");
        $_SESSION['user'] = $name;
        log_activity($db, $_SESSION['user'], "sign_in");
        write_db($db);
        header('Location: lobby.php');
        exit;
      }
    } elseif ($action === 'signin') {
      $idx = find_user($db, $name);
      if ($idx >= 0 && intval($db['users'][$idx]['age']) === intval($age) && strcasecmp($db['users'][$idx]['sex'], $sex) === 0) {
        $_SESSION['user'] = $db['users'][$idx]['name'];
        log_activity($db, $_SESSION['user'], "sign_in");
        write_db($db);
        header('Location: lobby.php');
        exit;
      } else {
        $error = 'User not found or details do not match.';
      }
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>welcome to web — Sign In</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/style.css">
  <script defer src="assets/script.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg">
  <header class="site-header">
    <div class="brand">
      <img src="assets/images/logo.png" alt="Welcome to Web logo" class="logo-img" />
      <span>welcome to web</span>
    </div>
  </header>

  <main class="container fade-in">
    <section class="auth-card glass">
      <div class="auth-header">
        <img src="assets/images/login.png" alt="Login icon" class="icon-img" />
        <h1><i class="fa-solid fa-right-to-bracket"></i> Sign In</h1>
        <p class="muted">Enter your details to continue. Or create a new account below.</p>
      </div>

      <?php if ($error): ?>
        <div class="alert error"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert success"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <form method="post" class="form" id="auth-form">
        <input type="hidden" name="action" id="intent" value="signin" />
        <div class="field">
          <label for="name"><i class="fa-solid fa-user"></i> Name</label>
          <input type="text" id="name" name="name" required placeholder="Enter your name" value="<?php echo htmlspecialchars($name); ?>" />
        </div>
        <div class="field">
          <label for="age"><i class="fa-solid fa-hashtag"></i> Age</label>
          <input type="number" id="age" name="age" min="1" required placeholder="Your age" value="<?php echo htmlspecialchars($age); ?>" />
        </div>
        <div class="field">
          <label for="sex"><i class="fa-solid fa-venus-mars"></i> Sex</label>
          <select id="sex" name="sex" required>
            <option value="">Select…</option>
            <option value="M" <?php echo $sex==='M'?'selected':''; ?>>Male</option>
            <option value="F" <?php echo $sex==='F'?'selected':''; ?>>Female</option>
            <option value="Other" <?php echo $sex==='Other'?'selected':''; ?>>Other</option>
          </select>
        </div>
        <div class="buttons">
          <button class="btn primary" type="submit" id="btn-signin">
            <i class="fa-solid fa-right-to-bracket"></i> Sign In
          </button>
          <button class="btn ghost" type="submit" id="btn-create">
            <i class="fa-solid fa-user-plus"></i> Create Account
          </button>
        </div>
      </form>

      <div class="divider"><span>Or</span></div>

      <div class="create-row" id="create-alt" role="button" tabindex="0" aria-label="Create Account alternative">
        <img src="assets/images/google.png" alt="Google icon" class="mini-icon" />
        <img src="assets/images/gmail.png" alt="Gmail icon" class="mini-icon" />
        <span class="muted">Create Account</span>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="muted small"><i class="fa-solid fa-envelope"></i> copyright@gmail.com</div>
  </footer>

  <script>
    (function() {
      const form = document.getElementById('auth-form');
      const intent = document.getElementById('intent');
      const btnSignin = document.getElementById('btn-signin');
      const btnCreate = document.getElementById('btn-create');
      const alt = document.getElementById('create-alt');

      if (btnSignin) {
        btnSignin.addEventListener('click', () => { if (intent) intent.value = 'signin'; });
      }
      if (btnCreate) {
        btnCreate.addEventListener('click', () => { if (intent) intent.value = 'create'; });
      }

      if (alt && form) {
        const submitCreate = () => {
          if (intent) intent.value = 'create';
          form.requestSubmit ? form.requestSubmit() : form.submit();
        };
        alt.addEventListener('click', submitCreate);
        alt.addEventListener('keypress', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); submitCreate(); }});
      }
    })();
  </script>
</body>
</html>
