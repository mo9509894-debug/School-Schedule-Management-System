<?php
// =====================================================================
// الصفحة الرئيسية (صفحة واحدة مقسمة لأقسام، بتتبدل بالـ JS)
// =====================================================================
// ملفات البيانات: كل واحد في ملف لوحده جوه فولدر data
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
// Opening the Admin login screen always starts a fresh login attempt.
if (($_GET['start'] ?? '') === 'adminLogin' || isset($_GET['error'])) {
    unset($_SESSION['is_admin']);
    $_SESSION['active_role'] = 'admin';
}
include 'data/grades.php';
include 'data/classes.php';
include 'data/days.php';
include 'data/sessions.php';

// Demo CRUD data lives in a project file so the admin handlers stay separate from index.php.
$demoStorePath = __DIR__ . '/data/demo_sessions.json';
$demoStore = ['added' => [], 'overrides' => [], 'deleted' => []];
if (is_file($demoStorePath)) {
    $decodedDemoStore = json_decode((string)file_get_contents($demoStorePath), true);
    if (is_array($decodedDemoStore)) $demoStore = array_replace($demoStore, $decodedDemoStore);
}
foreach ($sessions as $gk => &$byClass) {
    foreach ($byClass as $ck => &$byDay) {
        foreach ($byDay as $dk => &$rows) {
            $rows = array_values(array_filter($rows, function ($row) use ($demoStore) {
                return !in_array((string)$row['id'], array_map('strval', $demoStore['deleted'] ?? []), true);
            }));
            foreach ($rows as &$row) {
                $key = (string)$row['id'];
                if (isset($demoStore['overrides'][$key]) && is_array($demoStore['overrides'][$key])) {
                    $override = $demoStore['overrides'][$key];
                    $row['name'] = (string)($override['subject'] ?? $row['name']);
                    $row['start'] = (string)($override['start_time'] ?? $row['start']);
                    $row['end'] = (string)($override['end_time'] ?? $row['end']);
                    $row['type'] = (string)($override['type'] ?? $row['type']);
                    $row['desc'] = (string)($override['description'] ?? $row['desc']);
                    $row['teacher'] = (string)($override['teacher'] ?? $row['teacher'] ?? '');
                }
            }
            unset($row);
        }
        unset($rows);
    }
    unset($byDay);
}
unset($byClass);
foreach (($demoStore['added'] ?? []) as $demoRow) {
    if (!is_array($demoRow)) continue;
    $g = (string)($demoRow['grade'] ?? ''); $c = (string)($demoRow['class_name'] ?? ''); $d = (string)($demoRow['day'] ?? '');
    if ($g === '' || $c === '' || $d === '' || !isset($sessions[$g][$c])) continue;
    $sessions[$g][$c][$d] ??= [];
    if (in_array((string)($demoRow['id'] ?? ''), array_map(fn($r) => (string)$r['id'], $sessions[$g][$c][$d]), true)) continue;
    $sessions[$g][$c][$d][] = [
        'id' => (string)($demoRow['id'] ?? ('demo-' . uniqid())),
        'name' => (string)($demoRow['subject'] ?? ''), 'start' => (string)($demoRow['start_time'] ?? ''),
        'end' => (string)($demoRow['end_time'] ?? ''), 'type' => (string)($demoRow['type'] ?? 'class'),
        'desc' => (string)($demoRow['description'] ?? ''), 'teacher' => (string)($demoRow['teacher'] ?? '')
    ];
}

// Temporary demo admin mode is controlled by the login session.
$isAdmin = !empty($_SESSION['is_admin']);
$activeRole = $isAdmin ? 'admin' : (string)($_SESSION['active_role'] ?? '');

// ---- نحدد أنهي قسم يظهر أول ما الصفحة تفتح ----
$start = 'welcome';
// The grades landing URL is reserved for a successful admin login. Student navigation is client-side.
if (($_GET['start'] ?? '') === 'grades' && ($isAdmin || $activeRole === 'student')) $start = 'grades';
if (($_GET['start'] ?? '') === 'adminLogin') $start = 'adminLogin';
if (isset($_GET['error'])) $start = 'adminLogin';

// بعد الحفظ أو الحذف بنرجع لنفس الجدول (من admin/edit.php و admin/delete.php)
$initial = null;
$g = $_GET['grade'] ?? ''; $c = $_GET['class'] ?? ''; $d = $_GET['day'] ?? '';
if ($g !== '' && in_array($g, array_column($grades, 'grade'), true)
    && in_array($c, array_column($classes, 'class'), true)
    && in_array($d, array_column($days, 'day'), true)) {
    $start = 'schedule';
    $initial = ['grade' => $g, 'cls' => $c, 'day' => $d];
}

