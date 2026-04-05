/*
  calendar.js
  - Renders calendar grid into #my-calendar
  - Fetches events via ?action=get_events&year=YYYY&month=M
  - Adds month/year dropdowns
  - Adds click-to-book simple prompt UI and POSTs to ?action=add_event
  - Handles overlapping and long events (renders pills across days by placing them in each day cell)
  - Minimal, dependency-free vanilla JS
*/

/* ---------- CONFIG ---------- */
const root = document.getElementById('my-calendar');
const tz = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
const MAX_EVENT_ROWS = 3; // pills before showing "+n more"
/* ----------------------------- */

let viewDate = new Date(); // defaults to today
let events = []; // fetched events (array of {id,title,start,end,color})

function formatYMD(dt) {
  const y = dt.getFullYear();
  const m = String(dt.getMonth()+1).padStart(2,'0');
  const d = String(dt.getDate()).padStart(2,'0');
  return `${y}-${m}-${d}`;
}

function parseYMD(s) {
  const [y,m,d] = s.split('-').map(Number);
  return new Date(y, m-1, d);
}

function openNewEventModal(date) {
    const modal = document.getElementById("new-event-modal");
    modal.style.display = "flex";

    const form = document.getElementById("new-event-form");

    // Reset form each time
    form.reset();

    // Prefill start & days
    form.start.value = date;
    form.days.value = 1;

    // Ensure recurring section is hidden at start
    document.getElementById("recurring-toggle").checked = false;
    document.getElementById("recurring-options").style.display = "none";
}


function closeNewEventModal() {
    document.getElementById("new-event-modal").style.display = "none";
}


// build header (month/year selects + prev/next)
function buildHeader(container) {
  const header = document.createElement('div');
  header.className = 'mycal-header';

  const prevBtn = document.createElement('button');
  prevBtn.className = 'mycal-nav';
  prevBtn.textContent = '←';
  prevBtn.title = 'Previous month';
  prevBtn.addEventListener('click', () => {
    viewDate.setMonth(viewDate.getMonth()-1);
    fetchAndRender();
  });

  const nextBtn = document.createElement('button');
  nextBtn.className = 'mycal-nav';
  nextBtn.textContent = '→';
  nextBtn.title = 'Next month';
  nextBtn.addEventListener('click', () => {
    viewDate.setMonth(viewDate.getMonth()+1);
    fetchAndRender();
  });

  // month select
  const monthSelect = document.createElement('select');
  monthSelect.className = 'mycal-select';
  for (let i=0;i<12;i++){
    const opt = document.createElement('option');
    const tmp = new Date(2000, i, 1);
    opt.value = i;
    opt.textContent = tmp.toLocaleString(undefined, {month:'long'});
    monthSelect.appendChild(opt);
  }
  monthSelect.value = viewDate.getMonth();
  monthSelect.addEventListener('change', (e)=>{
    viewDate.setMonth(parseInt(e.target.value,10));
    fetchAndRender();
  });

  // year select (range +/-5)
  const yearSelect = document.createElement('select');
  yearSelect.className = 'mycal-select';
  const curYear = viewDate.getFullYear();
  for (let y = curYear-5; y <= curYear+5; y++){
    const o = document.createElement('option');
    o.value = y; o.textContent = y;
    if (y === viewDate.getFullYear()) o.selected = true;
    yearSelect.appendChild(o);
  }
  yearSelect.addEventListener('change', (e)=>{
    viewDate.setFullYear(parseInt(e.target.value,10));
    fetchAndRender();
  });

  // title display
  const title = document.createElement('div');
  title.className = 'mycal-title';

  // assemble header
  header.appendChild(prevBtn);
  header.appendChild(monthSelect);
  header.appendChild(yearSelect);
  header.appendChild(nextBtn);
  header.appendChild(title);

  container.appendChild(header);
  return { titleEl: title, monthSelect, yearSelect };
}

// fetch events for current view month via AJAX
function fetchEvents(year, month) {
  const url = `${CAL_API}?action=get_events&year=${year}&month=${month}`;
  return fetch(url).then(r => r.json()).then(data => {
    if (!data.success) throw new Error('Failed to fetch events');
    return data.events || [];
  });
}

