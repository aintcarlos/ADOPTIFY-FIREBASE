<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (!empty($_POST['pets']) && is_array($_POST['pets'])) {
        foreach ($_POST['pets'] as $pid) {
            $pid  = intval($pid);
            $stmt = $conn->prepare("DELETE FROM adoption_list WHERE pet_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $pid, $user_id);
            $stmt->execute();
        }
    }
    header("Location: adoption-list.php?success=" . urlencode("Removed from list"));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Adoption List | Adoptify</title>
  <link rel="stylesheet" href="css/adoption-list.css">
  <style>
  .book-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }
  .book-modal-overlay.active { display: flex; }

  .book-modal {
    background: white;
    border-radius: 16px;
    padding: 30px;
    width: 480px;
    max-width: 95%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 12px 30px rgba(0,0,0,0.3);
    animation: popIn 0.25s ease;
  }

  @keyframes popIn {
    from { transform: scale(0.9); opacity: 0; }
    to   { transform: scale(1);   opacity: 1; }
  }

  .book-modal h3 { color: #0b6b2a; font-size: 20px; margin-bottom: 6px; text-align: center; }
  .book-modal label { display: block; font-weight: 600; color: #333; font-size: 13px; margin-top: 14px; margin-bottom: 4px; }
  .book-modal input[type="text"],
  .book-modal input[type="email"],
  .book-modal textarea {
    width: 100%; padding: 10px 12px; border-radius: 8px;
    border: 1px solid #ccc; font-size: 14px; font-family: sans-serif;
  }
  .book-modal input:focus, .book-modal textarea:focus {
    outline: none; border-color: #0b6b2a; box-shadow: 0 0 0 2px rgba(11,107,42,0.15);
  }
  .book-modal textarea { height: 70px; resize: vertical; }

  /* CALENDAR */
  .calendar-wrap { margin-top: 14px; }
  .calendar-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
  .calendar-header button { background: none; border: 1px solid #ccc; border-radius: 6px; padding: 3px 10px; cursor: pointer; font-size: 15px; }
  .calendar-header span { font-weight: 600; color: #0b6b2a; font-size: 14px; }
  .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px; text-align: center; }
  .cal-day-name { font-size: 11px; font-weight: 700; color: #888; padding: 4px 0; }
  .cal-day { padding: 7px 2px; border-radius: 6px; font-size: 13px; cursor: pointer; border: 1px solid transparent; transition: 0.15s; }
  .cal-day:hover:not(.booked):not(.past):not(.empty) { background: #e0f0e5; border-color: #0b6b2a; }
  .cal-day.selected { background: #0b6b2a; color: white; font-weight: bold; }
  .cal-day.booked { background: #fde8e8; color: #c0392b; font-weight: 600; cursor: not-allowed; text-decoration: line-through; }
  .cal-day.past { color: #ccc; cursor: not-allowed; }
  .cal-day.today { border: 2px solid #0b6b2a; font-weight: bold; }
  .cal-day.empty { cursor: default; }

  .cal-legend { display: flex; gap: 12px; margin-top: 8px; font-size: 11px; color: #555; flex-wrap: wrap; }
  .cal-legend span { display: flex; align-items: center; gap: 4px; }
  .legend-dot { width: 11px; height: 11px; border-radius: 3px; display: inline-block; }

  .modal-btns { display: flex; gap: 10px; margin-top: 18px; }
  .btn-book { flex: 1; padding: 11px; background: #0b6b2a; color: white; border: none; border-radius: 20px; font-weight: 700; font-size: 14px; cursor: pointer; transition: background 0.2s; }
  .btn-book:hover { background: #084d20; }
  .btn-book:disabled { background: #aaa; cursor: not-allowed; }
  .btn-cancel-modal { flex: 1; padding: 11px; background: white; color: #a10000; border: 2px solid #a10000; border-radius: 20px; font-weight: 700; font-size: 14px; cursor: pointer; transition: 0.2s; }
  .btn-cancel-modal:hover { background: #a10000; color: white; }

  .btn-meetup { padding: 7px 14px; background: #0b6b2a; color: white; border: none; border-radius: 18px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
  .btn-meetup:hover { background: #084d20; }
  </style>
</head>
<body>

<?php if (isset($_GET['success'])): ?>
<div class="toast success" id="toast">✅ <?php echo htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8'); ?></div>
<?php elseif (isset($_GET['error'])): ?>
<div class="toast error" id="toast">❌ <?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<header class="top-header">
  <div class="brand"><h1>ADOPTIFY</h1></div>
  <nav class="nav-actions">
    <a href="adopt-now.php" class="btn small">← Back to Pets</a>
    <a href="logout.php"    class="btn small">Logout</a>
  </nav>
</header>

<main class="page-grid">
  <aside class="sidebar">
    <div class="paw-decor">🐾</div>
    <p style="color:#555; text-align:center; font-size:13px; line-height:1.6;">Select pets and click Remove to delete them from your list.</p>
  </aside>

  <section class="content">
    <h2>My Adoption List</h2>

    <form method="POST">
      <table class="adopt-table">
        <thead>
          <tr>
            <th></th><th>Image</th><th>Name</th><th>Type</th><th>Age</th><th>Status</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
<?php
$stmt = $conn->prepare("
    SELECT pets.* FROM adoption_list
    JOIN pets ON pets.id = adoption_list.pet_id
    WHERE adoption_list.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
        $img    = htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8');
        $imgSrc = (strpos($img, 'http') === 0) ? $img : 'images/' . $img;
?>
          <tr>
            <td><input type="checkbox" name="pets[]" value="<?php echo intval($row['id']); ?>"></td>
            <td><img src="<?php echo $imgSrc; ?>" alt="pet"></td>
            <td><?php echo htmlspecialchars($row['name'],   ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($row['type'],   ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo intval($row['age']); ?></td>
            <td><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
              <button type="button" class="btn-meetup"
                data-petid="<?php echo intval($row['id']); ?>"
                data-petname="<?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>"
                onclick="openBooking(this)">
                📅 Book Meetup
              </button>
            </td>
          </tr>
<?php
    endwhile;
else:
?>
          <tr><td colspan="7" style="text-align:center; padding:20px; color:#777;">Your adoption list is empty.</td></tr>
<?php endif; ?>
        </tbody>
      </table>
      <br>
      <button name="delete" class="btn-danger" onclick="return confirm('Remove selected pets from your list?')">Remove Selected</button>
    </form>
  </section>
</main>

<!-- BOOKING MODAL -->
<div class="book-modal-overlay" id="bookModal">
  <div class="book-modal">
    <h3>📅 Book a Meetup</h3>
    <p style="text-align:center; color:#777; font-size:13px; margin-bottom:4px;">with <strong id="modalPetName"></strong></p>

    <form action="book_meetup.php" method="POST">
      <input type="hidden" name="pet_id" id="modalPetId">
      <input type="hidden" name="meetup_date" id="selectedDateInput">

      <label>Your Full Name</label>
      <input type="text" name="adopter_name" required placeholder="Enter your name">

      <label>Your Email</label>
      <input type="email" name="adopter_email" required placeholder="Enter your email">

      <div class="calendar-wrap">
        <label>Pick a Date <span style="color:red;">*</span>
          <span id="selectedDateLabel" style="color:#0b6b2a; font-weight:700; margin-left:8px;"></span>
        </label>

        <div class="calendar-header">
          <button type="button" onclick="prevMonth()">&#8592;</button>
          <span id="calMonthYear"></span>
          <button type="button" onclick="nextMonth()">&#8594;</button>
        </div>

        <div class="calendar-grid" id="calGrid">
          <div class="cal-day-name">Sun</div>
          <div class="cal-day-name">Mon</div>
          <div class="cal-day-name">Tue</div>
          <div class="cal-day-name">Wed</div>
          <div class="cal-day-name">Thu</div>
          <div class="cal-day-name">Fri</div>
          <div class="cal-day-name">Sat</div>
        </div>

        <div class="cal-legend">
          <span><div class="legend-dot" style="background:#fde8e8; border:1px solid #c0392b;"></div> Already Booked</span>
          <span><div class="legend-dot" style="background:#0b6b2a;"></div> Selected</span>
          <span><div class="legend-dot" style="background:#e0f0e5; border:1px solid #0b6b2a;"></div> Available</span>
        </div>
      </div>

      <label>Message to Owner (optional)</label>
      <textarea name="message" placeholder="Any notes for the owner..."></textarea>

      <div class="modal-btns">
        <button type="submit" class="btn-book" id="submitBooking" disabled>Confirm Booking</button>
        <button type="button" class="btn-cancel-modal" onclick="closeBooking()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<footer class="page-footer"><small>© 2026 Adoptify</small></footer>

<script>
const toast = document.getElementById('toast');
if (toast) setTimeout(() => toast.classList.add('hide'), 4000);

let currentPetId = null;
let bookedDates  = [];
let selectedDate = null;
let calYear      = new Date().getFullYear();
let calMonth     = new Date().getMonth();

function openBooking(btn) {
  currentPetId = btn.dataset.petid;
  document.getElementById('modalPetName').textContent    = btn.dataset.petname;
  document.getElementById('modalPetId').value            = currentPetId;
  document.getElementById('selectedDateInput').value     = '';
  document.getElementById('selectedDateLabel').textContent = '';
  document.getElementById('submitBooking').disabled      = true;
  selectedDate = null;
  calYear  = new Date().getFullYear();
  calMonth = new Date().getMonth();

  fetch('get_booked_dates.php?pet_id=' + currentPetId)
    .then(r => r.json())
    .then(dates => {
      bookedDates = dates;
      renderCalendar();
      document.getElementById('bookModal').classList.add('active');
    })
    .catch(() => {
      bookedDates = [];
      renderCalendar();
      document.getElementById('bookModal').classList.add('active');
    });
}

function closeBooking() {
  document.getElementById('bookModal').classList.remove('active');
}

document.getElementById('bookModal').addEventListener('click', function(e) {
  if (e.target === this) closeBooking();
});

function renderCalendar() {
  const monthNames = ['January','February','March','April','May','June',
                      'July','August','September','October','November','December'];
  document.getElementById('calMonthYear').textContent = monthNames[calMonth] + ' ' + calYear;

  const grid    = document.getElementById('calGrid');
  const headers = Array.from(grid.querySelectorAll('.cal-day-name'));
  grid.innerHTML = '';
  headers.forEach(h => grid.appendChild(h));

  const today      = new Date(); today.setHours(0,0,0,0);
  const firstDay   = new Date(calYear, calMonth, 1).getDay();
  const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();

  for (let i = 0; i < firstDay; i++) {
    const e = document.createElement('div');
    e.className = 'cal-day empty';
    grid.appendChild(e);
  }

  for (let d = 1; d <= daysInMonth; d++) {
    const cell    = document.createElement('div');
    const dateObj = new Date(calYear, calMonth, d);
    const dateStr = calYear + '-' + String(calMonth+1).padStart(2,'0') + '-' + String(d).padStart(2,'0');

    cell.className   = 'cal-day';
    cell.textContent = d;

    if (dateObj < today) {
      cell.classList.add('past');
    } else if (bookedDates.includes(dateStr)) {
      cell.classList.add('booked');
      cell.title = '❌ Already booked';
    } else {
      if (dateObj.toDateString() === today.toDateString()) cell.classList.add('today');
      if (selectedDate === dateStr) cell.classList.add('selected');
      cell.addEventListener('click', () => selectDate(dateStr, cell));
    }

    grid.appendChild(cell);
  }
}

function selectDate(dateStr, cell) {
  selectedDate = dateStr;
  document.getElementById('selectedDateInput').value = dateStr;
  document.getElementById('submitBooking').disabled  = false;

  // Show selected date nicely
  const d = new Date(dateStr + 'T00:00:00');
  document.getElementById('selectedDateLabel').textContent =
    '— ' + d.toLocaleDateString('en-US', { month:'long', day:'numeric', year:'numeric' });

  document.querySelectorAll('.cal-day.selected').forEach(c => c.classList.remove('selected'));
  cell.classList.add('selected');
}

function prevMonth() {
  calMonth--;
  if (calMonth < 0) { calMonth = 11; calYear--; }
  renderCalendar();
}

function nextMonth() {
  calMonth++;
  if (calMonth > 11) { calMonth = 0; calYear++; }
  renderCalendar();
}
</script>

</body>
</html>