include 'includes/header.php';

// شريط العنوان اللي بيظهر فوق كل قسم داخلي
function bar($title, $sub = '') {
    global $isAdmin, $activeRole; ?>
    <div class="app-bar py-3 px-3">
      <div class="wrap d-flex align-items-center gap-3">
        <button class="btn btn-back p-0" data-back aria-label="Back"><i class="bi bi-arrow-left"></i></button>
        <div class="flex-grow-1">
          <div class="fs-4 fw-bold lh-1"><?= $title ?></div>
          <?php if ($sub) { ?><small><?= $sub ?></small><?php } ?>
        </div>
        <span class="role-chip" data-role-chip <?= $activeRole === '' ? 'hidden' : '' ?>><?= $activeRole === 'admin' ? 'Admin' : ($activeRole === 'student' ? 'Student' : '') ?></span><?php if ($isAdmin) { ?><a class="btn btn-logout ms-1" data-logout href="admin/logout.php"><i class="bi bi-box-arrow-right"></i> Log out</a><?php } ?>
      </div>
    </div>
<?php } ?>

<!-- 1) الصفحة الأولى: الترحيب -->
<section id="welcome" class="view <?= $start === 'welcome' ? 'active' : '' ?>">
  <div class="hero"><div class="hero-inner">
    <a class="tag location-link" href="https://maps.app.goo.gl/KPMGiW94bD8YdouNA" target="_blank" rel="noopener noreferrer" aria-label="Open WE School location in Google Maps"><i class="bi bi-geo-alt-fill"></i> Assiut <i class="bi bi-box-arrow-up-right"></i></a>
    <h1>WE Schools of<br>Applied Technology</h1>
    <p class="sub">Your class schedule, always up to date.</p>
    <div class="school-card"><img src="assets/img/school.jpg" alt="WE School of Applied Technology building in Assiut"></div>
    <div class="facts">
      <span><i class="bi bi-mortarboard"></i> 3 grades</span>
      <span><i class="bi bi-people"></i> 4 classes in each grade</span>
      <span><i class="bi bi-calendar3"></i> 5 school days a week</span>
    </div>
    <button class="btn-go" data-go="role">Continue <i class="bi bi-arrow-right"></i></button>
    <p class="credit">Image by Mohamed Hassan and Sherhan</p>
  </div></div>
</section>

<!-- 2) اختيار: طالب ولا أدمين -->
<section id="role" class="view <?= $start === 'role' ? 'active' : '' ?>">
  <?php bar('Welcome', 'Choose how you want to continue'); ?>
  <div class="wrap p-3 pt-4 d-grid gap-3">
    <h2 class="h4 fw-bold mb-0">Who are you?</h2>
    <!-- الطالب: بيروح على اختيار الصف -->
    <button class="choice c-indigo" data-go="grades">
      <span class="ico"><i class="bi bi-mortarboard"></i></span>
      <span><span class="t d-block">Student</span><span class="s">View your class schedule</span></span>
      <span class="go"><i class="bi bi-arrow-right"></i></span>
    </button>
    <!-- الأدمين: لو مسجّل دخول بيعدي على طول، لو لأ بيروح لصفحة الباسورد -->
    <button class="choice c-violet" data-go="adminLogin">
      <span class="ico"><i class="bi bi-shield-lock"></i></span>
      <span><span class="t d-block">Admin</span><span class="s">Manage and edit the schedule</span></span>
      <span class="go"><i class="bi bi-arrow-right"></i></span>
    </button>
  </div>
</section>

<!-- 3) باسورد الأدمين: الفورم بيتبعت لـ admin/login.php -->
<section id="adminLogin" class="view <?= $start === 'adminLogin' ? 'active' : '' ?>">
  <?php bar('Admin login', 'Enter the admin password'); ?>
  <div class="wrap p-3 pt-4">
    <form method="POST" action="admin/login.php" class="bg-white rounded-4 border p-4 d-grid gap-3">
      <div class="text-center fs-1 text-primary"><i class="bi bi-shield-lock"></i></div>
      <?php if (isset($_GET['error'])) { ?>
        <div class="alert alert-danger mb-0">Wrong password. Try again.</div>
      <?php } ?>
      <div>
        <label class="form-label fw-semibold">Password</label>
        <input type="password" name="password" class="form-control form-control-lg" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary btn-lg rounded-pill">Log in</button>
      <small class="text-secondary text-center">Temporary demo password: <strong>WAdmin</strong></small>
    </form>
  </div>
</section>