// render calendar grid
async function fetchAndRender() {
  root.innerHTML = ''; // clear
  const { titleEl, monthSelect, yearSelect } = buildHeader(root);

  const y = viewDate.getFullYear();
  const m = viewDate.getMonth() + 1; // 1-based
  // set selects to current
  monthSelect.value = viewDate.getMonth();
  // update year select to contain current year; if not present, rebuild
  if (!Array.from(yearSelect.options).some(o => Number(o.value) === y)) {
    // rebuild simple: clear and fill new range
    yearSelect.innerHTML = '';
    for (let yr = y-5; yr <= y+5; yr++){
      const o = document.createElement('option'); o.value = yr; o.textContent = yr;
      if (yr===y) o.selected=true;
      yearSelect.appendChild(o);
    }
  } else {
    yearSelect.value = y;
  }

  // update title
  titleEl.textContent = new Date(y, m-1, 1).toLocaleString(undefined, {month:'long', year:'numeric'});

  // fetch events
  try {
    events = await fetchEvents(y, m);
  } catch (err) {
    root.appendChild(document.createTextNode('Error loading events: ' + err.message));
    return;
  }

  // Build grid: weekdays header + days (starting Sunday)
  const grid = document.createElement('div');
  grid.className = 'mycal-grid';

  const weekdays = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
  const weekdayRow = document.createElement('div');
  weekdayRow.className = 'mycal-weekdays';
  weekdays.forEach(dn => {
    const el = document.createElement('div');
    el.className = 'mycal-weekday';
    el.textContent = dn;
    weekdayRow.appendChild(el);
  });
  grid.appendChild(weekdayRow);

  // determine start/end of range to display
  const firstOfMonth = new Date(y, m-1, 1);
  // startGrid = previous Sunday
  const startGrid = new Date(firstOfMonth);
  startGrid.setDate(firstOfMonth.getDate() - firstOfMonth.getDay());
  // endGrid = last Saturday in month grid
  const lastOfMonth = new Date(y, m, 0);
  const endGrid = new Date(lastOfMonth);
  endGrid.setDate(lastOfMonth.getDate() + (6 - lastOfMonth.getDay()));

  // iterate days from startGrid to endGrid
  const daysContainer = document.createElement('div');
  daysContainer.className = 'mycal-days';

  for (let d = new Date(startGrid); d <= endGrid; d.setDate(d.getDate() + 1)) {
    const cell = document.createElement('div');
    cell.className = 'mycal-day';
    cell.tabIndex = 0;
    const isMuted = (d.getMonth() !== (m-1));
    if (isMuted) cell.classList.add('mycal-day--muted');
    // highlight today
    const today = new Date();
    if (d.getFullYear() === today.getFullYear() && d.getMonth() === today.getMonth() && d.getDate() === today.getDate()) {
      cell.classList.add('mycal-day--today');
    }
    // date number
    const dateNum = document.createElement('div');
    dateNum.className = 'mycal-date';
    dateNum.textContent = d.getDate();
    cell.appendChild(dateNum);

    // events container
    const evWrap = document.createElement('div');
    evWrap.className = 'mycal-events';
    const dateStr = formatYMD(d);

    // find events that include this day (start <= date <= end)
    const dayEvents = events.filter(ev => {
      return (ev.start <= dateStr && ev.end >= dateStr);
    });

    // limit rows and show +n more
    for (let i=0; i < Math.min(dayEvents.length, MAX_EVENT_ROWS); i++){
      const ev = dayEvents[i];
      const pill = document.createElement('div');
      pill.className = 'mycal-ev mycal-ev--' + (ev.color || 'default');
      pill.textContent = ev.title;
      pill.title = `${ev.title} (${ev.start} → ${ev.end})`;
      evWrap.appendChild(pill);
    }
    if (dayEvents.length > MAX_EVENT_ROWS) {
      const more = document.createElement('div');
      more.className = 'mycal-more';
      more.textContent = `+${dayEvents.length - MAX_EVENT_ROWS} more`;
      more.addEventListener('click', () => showDayModal(dateStr));
      evWrap.appendChild(more);
    }

    cell.appendChild(evWrap);

    // click to add/book event
    cell.addEventListener('click', (e) => {
      // don't trigger when clicking on existing event pill (could be extended)
      if (e.target.classList && e.target.classList.contains('mycal-ev')) {
        let title = e.target.innerText;
        let notes = e.target.dataset.notes || "No notes available";
        alert("Event: " + title + "\nNotes: " + notes);
        //return;
      }else
      //showAddPrompt(dateStr);
      openNewEventModal(dateStr);
    });

    daysContainer.appendChild(cell);
  }

  grid.appendChild(daysContainer);
  root.appendChild(grid);
  
    // Track if user manually edited recurring_end
    let recurringEndEdited = false;
    const recurringEndInput = document.querySelector("#new-event-form input[name='recurring_end']");
    recurringEndInput.addEventListener("input", () => {
        recurringEndEdited = true;
    });

    // Update recurring_end when start changes (if not manually edited)
    document.querySelector("#new-event-form input[name='start']").addEventListener("input", function() {
        if (!recurringEndEdited) {
            recurringEndInput.value = this.value;
        }
    });

    // Toggle recurring section visibility + prefill end date
    document.getElementById("recurring-toggle").addEventListener("change", function() {
        const recurringBlock = document.getElementById("recurring-options");
        const startDate = document.querySelector("#new-event-form input[name='start']").value;

        if (this.checked) {
            recurringBlock.style.display = "block";
            if (startDate && !recurringEndInput.value) {
                recurringEndInput.value = startDate;
            }
        } else {
            recurringBlock.style.display = "none";
            recurringEndInput.value = "";
            recurringEndEdited = false; // reset state
        }
    });

    // Handle form submit
    document.getElementById("new-event-form").addEventListener("submit", function(e) {
        e.preventDefault();

        const title = this.title.value;
        const start = this.start.value;
        const days = this.days.value;
        const color = this.color.value;
        const notes = this.notes.value;

        let recurring = "none";
        let recurring_end = "";
        if (this.recurring_enabled.checked) {
            recurring = this.recurring.value;
            recurring_end = this.recurring_end.value;
        }

        alert(
          "New Event:\n" +
          "Title: " + title + "\n" +
          "Start: " + start + "\n" +
          "Days: " + days + "\n" +
          "Recurring: " + recurring + "\n" +
          "Recurring End: " + recurring_end + "\n" +
          "Color: " + color + "\n" +
          "Notes: " + notes
        );

        closeNewEventModal();
});

}

