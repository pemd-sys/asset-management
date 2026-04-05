<?php
/**
 * modern-calendar.php
 *
 * Minimal, modern-looking, accessible month calendar component.
 * - Uses DateTime/DateInterval with timezone support
 * - Navigation (prev/next month + month/year selects)
 * - Multi-day events (start/end) and events that span months
 * - Overlapping event stacking and continuation indicators
 * - Scoped CSS (modern-calendar) so it won't leak to other page elements
 *
 * Usage: place this file and calendar CSS in a webroot and open it via PHP.
 */

/* -------------------------
   Configuration + helpers
   ------------------------- */
date_default_timezone_set('Europe/London'); // default timezone (changeable)

class ModernCalendar {
    public DateTime $viewDate;              // Date representing the month being viewed (first day)
    public DateTimeZone $tz;
    private array $events = [];            // array of events (associative arrays)
    public int $maxEventRows = 3;          // how many stacked event rows to show before "+N more"

    public function __construct(string $date = 'now', ?string $timezone = null) {
        $this->tz = new DateTimeZone($timezone ?? date_default_timezone_get());
        $this->viewDate = new DateTime($date, $this->tz);
        // normalize to first day of month at 00:00
        $this->viewDate->modify('first day of this month')->setTime(0,0,0);
    }

    /**
     * Add an event
     * $title: string
     * $start: date string parseable by DateTime
     * $end: optional date string (inclusive end day). If null, event is single-day.
     * $color: optional CSS color class name (e.g., 'red', 'blue', 'green')
     * $meta: optional array for extra info (e.g., 'aria-label' or 'time')
     */
    public function add_event(string $title, string $start, ?string $end = null, string $color = 'default', array $meta = []) {
        $startDt = new DateTime($start, $this->tz);
        // Ensure start time is at midnight (we show day-level calendar)
        $startDt->setTime(0,0,0);
        $endDt = $end ? new DateTime($end, $this->tz) : clone $startDt;
        $endDt->setTime(23,59,59); // inclusive end-of-day

        // store canonical event
        $this->events[] = [
            'title' => $title,
            'start' => $startDt,
            'end'   => $endDt,
            'color' => $color,
            'meta'  => $meta
        ];
    }

    /**
     * Get events that intersect with the given day (DateTime at midnight)
     * Returns array of events with additional flags: continues_left, continues_right
     */
    private function events_for_day(DateTime $day): array {
        $dayStart = clone $day;
        $dayStart->setTime(0,0,0);
        $dayEnd = clone $day;
        $dayEnd->setTime(23,59,59);

        $list = [];
        foreach ($this->events as $ev) {
            if ($ev['start'] <= $dayEnd && $ev['end'] >= $dayStart) {
                $continues_left  = $ev['start'] < $dayStart;
                $continues_right = $ev['end'] > $dayEnd;
                $list[] = array_merge($ev, [
                    'continues_left' => $continues_left,
                    'continues_right'=> $continues_right,
                ]);
            }
        }
        return $list;
    }