<!-- 4) اختيار الصف -->
<section id="grades" class="view <?= $start === 'grades' ? 'active' : '' ?>">
  <?php bar('Class Schedule'); ?>
  <div class="wrap p-3 pt-4 d-grid gap-3">
    <div><h2 class="h4 fw-bold">Select Grade</h2><p class="text-secondary mb-0">Choose a grade to view the weekly schedule</p></div>
    <?php foreach ($grades as $row) { ?>
    <button class="choice c-<?= $row['color'] ?>" data-grade="<?= $row['grade'] ?>" data-go="classes">
      <span class="ico"><i class="bi bi-mortarboard"></i></span>
      <span><span class="t d-block">Grade <?= $row['grade'] ?></span><span class="s"><?= $row['students'] ?> Students</span></span>
      <span class="go"><i class="bi bi-arrow-right"></i></span>
    </button>
    <?php } ?>
  </div>
</section>

<!-- 5) اختيار الفصل -->
<section id="classes" class="view">
  <?php bar('<span class="grade-label"></span>', 'Select your class'); ?>
  <div class="wrap p-3 pt-4 d-grid gap-3">
    <div><h2 class="h4 fw-bold">Available Classes</h2><p class="text-secondary mb-0">Choose your class to view the schedule</p></div>
    <?php foreach ($classes as $row) { ?>
    <button class="choice c-<?= $row['color'] ?>" data-cls="<?= $row['class'] ?>" data-go="days">
      <span class="ico"><i class="bi bi-people"></i></span>
      <span><span class="t d-block">Class <?= $row['class'] ?></span><span class="s"><?= $row['students'] ?> Students</span></span>
      <span class="go"><i class="bi bi-arrow-right"></i></span>
    </button>
    <?php } ?>
  </div>
</section>

<!-- 6) اختيار اليوم -->
<section id="days" class="view">
  <?php bar('<span class="class-label"></span>', 'Select a day'); ?>
  <div class="wrap p-3 pt-4 d-grid gap-3">
    <div><h2 class="h4 fw-bold">Weekly Schedule</h2><p class="text-secondary mb-0">Choose a day to view the class schedule</p></div>
    <?php foreach ($days as $row) { ?>
    <button class="choice c-<?= $row['color'] ?>" data-day="<?= $row['day'] ?>" data-go="schedule">
      <span class="ico"><i class="bi bi-calendar3"></i></span>
      <span><span class="t d-block"><?= $row['day'] ?></span><span class="s"><?= $row['date'] ?></span></span>
      <span class="go"><i class="bi bi-arrow-right"></i></span>
    </button>
    <?php } ?>
  </div>
</section>

