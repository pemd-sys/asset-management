<?php
/* Modernized Calendar Class */
class Calendar {
    private $active_year, $active_month, $active_day;
    private $events = [];

    public function __construct($date = null) {
        $this->active_year  = $date ? date('Y', strtotime($date)) : date('Y');
        $this->active_month = $date ? date('m', strtotime($date)) : date('m');
        $this->active_day   = $date ? date('d', strtotime($date)) : date('d');
    }

    // Add an event: text, date, number of days, optional color
    public function add_event($txt, $date, $days = 1, $color = '') {
        $this->events[] = [$txt, $date, $days, $color];
    }

    // Render the calendar
    public function __toString() {
        $num_days = date('t', strtotime($this->active_year . '-' . $this->active_month . '-1'));
        $num_days_last_month = date('j', strtotime('last day of previous month', strtotime($this->active_year . '-' . $this->active_month . '-1')));
        $days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        $first_day_of_week = array_search(date('D', strtotime($this->active_year . '-' . $this->active_month . '-1')), $days);

        $html = '<div class="calendar">';
        $html .= '<div class="calendar-header">' . date('F Y', strtotime($this->active_year . '-' . $this->active_month . '-1')) . '</div>';

        // Day name headers
        foreach ($days as $day) {
            $html .= '<div class="calendar-day-name">' . $day . '</div>';
        }

        // Previous month filler days
        for ($i = $first_day_of_week; $i > 0; $i--) {
            $html .= '<div class="calendar-day ignore">' . ($num_days_last_month - $i + 1) . '</div>';
        }

        // Current month days
        for ($d = 1; $d <= $num_days; $d++) {
            $class = 'calendar-day';
            if ($d == $this->active_day) {
                $class .= ' selected';
            }
            $html .= '<div class="' . $class . '"><span class="date-num">' . $d . '</span>';

            // Events for this day
            foreach ($this->events as $event) {
                for ($e = 0; $e < $event[2]; $e++) {
                    if (date('Y-m-d', strtotime($this->active_year . '-' . $this->active_month . '-' . $d)) ==
                        date('Y-m-d', strtotime($event[1] . ' + ' . $e . ' days'))) {
                        $color_class = $event[3] ? ' ' . $event[3] : '';
                        $html .= '<div class="calendar-event' . $color_class . '">' . htmlspecialchars($event[0]) . '</div>';
                    }
                }
            }
            $html .= '</div>';
        }

        // Next month filler days
        $total_cells = $first_day_of_week + $num_days;
        $next_days = (7 * ceil($total_cells / 7)) - $total_cells;
        for ($i = 1; $i <= $next_days; $i++) {
            $html .= '<div class="calendar-day ignore">' . $i . '</div>';
        }

        $html .= '</div>';
        return $html;
    }
}

/* Example usage */
$calendar = new Calendar(); // defaults to current month
$calendar->add_event('Team Meeting', date('Y-m-05'), 1, 'blue');
$calendar->add_event('Project Deadline', date('Y-m-12'), 1, 'red');
$calendar->add_event('Conference', date('Y-m-20'), 3, 'green');
$calendar->add_event('Birthday Party', date('Y-m-25'), 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Modern PHP Calendar</title>
<link rel="stylesheet" href="modern-calendar1.css">
</head>
<body>
    <?php echo $calendar; ?>
</body>
</html>