// show prompt to add event (minimalist)
// In production replace with a proper modal form
function showAddPrompt(dateStr) {
  const title = prompt('Event title (leave empty to cancel):');
  if (!title) return;
  let days = prompt('How many days (1):', '1');
  days = parseInt(days,10) || 1;
  const color = prompt('Color (blue,red,green,orange,teal,purple) or leave empty:', 'blue') || 'default';
  // recurrence minimal: none/daily/weekly/monthly/yearly
  const recurrence = prompt('Recurrence (none,daily,weekly,monthly,yearly):', 'none') || 'none';
  let recurrence_end = null;
  if (recurrence !== 'none') {
    recurrence_end = prompt('Recurrence end date (YYYY-MM-DD) or leave empty:', '');
    if (!recurrence_end) recurrence_end = null;
  }

  const start = dateStr;
  // compute end date
  const d = parseYMD(dateStr);
  d.setDate(d.getDate() + days - 1);
  const end = formatYMD(d);

  // POST to add event
  const form = new FormData();
  form.append('action','add_event');
  form.append('title', title);
  form.append('start', start);
  form.append('end', end);
  form.append('color', color);
  form.append('recurrence', recurrence);
  if (recurrence_end) form.append('recurrence_end', recurrence_end);

  fetch(CAL_API, { method: 'POST', body: form })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        alert('Event added');
        // refresh calendar data only
        fetchAndRender();
      } else {
        alert('Failed to add event: ' + (res.error || 'unknown'));
      }
    })
    .catch(err => alert('Network error: ' + err.message));
}

// show a quick day modal listing events (simplified)
// For now we just alert the list
function showDayModal(dateStr) {
  const dayEvents = events.filter(ev => ev.start <= dateStr && ev.end >= dateStr);
  if (!dayEvents.length) {
    alert('No events on ' + dateStr);
    return;
  }
  const lines = dayEvents.map(ev => `${ev.title} (${ev.start} → ${ev.end})`);
  alert('Events on ' + dateStr + ':\n\n' + lines.join('\n'));
}



// initial render
fetchAndRender();