    /**
     * Render the calendar HTML for current $this->viewDate
     */
    public function render(): string {
        $firstOfMonth = clone $this->viewDate;
        $year = (int)$firstOfMonth->format('Y');
        $month = (int)$firstOfMonth->format('n');

        // compute day grid: start from the Sunday (or Monday if you prefer) of the week that contains first of month
        $startGrid = clone $firstOfMonth;
        $startGrid->modify('Sunday this week'); // aligns start to Sunday (change if you want Mon)
        // if firstOfMonth is earlier than Sunday this week (i.e., first is Sunday), this gives that same day
        if ($startGrid > $firstOfMonth) {
            $startGrid->modify('-7 days');
        }

        // end grid: last Saturday that contains last day of the month
        $endOfMonth = clone $firstOfMonth;
        $endOfMonth->modify('last day of this month');
        $endGrid = clone $endOfMonth;
        $endGrid->modify('Saturday this week');
        if ($endGrid < $endOfMonth) {
            $endGrid->modify('+7 days');
        }

        // Build HTML
        ob_start();
        $monthLabel = $firstOfMonth->format('F Y');
        ?>
        <div class="modern-calendar" aria-labelledby="mc-heading">
            <div class="modern-calendar__controls">
                <form method="get" class="modern-calendar__form" aria-label="Calendar navigation">
                    <!-- Prev month -->
                    <?php
                        // previous month link params
                        $prev = clone $firstOfMonth; $prev->modify('-1 month');
                        $next = clone $firstOfMonth; $next->modify('+1 month');
                    ?>
                    <a href="?y=<?=$prev->format('Y')?>&m=<?=$prev->format('n')?>" class="modern-calendar__btn" aria-label="Previous month">&larr;</a>

                    <!-- Month select -->
                    <label class="sr-only" for="mc-month">Month</label>
                    <select id="mc-month" name="m" class="modern-calendar__select" onchange="this.form.submit()">
                        <?php for($i=1;$i<=12;$i++): $dt = DateTime::createFromFormat('!n', $i, $this->tz); ?>
                            <option value="<?=$i?>" <?=($i== $month ? 'selected' : '')?>><?=$dt->format('F')?></option>
                        <?php endfor; ?>
                    </select>

                    <!-- Year select (range +/- 5 years) -->
                    <label class="sr-only" for="mc-year">Year</label>
                    <select id="mc-year" name="y" class="modern-calendar__select" onchange="this.form.submit()">
                        <?php for($y = $year - 5; $y <= $year + 5; $y++): ?>
                            <option value="<?=$y?>" <?=($y== $year ? 'selected' : '')?>><?=$y?></option>
                        <?php endfor; ?>
                    </select>

                    <a href="?y=<?=$next->format('Y')?>&m=<?=$next->format('n')?>" class="modern-calendar__btn" aria-label="Next month">&rarr;</a>

                    <!-- timezone selector (simple) -->
                    <label class="sr-only" for="mc-tz">Timezone</label>
                    <select id="mc-tz" name="tz" class="modern-calendar__select modern-calendar__tz" onchange="this.form.submit()">
                        <?php
                        // keep list minimal for readability; expand as needed
                        $tzs = ['Europe/London','UTC','America/New_York','Europe/Berlin','Asia/Tokyo'];
                        $currentTz = $this->tz->getName();
                        foreach($tzs as $tzOpt): ?>
                            <option value="<?=htmlspecialchars($tzOpt)?>" <?=($tzOpt === $currentTz ? 'selected' : '')?>><?=$tzOpt?></option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <div class="modern-calendar__title" id="mc-heading" aria-hidden="true"><?=$monthLabel?></div>
            </div>

            <!-- calendar grid header (weekdays) -->
            <div class="modern-calendar__grid" role="grid" aria-labelledby="mc-heading">
                <div class="modern-calendar__weekdays" role="row">
                    <?php
                    $dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                    foreach($dayNames as $dn): ?>
                        <div class="modern-calendar__weekday" role="columnheader"><?=$dn?></div>
                    <?php endforeach; ?>
                </div>

                <!-- days: iterate from startGrid to endGrid -->
                <div class="modern-calendar__days" role="rowgroup">
                    <?php
                    $cursor = clone $startGrid;
                    while ($cursor <= $endGrid): 
                        $isCurrentMonth = ($cursor->format('n') == $month);
                        $classes = ['modern-calendar__day'];
                        if (!$isCurrentMonth) $classes[] = 'modern-calendar__day--muted';
                        if ($cursor->format('Y-m-d') === (new DateTime('now', $this->tz))->format('Y-m-d')) $classes[] = 'modern-calendar__day--today';
                        ?>
                        <div
                            role="gridcell"
                            tabindex="0"
                            aria-selected="<?= $cursor->format('n') == $month && $cursor->format('j') == $this->viewDate->format('j') ? 'true' : 'false' ?>"
                            aria-label="<?=htmlspecialchars($cursor->format('l, F j, Y'))?>"
                            class="<?=implode(' ', $classes)?>">
                            <div class="modern-calendar__date"><?= $cursor->format('j') ?></div>

                            <div class="modern-calendar__events" aria-hidden="false">
                                <?php
                                // get events intersecting this day
                                $events = $this->events_for_day($cursor);
                                // limit display rows; handle stacking with +n
                                if (count($events) > 0) {
                                    // sort events by start date (earlier first), then by duration desc (longer first)
                                    usort($events, function($a,$b){
                                        $cmp = $a['start'] <=> $b['start'];
                                        if ($cmp !== 0) return $cmp;
                                        $durA = $a['end']->getTimestamp() - $a['start']->getTimestamp();
                                        $durB = $b['end']->getTimestamp() - $b['start']->getTimestamp();
                                        return $durB <=> $durA;
                                    });

                                    $shown = 0;
                                    foreach($events as $ev) {
                                        if ($shown >= $this->maxEventRows) break;
                                        // event pill with continuation markers
                                        $contLeft = $ev['continues_left'] ? ' modern-calendar__ev--left' : '';
                                        $contRight = $ev['continues_right'] ? ' modern-calendar__ev--right' : '';
                                        $color = ' modern-calendar__ev--'.$ev['color'];
                                        $ariaTitle = htmlspecialchars($ev['title'] . ' — ' . $ev['start']->format('M j') . ($ev['end']->format('Y-m-d') != $ev['start']->format('Y-m-d') ? ' — ' . $ev['end']->format('M j') : ''));
                                        echo '<div class="modern-calendar__ev'.$color.$contLeft.$contRight.'" title="'.$ariaTitle.'">'.htmlspecialchars($ev['title']).'</div>';
                                        $shown++;
                                    }
                                    if (count($events) > $this->maxEventRows) {
                                        $more = count($events) - $this->maxEventRows;
                                        echo '<div class="modern-calendar__more" aria-hidden="false">+'.$more.' more</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                        $cursor->modify('+1 day');
                    endwhile;
                    ?>
                </div>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }
}

/* -------------------------
   Handle query parameters for navigation and timezone
   ------------------------- */
$year = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
$month = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
$tzParam = isset($_GET['tz']) ? $_GET['tz'] : date_default_timezone_get();

// Validate month/year
if ($month < 1) $month = 1;
if ($month > 12) $month = 12;
if ($year < 1970) $year = 1970;
if ($year > 2100) $year = 2100;

$viewDateStr = sprintf('%04d-%02d-01', $year, $month);
$calendar = new ModernCalendar($viewDateStr, $tzParam);

/* -------------------------
   Sample events — these demonstrate:
   - single-day event
   - multi-day event spanning across month boundary
   - overlapping events
   - events with colors and meta
   ------------------------- */
$today = new DateTime('now', $calendar->tz);
$Y = $today->format('Y');
$M = $today->format('m');

// Add sample events relative to current month for quick testing
$calendar->add_event('Team Meeting', "$Y-$M-03", null, 'blue', ['time'=>'10:00']);
$calendar->add_event('Project Deadline', "$Y-$M-12", null, 'red', ['time'=>'17:00']);
$calendar->add_event('Conference', "$Y-$M-20", "$Y-$M-22", 'green'); // 3-day event
// overlapping events
$calendar->add_event('1:1 Review', "$Y-$M-12", null, 'purple');
$calendar->add_event('Design Sync', "$Y-$M-12", "$Y-$M-13", 'orange');
// long event spanning end of month (test crossing month boundary)
$crossStart = (new DateTime("$Y-$M-28", $calendar->tz))->format('Y-m-d');
$crossEnd   = (new DateTime("$Y-$M-28", $calendar->tz))->modify('+6 days')->format('Y-m-d');
$calendar->add_event('Sprint', $crossStart, $crossEnd, 'teal');

/* -------------------------
   Output HTML page (links CSS below)
   ------------------------- */
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Modern Calendar</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="modern-calendar.css">
<style>
/* tiny utility: visually hidden label for accessibility */
.sr-only {
    position: absolute !important;
    height: 1px; width: 1px;
    overflow: hidden;
    clip: rect(1px, 1px, 1px, 1px);
    white-space: nowrap;
}

</style>
</head>
<body>
    <?php
        // update calendar timezone if user changed via select (we must reload events in new tz)
        if (isset($_GET['tz']) && $_GET['tz'] !== $calendar->tz->getName()) {
            // Recreate calendar with new tz and re-add events to keep times consistent
            $calendar = new ModernCalendar($viewDateStr, $_GET['tz']);
            // Re-add same sample events (for demo). In production you'd load events from DB with consistent timezone handling.
            $calendar->add_event('Team Meeting', "$Y-$M-03", null, 'blue', ['time'=>'10:00']);
            $calendar->add_event('Project Deadline', "$Y-$M-12", null, 'red', ['time'=>'17:00']);
            $calendar->add_event('Conference', "$Y-$M-20", "$Y-$M-22", 'green');
            $calendar->add_event('1:1 Review', "$Y-$M-12", null, 'purple');
            $calendar->add_event('Design Sync', "$Y-$M-12", "$Y-$M-13", 'orange');
            $calendar->add_event('Sprint', $crossStart, $crossEnd, 'teal');
        }

        echo $calendar->render();
    ?>
</body>
</html>

