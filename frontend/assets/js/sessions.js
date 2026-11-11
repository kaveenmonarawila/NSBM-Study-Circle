/**
 * NSBM STUDY CIRCLE - DYNAMIC REAL-TIME STUDY SESSIONS & INTERACTIVE CALENDAR
 * Dynamic Date Tracking, Prev/Next Navigation, Live Event Integration & Responsive Filtering
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. DOM Elements
    const tabBtns = document.querySelectorAll('.filter-tab-pill');
    const subjectDropdownBtn = document.getElementById('subjectDropdownBtn');
    const subjectDropdownMenu = document.getElementById('subjectDropdownMenu');
    const subjectItems = document.querySelectorAll('.subject-item-link');
    const selectedSubjectLabel = document.getElementById('selectedSubjectLabel');
    const sessionCards = document.querySelectorAll('.session-card-item');
    const reserveBtn = document.getElementById('reservePlaceBtn');
    const capacityDisplay = document.getElementById('capacityDisplay');
    const attendingCountDisplay = document.getElementById('attendingCountDisplay');
    const createSessionForm = document.getElementById('createSessionForm');

    // Calendar Elements
    const calMonthYearHeader = document.getElementById('publicCalMonthYear');
    const calPrevBtn = document.getElementById('pubCalPrevBtn');
    const calNextBtn = document.getElementById('pubCalNextBtn');
    const calTodayBtn = document.getElementById('pubCalTodayBtn');
    const viewWeekBtn = document.getElementById('pubViewWeekBtn');
    const viewMonthBtn = document.getElementById('pubViewMonthBtn');
    const weeklyStrip = document.getElementById('publicWeeklyStrip');
    const monthlyContainer = document.getElementById('publicMonthlyCalendarContainer');
    const monthGridBody = document.getElementById('publicCalendarGridBody');
    const selectedDateLabel = document.getElementById('publicSelectedDateLabel');
    const resetFilterBtn = document.getElementById('pubResetFilterBtn');

    // State
    const today = new Date();
    let currentAnchorDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    let currentView = 'week'; // 'week' or 'month'
    let currentTab = 'upcoming'; // 'upcoming', 'this-week', 'online'
    let currentSubject = 'all';
    let selectedDateFilter = null; // null for all, or 'YYYY-MM-DD'
    let monthEventsCache = {};

    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    const dayNames = ["MON", "TUE", "WED", "THU", "FRI", "SAT", "SUN"];

    // Helper: format YYYY-MM-DD
    function formatDateKey(d) {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    const todayKey = formatDateKey(today);

    // ------------------------------------------------------------------------
    // 2. Fetch Live Events from Backend Calendar API
    // ------------------------------------------------------------------------
    async function fetchCalendarEvents(year, month) {
        const cacheKey = `${year}-${month}`;
        if (monthEventsCache[cacheKey]) {
            return monthEventsCache[cacheKey];
        }
        try {
            const res = await fetch(`../backend/controllers/calendar_controller.php?action=get_events&year=${year}&month=${month}`);
            if (res.ok) {
                const data = await res.json();
                if (data && data.success) {
                    monthEventsCache[cacheKey] = data.events_by_date || {};
                    return monthEventsCache[cacheKey];
                }
            }
        } catch (err) {
            console.warn('Calendar API fetch warning, using client fallback', err);
        }
        return {};
    }

    // ------------------------------------------------------------------------
    // 3. Render Dynamic Weekly Strip
    // ------------------------------------------------------------------------
    async function renderWeeklyStrip() {
        if (!weeklyStrip) return;

        // Calculate Monday of the current anchor week
        const cur = new Date(currentAnchorDate);
        const dayOfWeek = (cur.getDay() + 6) % 7; // 0 = Mon, 6 = Sun
        const monday = new Date(cur);
        monday.setDate(cur.getDate() - dayOfWeek);

        // Update header month & year
        if (calMonthYearHeader) {
            calMonthYearHeader.textContent = `${monthNames[monday.getMonth()]} ${monday.getFullYear()}`;
        }

        // Fetch events for relevant months
        const y = monday.getFullYear();
        const m = monday.getMonth() + 1;
        const eventsMap = await fetchCalendarEvents(y, m);

        weeklyStrip.innerHTML = '';

        for (let i = 0; i < 7; i++) {
            const d = new Date(monday);
            d.setDate(monday.getDate() + i);
            const dateStr = formatDateKey(d);
            const dayNum = d.getDate();
            const dayLabel = dayNames[i];
            const isToday = (dateStr === todayKey);
            const isSelected = (selectedDateFilter === dateStr);
            const hasEvents = !!(eventsMap[dateStr] && eventsMap[dateStr].length > 0);

            // Also check session cards in DOM
            let hasCardEvent = false;
            sessionCards.forEach(card => {
                const cDate = card.getAttribute('data-date');
                const cDay = parseInt(card.getAttribute('data-day') || '0', 10);
                if (cDate === dateStr || (cDay === dayNum && d.getMonth() === today.getMonth())) {
                    hasCardEvent = true;
                }
            });

            const col = document.createElement('div');
            col.className = `calendar-day-col ${isSelected ? 'selected' : ''} ${isToday ? 'cal-day-col-today' : ''}`;
            col.setAttribute('data-date', dateStr);
            col.setAttribute('data-day', dayNum);

            col.innerHTML = `
                <span class="cal-day-label">${dayLabel}</span>
                <span class="cal-date-number">${dayNum}</span>
                <span class="cal-active-dot" style="${(hasEvents || hasCardEvent) ? 'background:#00b978;' : ''}"></span>
            `;

            col.addEventListener('click', () => {
                document.querySelectorAll('.calendar-day-col').forEach(c => c.classList.remove('selected'));
                col.classList.add('selected');
                selectedDateFilter = dateStr;
                updateSelectedDateBadge(d);
                applyFilters();
            });

            weeklyStrip.appendChild(col);
        }
    }

    // ------------------------------------------------------------------------
    // 4. Render Dynamic Monthly Calendar Grid
    // ------------------------------------------------------------------------
    async function renderMonthlyGrid() {
        if (!monthGridBody) return;

        const year = currentAnchorDate.getFullYear();
        const month = currentAnchorDate.getMonth(); // 0 - 11

        if (calMonthYearHeader) {
            calMonthYearHeader.textContent = `${monthNames[month]} ${year}`;
        }

        const eventsMap = await fetchCalendarEvents(year, month + 1);

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startDayIndex = (firstDay.getDay() + 6) % 7; // Mon = 0, Sun = 6

        // Prev month padding
        const prevMonthLastDay = new Date(year, month, 0).getDate();

        monthGridBody.innerHTML = '';
        let currentWeekTr = document.createElement('tr');
        let nextMonthDay = 1;

        // Leading cells
        for (let i = 0; i < startDayIndex; i++) {
            const td = document.createElement('td');
            td.className = 'calendar-day-cell other-month';
            const num = prevMonthLastDay - startDayIndex + 1 + i;
            td.innerHTML = `<span class="calendar-day-num">${num}</span>`;
            currentWeekTr.appendChild(td);
        }

        // Current month cells
        for (let d = 1; d <= daysInMonth; d++) {
            const thisDate = new Date(year, month, d);
            const dateStr = formatDateKey(thisDate);
            const isToday = (dateStr === todayKey);
            const isSelected = (selectedDateFilter === dateStr);
            const dayEvents = eventsMap[dateStr] || [];

            const td = document.createElement('td');
            td.className = `calendar-day-cell ${isToday ? 'today' : ''} ${isSelected ? 'selected-day' : ''} ${dayEvents.length > 0 ? 'has-event' : ''}`;
            td.setAttribute('data-date', dateStr);

            let chipsHtml = '';
            dayEvents.slice(0, 2).forEach(ev => {
                const chipClass = ev.faculty_meta?.chip || 'chip-blue';
                chipsHtml += `
                    <span class="calendar-event-chip ${chipClass}" title="${ev.title}">
                        ${ev.time} ${ev.title}
                    </span>
                `;
            });
            if (dayEvents.length > 2) {
                chipsHtml += `<span class="badge bg-secondary-subtle text-secondary small py-0" style="font-size:0.6rem;">+${dayEvents.length - 2} more</span>`;
            }

            td.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="calendar-day-num">${d}</span>
                    ${isToday ? '<span class="badge bg-primary text-white rounded-pill px-1" style="font-size:0.55rem;">TODAY</span>' : ''}
                </div>
                ${chipsHtml}
            `;

            td.addEventListener('click', () => {
                document.querySelectorAll('#publicCalendarGridBody .calendar-day-cell').forEach(c => c.classList.remove('selected-day'));
                td.classList.add('selected-day');
                selectedDateFilter = dateStr;
                updateSelectedDateBadge(thisDate);
                applyFilters();
            });

            currentWeekTr.appendChild(td);

            if ((startDayIndex + d) % 7 === 0 || d === daysInMonth) {
                // If last row, fill remaining cells
                if (d === daysInMonth && (startDayIndex + d) % 7 !== 0) {
                    const remaining = 7 - ((startDayIndex + d) % 7);
                    for (let r = 0; r < remaining; r++) {
                        const tdNext = document.createElement('td');
                        tdNext.className = 'calendar-day-cell other-month';
                        tdNext.innerHTML = `<span class="calendar-day-num">${nextMonthDay++}</span>`;
                        currentWeekTr.appendChild(tdNext);
                    }
                }
                monthGridBody.appendChild(currentWeekTr);
                currentWeekTr = document.createElement('tr');
            }
        }
    }

    // ------------------------------------------------------------------------
    // 5. Update Selected Date Label
    // ------------------------------------------------------------------------
    function updateSelectedDateBadge(d) {
        if (!selectedDateLabel) return;
        const options = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' };
        selectedDateLabel.textContent = d.toLocaleDateString('en-US', options);
    }

    // ------------------------------------------------------------------------
    // 6. Navigation Controls
    // ------------------------------------------------------------------------
    if (calPrevBtn) {
        calPrevBtn.addEventListener('click', () => {
            if (currentView === 'week') {
                currentAnchorDate.setDate(currentAnchorDate.getDate() - 7);
                renderWeeklyStrip();
            } else {
                currentAnchorDate.setMonth(currentAnchorDate.getMonth() - 1);
                renderMonthlyGrid();
            }
        });
    }

    if (calNextBtn) {
        calNextBtn.addEventListener('click', () => {
            if (currentView === 'week') {
                currentAnchorDate.setDate(currentAnchorDate.getDate() + 7);
                renderWeeklyStrip();
            } else {
                currentAnchorDate.setMonth(currentAnchorDate.getMonth() + 1);
                renderMonthlyGrid();
            }
        });
    }

    if (calTodayBtn) {
        calTodayBtn.addEventListener('click', () => {
            currentAnchorDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
            selectedDateFilter = todayKey;
            updateSelectedDateBadge(today);
            if (currentView === 'week') {
                renderWeeklyStrip();
            } else {
                renderMonthlyGrid();
            }
            applyFilters();
        });
    }

    // View Toggles
    if (viewWeekBtn && viewMonthBtn) {
        viewWeekBtn.addEventListener('click', () => {
            viewWeekBtn.classList.add('active');
            viewMonthBtn.classList.remove('active');
            currentView = 'week';
            if (weeklyStrip) weeklyStrip.classList.remove('d-none');
            if (monthlyContainer) monthlyContainer.classList.add('d-none');
            renderWeeklyStrip();
        });

        viewMonthBtn.addEventListener('click', () => {
            viewMonthBtn.classList.add('active');
            viewWeekBtn.classList.remove('active');
            currentView = 'month';
            if (weeklyStrip) weeklyStrip.classList.add('d-none');
            if (monthlyContainer) monthlyContainer.classList.remove('d-none');
            renderMonthlyGrid();
        });
    }

    // Reset Filter Button
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', (e) => {
            e.preventDefault();
            selectedDateFilter = null;
            document.querySelectorAll('.calendar-day-col').forEach(c => c.classList.remove('selected'));
            document.querySelectorAll('#publicCalendarGridBody .calendar-day-cell').forEach(c => c.classList.remove('selected-day'));
            if (selectedDateLabel) selectedDateLabel.textContent = 'All Upcoming Sessions';
            applyFilters();
        });
    }

    // ------------------------------------------------------------------------
    // 7. Tab Filter Switching (Upcoming, This week, Online)
    // ------------------------------------------------------------------------
    if (tabBtns.length > 0) {
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                tabBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentTab = btn.getAttribute('data-tab') || 'upcoming';
                applyFilters();
            });
        });
    }

    // ------------------------------------------------------------------------
    // 8. Subject Dropdown Selection
    // ------------------------------------------------------------------------
    if (subjectDropdownBtn && subjectDropdownMenu) {
        subjectDropdownBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            subjectDropdownMenu.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!subjectDropdownBtn.contains(e.target) && !subjectDropdownMenu.contains(e.target)) {
                subjectDropdownMenu.classList.remove('show');
            }
        });

        subjectItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                subjectItems.forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');

                const subjectVal = item.getAttribute('data-subject') || 'all';
                currentSubject = subjectVal.toLowerCase();

                if (selectedSubjectLabel) {
                    selectedSubjectLabel.textContent = item.textContent.trim();
                }

                subjectDropdownMenu.classList.remove('show');
                applyFilters();
            });
        });
    }

    // ------------------------------------------------------------------------
    // 9. Unified Filtering Engine
    // ------------------------------------------------------------------------
    function applyFilters() {
        let visibleCount = 0;

        sessionCards.forEach(card => {
            const cardDate = (card.getAttribute('data-date') || '').trim();
            const cardDay = (card.getAttribute('data-day') || '').trim();
            const cardCategory = (card.getAttribute('data-category') || '').toLowerCase();
            const cardFormat = (card.getAttribute('data-format') || '').toLowerCase();
            const cardThisWeek = card.getAttribute('data-this-week') === 'true';

            // Tab condition
            let matchTab = true;
            if (currentTab === 'this-week') {
                matchTab = cardThisWeek;
            } else if (currentTab === 'online') {
                matchTab = (cardFormat === 'online');
            }

            // Subject condition
            let matchSubject = true;
            if (currentSubject !== 'all' && currentSubject !== 'all subjects') {
                matchSubject = cardCategory.includes(currentSubject) || currentSubject.includes(cardCategory);
            }

            // Date / Calendar condition
            let matchCalendar = true;
            if (selectedDateFilter) {
                if (cardDate) {
                    matchCalendar = (cardDate === selectedDateFilter);
                } else {
                    const filterDay = parseInt(selectedDateFilter.split('-')[2], 10);
                    matchCalendar = (parseInt(cardDay, 10) === filterDay);
                }
            }

            if (matchTab && matchSubject && matchCalendar) {
                card.style.display = 'flex';
                card.classList.add('animate-fade-in-up');
                visibleCount++;
            } else {
                card.style.display = 'none';
                card.classList.remove('animate-fade-in-up');
            }
        });

        // Show empty message if needed
        let emptyMsg = document.getElementById('noSessionsMsg');
        const container = document.getElementById('sessionsListContainer');
        if (visibleCount === 0) {
            if (!emptyMsg && container) {
                emptyMsg = document.createElement('div');
                emptyMsg.id = 'noSessionsMsg';
                emptyMsg.className = 'text-center py-5 bg-white rounded-4 border my-3 p-4';
                emptyMsg.innerHTML = `
                    <i class="bi bi-calendar-x text-muted" style="font-size: 2.8rem;"></i>
                    <h5 class="fw-bold mt-3 mb-1">No study sessions found for this date</h5>
                    <p class="text-muted small">Try selecting another date, switching to "Upcoming", or clearing the date filter.</p>
                    <button class="btn btn-outline-success btn-sm mt-2 fw-semibold" onclick="document.getElementById('pubResetFilterBtn')?.click()">
                        Show All Upcoming Sessions
                    </button>
                `;
                container.appendChild(emptyMsg);
            }
            if (emptyMsg) emptyMsg.style.display = 'block';
        } else if (emptyMsg) {
            emptyMsg.style.display = 'none';
        }
    }

    // ------------------------------------------------------------------------
    // 10. Seat Reservation & Create Session Handlers
    // ------------------------------------------------------------------------
    if (reserveBtn) {
        reserveBtn.addEventListener('click', () => {
            const isReserved = reserveBtn.classList.contains('reserved');
            if (!isReserved) {
                reserveBtn.classList.add('reserved');
                reserveBtn.textContent = 'Cancel Reservation';
                if (capacityDisplay) capacityDisplay.textContent = '19 of 24 places reserved';
                if (attendingCountDisplay) attendingCountDisplay.textContent = '19 students have reserved a place';
                showSessionToast('Place reserved! A confirmation with meeting link has been sent.');
            } else {
                reserveBtn.classList.remove('reserved');
                reserveBtn.textContent = 'Reserve your place';
                if (capacityDisplay) capacityDisplay.textContent = '18 of 24 places reserved';
                if (attendingCountDisplay) attendingCountDisplay.textContent = '18 students have reserved a place';
                showSessionToast('Reservation cancelled. Your place has been released.');
            }
        });
    }

    if (createSessionForm) {
        createSessionForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const submitBtn = createSessionForm.querySelector('button[type="submit"]');
            const orig = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Scheduling...';

            setTimeout(() => {
                showSessionToast('Study session scheduled successfully! It has been posted.');
                const modalEl = document.getElementById('createSessionModal');
                if (modalEl && window.bootstrap) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }
                createSessionForm.reset();
                submitBtn.disabled = false;
                submitBtn.innerHTML = orig;
            }, 800);
        });
    }

    function showSessionToast(msg) {
        let toast = document.getElementById('sessionActionToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'sessionActionToast';
            toast.style.cssText = `
                position: fixed; bottom: 24px; right: 24px;
                background: #0f172a; color: #ffffff; padding: 14px 24px;
                border-radius: 12px; box-shadow: 0 12px 28px rgba(0,0,0,0.25);
                z-index: 9999; font-weight: 600; font-size: 0.92rem;
                display: flex; align-items: center; gap: 10px;
                transform: translateY(120px); transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            `;
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="bi bi-check-circle-fill text-success" style="font-size: 1.1rem;"></i> ${msg}`;
        toast.style.transform = 'translateY(0)';
        setTimeout(() => { toast.style.transform = 'translateY(140px)'; }, 3400);
    }

    // Initial Render
    renderWeeklyStrip();
});
