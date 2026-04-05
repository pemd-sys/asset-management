<?php
/*
  calendar.php
  - Serves the calendar page
  - Also handles AJAX API:
      ?action=get_events&year=YYYY&month=M   (GET)  -> returns JSON events intersecting month
      (POST) action=add_event                 (POST) -> add new event (title,start,end,color,recurrence,recurrence_end)
  - Uses PDO. Configure DB settings below.
*/

/* ------------------ CONFIG: set your DB here ------------------ */
$dbHost = '127.0.0.1';
$dbName = 'calendar_db';
$dbUser = 'remote_user';
$dbPass = 'Q<@|NxQ1K';
$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";

/* timezone handling (change if needed) */
$defaultTz = new DateTimeZone('Europe/London');
/* -------------------------------------------------------------- */

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]);
    exit;
}

/* ------------------ Helper functions ------------------ */

/**
 * Expand recurring events and long events into event instances that intersect a date range.
 * Input: event rows from DB (assoc array)
 * Output: array of event instances with keys: id,title,start (YYYY-MM-DD), end (YYYY-MM-DD), color
 */
function expand_event_instances(array $eventRow, DateTime $rangeStart, DateTime $rangeEnd, DateTimeZone $tz): array {
    $instances = [];

    // parse original start/end
    $evStart = new DateTime($eventRow['start_date'], $tz);
    $evStart->setTime(0,0,0);
    $evEnd = new DateTime($eventRow['end_date'], $tz);
    $evEnd->setTime(23,59,59);

    $recurrence = $eventRow['recurrence'] ?? 'none';
    $recurrenceEnd = $eventRow['recurrence_end'] ? new DateTime($eventRow['recurrence_end'], $tz) : null;

    // helper: add instance if it overlaps range
    $addIfOverlap = function(DateTime $s, DateTime $e) use (&$instances, $eventRow, $rangeStart, $rangeEnd) {
        if ($e < $rangeStart || $s > $rangeEnd) return;
        $instances[] = [
            'id' => (int)$eventRow['id'],
            'title' => $eventRow['title'],
            'start' => $s->format('Y-m-d'),
            'end'   => $e->format('Y-m-d'),
            'color' => $eventRow['color'] ?? 'default'
        ];
    };

    // Non-recurring: just add the original event if overlaps
    if ($recurrence === 'none' || empty($recurrence)) {
        $addIfOverlap($evStart, $evEnd);
        return $instances;
    }

    // recurring: create occurrences between rangeStart and rangeEnd
    // We'll generate occurrences by moving an occurrence anchor and applying the original span length
    $spanInterval = $evStart->diff($evEnd); // duration
    // start generating from either event start or rangeStart earlier enough
    $cursor = clone $evStart;

    // If the cursor is far before rangeStart, advance it quickly depending on recurrence type
    if ($cursor < $rangeStart) {
        switch ($recurrence) {
            case 'daily':
                $days = (int)$cursor->diff($rangeStart)->format('%a');
                $cursor->modify("+{$days} days");
                break;
            case 'weekly':
                $weeks = floor($cursor->diff($rangeStart)->format('%a') / 7);
                $cursor->modify("+{$weeks} weeks");
                break;
            case 'monthly':
                $months = ($rangeStart->format('Y') - $cursor->format('Y')) * 12 + ($rangeStart->format('n') - $cursor->format('n'));
                if ($months > 0) $cursor->modify("+{$months} months");
                break;
            case 'yearly':
                $years = (int)$rangeStart->format('Y') - (int)$cursor->format('Y');
                if ($years > 0) $cursor->modify("+{$years} years");
                break;
            default:
                break;
        }
    }

    // generate until beyond rangeEnd or recurrenceEnd
    while ($cursor <= $rangeEnd) {
        if ($recurrenceEnd && $cursor > $recurrenceEnd) break;

        $occStart = clone $cursor;
        $occEnd = (clone $occStart)->add($spanInterval);
        $addIfOverlap($occStart, $occEnd);

        // advance cursor
        switch ($recurrence) {
            case 'daily': $cursor->modify('+1 day'); break;
            case 'weekly': $cursor->modify('+1 week'); break;
            case 'monthly': $cursor->modify('+1 month'); break;
            case 'yearly': $cursor->modify('+1 year'); break;
            default: $cursor = clone $rangeEnd; break 2;
        }
    }

    return $instances;
}

/* ------------------ API handlers ------------------ */

$action = $_REQUEST['action'] ?? null;

