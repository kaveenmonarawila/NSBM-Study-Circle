/**
 * NSBM STUDY CIRCLE - STUDY SESSIONS JAVASCRIPT
 * Handles Tab filtering, Subject dropdown, Interactive 7-Day Calendar, Seat reservation & Modals
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Elements
    const tabBtns = document.querySelectorAll('.filter-tab-pill');
    const subjectDropdownBtn = document.getElementById('subjectDropdownBtn');
    const subjectDropdownMenu = document.getElementById('subjectDropdownMenu');
    const subjectItems = document.querySelectorAll('.subject-item-link');
    const selectedSubjectLabel = document.getElementById('selectedSubjectLabel');
    const calendarDays = document.querySelectorAll('.calendar-day-col');
    const sessionCards = document.querySelectorAll('.session-card-item');
    const reserveBtn = document.getElementById('reservePlaceBtn');
    const capacityDisplay = document.getElementById('capacityDisplay');
    const attendingCountDisplay = document.getElementById('attendingCountDisplay');
    const createSessionForm = document.getElementById('createSessionForm');

    let currentTab = 'upcoming'; // 'upcoming', 'this-week', 'online'
    let currentSubject = 'all';
    let selectedDate = '24'; // default selected Saturday 24th from PDF

    // 2. Tab Filter Switching
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

    // 3. Subject Dropdown Toggle & Selection
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

    // 4. Weekly Calendar Selection
    if (calendarDays.length > 0) {
        calendarDays.forEach(day => {
            day.addEventListener('click', () => {
                calendarDays.forEach(d => d.classList.remove('selected'));
                day.classList.add('selected');
                selectedDate = day.getAttribute('data-date') || '';
                applyFilters();
            });
        });
    }

    // 5. Unified Filter Function
    function applyFilters() {
        let visibleCount = 0;

        sessionCards.forEach(card => {
            const cardDate = (card.getAttribute('data-day') || '').trim();
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

            // Calendar condition: if user explicitly clicked a day, prefer matching that day or show all if none match
            let matchCalendar = true;
            if (selectedDate && selectedDate !== 'all') {
                // If filtering by specific date
                matchCalendar = (cardDate === selectedDate);
            }

            // If combined matches
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
                    <h5 class="fw-bold mt-3 mb-1">No sessions found for this selection</h5>
                    <p class="text-muted small">Try choosing another day, switching to "Upcoming", or selecting "All subjects".</p>
                    <button class="btn btn-outline-secondary btn-sm mt-2 fw-semibold" onclick="resetSessionFilters()">Show All Upcoming Sessions</button>
                `;
                container.appendChild(emptyMsg);
            }
            if (emptyMsg) emptyMsg.style.display = 'block';
        } else if (emptyMsg) {
            emptyMsg.style.display = 'none';
        }
    }

    // Global reset helper
    window.resetSessionFilters = function() {
        if (tabBtns.length > 0) {
            tabBtns.forEach(b => b.classList.remove('active'));
            tabBtns[0].classList.add('active');
        }
        currentTab = 'upcoming';
        currentSubject = 'all';
        if (selectedSubjectLabel) {
            selectedSubjectLabel.textContent = 'All subjects';
        }
        selectedDate = '';
        calendarDays.forEach(d => d.classList.remove('selected'));
        // Select 24 by default
        const defaultDay = document.querySelector('.calendar-day-col[data-date="24"]');
        if (defaultDay) defaultDay.classList.add('selected');
        selectedDate = '24';
        applyFilters();
    };

    // 6. Interactive Seat Reservation Toggle (Details Page)
    if (reserveBtn) {
        reserveBtn.addEventListener('click', () => {
            const isReserved = reserveBtn.classList.contains('reserved');

            if (!isReserved) {
                reserveBtn.classList.add('reserved');
                reserveBtn.textContent = 'Cancel Reservation';
                if (capacityDisplay) {
                    capacityDisplay.textContent = '19 of 24 places reserved';
                }
                if (attendingCountDisplay) {
                    attendingCountDisplay.textContent = '19 students have reserved a place';
                }
                showSessionToast('Place reserved! We have sent a calendar invite and session link to your email.');
            } else {
                reserveBtn.classList.remove('reserved');
                reserveBtn.textContent = 'Reserve your place';
                if (capacityDisplay) {
                    capacityDisplay.textContent = '18 of 24 places reserved';
                }
                if (attendingCountDisplay) {
                    attendingCountDisplay.textContent = '18 students have reserved a place';
                }
                showSessionToast('Reservation cancelled. Your place has been released.');
            }
        });
    }

    // 7. Create Session Form Handler
    if (createSessionForm) {
        createSessionForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const submitBtn = createSessionForm.querySelector('button[type="submit"]');
            const orig = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Scheduling...';

            setTimeout(() => {
                showSessionToast('Session scheduled successfully! It has been added to the calendar.');
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

    // Toast Notification Utility
    function showSessionToast(msg) {
        let toast = document.getElementById('sessionActionToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'sessionActionToast';
            toast.style.cssText = `
                position: fixed;
                bottom: 24px;
                right: 24px;
                background: #0f172a;
                color: #ffffff;
                padding: 14px 24px;
                border-radius: 12px;
                box-shadow: 0 12px 28px rgba(0,0,0,0.25);
                z-index: 9999;
                font-weight: 600;
                font-size: 0.92rem;
                display: flex;
                align-items: center;
                gap: 10px;
                transform: translateY(120px);
                transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            `;
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="bi bi-check-circle-fill text-success" style="font-size: 1.1rem;"></i> ${msg}`;
        toast.style.transform = 'translateY(0)';
        setTimeout(() => {
            toast.style.transform = 'translateY(140px)';
        }, 3400);
    }
});