<!-- 7) جدول اليوم. كل الحصص موجودة في الصفحة والـ JS بيظهر اللي يخص اختيار الطالب بس -->
<section id="schedule" class="view <?= $start === 'schedule' ? 'active' : '' ?>">
  <?php bar('<span class="day-label"></span>', ''); ?>
  <div class="wrap p-3 pt-4 d-grid gap-3">
    <?php if ($isAdmin) { /* زر الإضافة بيظهر للأدمين بس */ ?>
      <button class="btn btn-primary rounded-pill" id="addSessionBtn"><i class="bi bi-plus-lg"></i> Add session</button>
    <?php } ?>
    <div id="emptySchedule" class="alert alert-info d-none mb-0">No sessions for this day yet.</div>
    <div id="sessionList" class="d-grid gap-3">

    <?php /* بنلف على الصف ← الفصل ← اليوم ← الحصص من ملف data/sessions.php */
    foreach ($sessions as $gk => $byClass) { foreach ($byClass as $ck => $byDay) { foreach ($byDay as $dk => $rows) { foreach ($rows as $s) {
        $s['grade'] = $gk; $s['class'] = $ck; $s['day'] = $dk;
        $brk = strtolower(trim((string)($s['type'] ?? 'class'))) === 'break'; ?>
    <!-- الـ data-* دي بيقراها الـ JS للفلترة ولملء نافذة التعديل -->
    <article class="session d-none <?= $brk ? 'is-break' : '' ?>"
             data-id="<?= htmlspecialchars((string)$s['id'], ENT_QUOTES, 'UTF-8') ?>" data-grade="<?= htmlspecialchars((string)$s['grade'], ENT_QUOTES, 'UTF-8') ?>" data-class="<?= htmlspecialchars((string)$s['class'], ENT_QUOTES, 'UTF-8') ?>" data-day="<?= htmlspecialchars((string)$s['day'], ENT_QUOTES, 'UTF-8') ?>"
             data-name="<?= htmlspecialchars((string)$s['name'], ENT_QUOTES, 'UTF-8') ?>" data-start="<?= htmlspecialchars((string)$s['start'], ENT_QUOTES, 'UTF-8') ?>" data-end="<?= htmlspecialchars((string)$s['end'], ENT_QUOTES, 'UTF-8') ?>"
             data-type="<?= htmlspecialchars((string)($s['type'] ?? 'class'), ENT_QUOTES, 'UTF-8') ?>" data-desc="<?= htmlspecialchars($s['desc'], ENT_QUOTES, 'UTF-8') ?>" data-teacher="<?= htmlspecialchars($s['teacher'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="d-flex align-items-center gap-3">
        <span class="ico"><i class="bi <?= $brk ? 'bi-cup-hot' : 'bi-clock' ?>"></i></span>
        <div class="flex-grow-1">
          <div class="name"><?= htmlspecialchars((string)$s['name'], ENT_QUOTES, 'UTF-8') ?></div>
          <div class="time"><?= htmlspecialchars((string)$s['start'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars((string)$s['end'], ENT_QUOTES, 'UTF-8') ?></div>
          <?php if (!empty($s['teacher'])) { ?><div class="teacher"><i class="bi bi-person"></i> Teacher: <?= htmlspecialchars($s['teacher'], ENT_QUOTES, 'UTF-8') ?></div><?php } ?>
        </div>
        <span class="badge-type align-self-start"><?= htmlspecialchars((string)($s['type'] ?? 'class'), ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <?php if ($s['desc']) { /* الباراجراف: اللي هيتدرّس في الحصة */ ?><p class="desc"><?= htmlspecialchars((string)$s['desc'], ENT_QUOTES, 'UTF-8') ?></p><?php } ?>

      <?php if ($isAdmin) { /* أزرار التعديل والحذف للأدمين بس */ ?>
      <div class="d-flex gap-2 mt-3">
        <!-- التعديل: بيفتح النافذة، والحفظ بيروح لـ admin/edit.php -->
        <button type="button" class="btn btn-warning btn-sm edit-session"><i class="bi bi-pencil"></i> Edit</button>
        <!-- الحذف: فورم POST بيروح لـ admin/delete.php -->
        <form method="POST" action="admin/delete.php" onsubmit="return confirm('Delete this session?')">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
          <input type="hidden" name="grade" value="<?= $s['grade'] ?>">
          <input type="hidden" name="class_name" value="<?= $s['class'] ?>">
          <input type="hidden" name="day" value="<?= $s['day'] ?>">
          <button type="submit" class="btn btn-danger btn-sm delete-session"><i class="bi bi-trash"></i> Delete</button>
        </form>
      </div>
      <?php } ?>
    </article>
    <?php } } } } ?>
    </div><!-- /#sessionList -->
  </div>
</section>

<?php if ($isAdmin) { /* نافذة الإضافة والتعديل للأدمين بس. بتتبعت لـ admin/edit.php */ ?>
<div class="modal fade" id="sessionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="admin/insert.php" id="sessionForm" class="modal-content rounded-4">
      <div class="modal-header"><h5 class="modal-title" id="modalTitle">Edit session</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <!-- session_id فاضي = حصة جديدة. الحقول دي بيملاها الـ JS -->
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="session_id">
        <input type="hidden" name="grade">
        <input type="hidden" name="class_name">
        <input type="hidden" name="day">
        <div class="mb-3"><label class="form-label">Subject</label>
          <input type="text" class="form-control" name="subject" placeholder="Enter subject name" required>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6"><label class="form-label">Start</label><input type="text" class="form-control" name="start_time" placeholder="8:00 AM" required></div>
          <div class="col-6"><label class="form-label">End</label><input type="text" class="form-control" name="end_time" placeholder="9:00 AM" required></div>
        </div>
        <div class="mb-3"><label class="form-label">Type</label>
          <input type="text" class="form-control" name="type" placeholder="class, break, or lab" value="class" required>
          <small class="text-secondary">Write the type as needed, for example: class, break, or lab.</small>
        </div>
        <div class="mb-3"><label class="form-label">Teacher name</label><input type="text" class="form-control" name="teacher" placeholder="Enter teacher name"></div>
        <div class="mb-0"><label class="form-label">What will be taught</label><textarea class="form-control" name="description" rows="3"></textarea></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
    </form>
  </div>
</div>
<?php } ?>

<!-- بنبعت للـ JS الجدول اللي لازم يتفتح (بعد الحفظ أو الحذف) -->
<script>window.INITIAL = <?= json_encode($initial) ?>; window.ACTIVE_ROLE = <?= json_encode($activeRole) ?>; window.IS_ADMIN = <?= $isAdmin ? 'true' : 'false' ?>;</script>
<?php include 'includes/footer.php'; ?>