if ($action === 'get_events') {
    // GET params: year, month
    $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');

    // range: start = first day displayed in calendar grid (start on Sunday), end = last day that fills grid (Saturday)
    $firstOfMonth = new DateTime(sprintf('%04d-%02d-01', $year, $month), $defaultTz);
    $startGrid = clone $firstOfMonth;
    $startGrid->modify('Sunday this week');
    if ($startGrid > $firstOfMonth) $startGrid->modify('-7 days');

    $endOfMonth = clone $firstOfMonth;
    $endOfMonth->modify('last day of this month');
    $endGrid = clone $endOfMonth;
    $endGrid->modify('Saturday this week');
    if ($endGrid < $endOfMonth) $endGrid->modify('+7 days');

    // load events that might intersect the range (by simple bounding query)
    // We must fetch all DB events that start <= endGrid AND end >= startGrid OR recurring events (recurrence != 'none')
    $sql = "SELECT * FROM events WHERE (start_date <= :endGrid AND end_date >= :startGrid) OR recurrence <> 'none'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':endGrid' => $endGrid->format('Y-m-d'),
        ':startGrid' => $startGrid->format('Y-m-d')
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $instances = [];
    foreach ($rows as $row) {
        $expanded = expand_event_instances($row, $startGrid, $endGrid, $defaultTz);
        foreach ($expanded as $inst) $instances[] = $inst;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'events' => $instances]);
    exit;
}

if ($action === 'add_event' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST: title, start (YYYY-MM-DD), end (YYYY-MM-DD) optional, color, recurrence, recurrence_end optional
    $title = trim($_POST['title'] ?? '');
    $start = $_POST['start'] ?? '';
    $end = $_POST['end'] ?? $start;
    $color = $_POST['color'] ?? 'default';
    $recurrence = $_POST['recurrence'] ?? 'none'; // none,daily,weekly,monthly,yearly
    $recurrence_end = $_POST['recurrence_end'] ?? null;


    if (!$title || !$start) {
        header('Content-Type: application/json', true, 400);
        echo json_encode(['error' => 'Missing title or start date']);
        exit;
    }

    // insert
    $sql = "INSERT INTO events (title, start_date, end_date, color, recurrence, recurrence_end, created_at)
            VALUES (:title, :start, :end, :color, :recurrence, :recurrence_end, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $title,
        ':start' => $start,
        ':end' => $end,
        ':color' => $color,
        ':recurrence' => $recurrence,
        ':recurrence_end' => $recurrence_end
    ]);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);
    exit;
}

/* ------------------ If no action -> render HTML page ------------------ */
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>My Calendar</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="calendar_3.css">
</head>
<body>
  <!-- Place this calendar inside any container on your page -->
  <div id="calendar-root">
    <div class="my-calendar" id="my-calendar" aria-live="polite"></div>
  </div>


  <script>
  // small config passed to JS: API endpoint (same file)
  const CAL_API = '<?php echo basename(__FILE__); ?>';
  </script>
  <script src="calendar_3.js"></script>
  
  <!-- Minimalist modal for adding events -->
<div id="new-event-modal" class="mycal-modal" style="display:none;">
  <div class="mycal-modal-content">
    <h3 id="event-modal-title">Add New Event</h3>
    <form id="new-event-form">
      <label>Event Name:</label>
      <input type="text" name="title" required>

      <label>Start Date:</label>
      <input type="date" name="start" required>

      <label>Number of Days:</label>
      <input type="number" name="days" min="1" value="1">

	<label class="checkbox-label">
	  <input type="checkbox" id="recurring-toggle" name="recurring_enabled">
  	  <span>Recurring Event</span>
	</label>

	<div id="recurring-options" class = "mycal-modal-recurring-options" style="display:none;">
	  <label>Recurring Type:</label>
	  <select name="recurring">
	    <option value="weekly">Weekly</option>
	    <option value="monthly">Monthly</option>
	  </select>

	  <label>Recurring End Date:</label>
	  <input type="date" name="recurring_end">
	</div>

      <label>Colour:</label>
      <input type="color" name="color" value="#3498db">

      <label>Notes:</label>
      <textarea name="notes"></textarea>

      <div class="mycal-modal-actions">
        <button type="submit">OK</button>
        <button type="button" onclick="closeNewEventModal()">Cancel</button>
        <button type="button" id="event-delete-btn" onclick="deleteEvent()">Delete</button>
      </div>
    </form>
  </div>
</div>


</body>

</html>

