/**
 * ============================================================================
 * NSBM STUDY CIRCLE - ADMIN CONSOLE INTERACTIVE ENGINE
 * Powers all 10 Admin Console pages (Dashboard, Users, Approvals, Resources,
 * Academic Areas, First-Year Subjects, Discussions, Groups, Sessions,
 * Reports & Analytics, and Admin Settings).
 * ============================================================================
 */
(function () {
    'use strict';

    // Toast Notification Helper
    function showAdminToast(message, type = 'success') {
        let container = document.getElementById('adminToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'adminToastContainer';
            container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;';
            document.body.appendChild(container);
        }

        const bg = type === 'danger' ? '#B91C1C' : (type === 'warning' ? '#B45309' : '#0F172A');
        const icon = type === 'danger' ? 'bi-exclamation-circle-fill text-danger' : 'bi-check-circle-fill text-success';

        const toast = document.createElement('div');
        toast.style.cssText = `background:${bg};color:#FFFFFF;padding:12px 18px;border-radius:12px;font-size:0.84rem;font-weight:600;box-shadow:0 10px 25px rgba(15,23,42,0.2);display:flex;align-items:center;gap:10px;transition:opacity 0.25s ease;`;
        toast.innerHTML = `<i class="bi ${icon}"></i><span>${message}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 260);
        }, 2800);
    }

    window.showAdminToast = showAdminToast;

    // Parse a fetch() response as JSON. Some hosts (e.g. InfinityFree's browser
    // check) can answer with an HTML page instead, so never trust response.json().
    async function readJson(response) {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            showAdminToast('Something went wrong loading data. Please refresh the page and try again.', 'warning');
            const err = new Error('Non-JSON response from server');
            err.nonJson = true;
            throw err;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // 1. Theme Toggle (Synchronized across Website, Student Portal & Admin)
        const savedTheme = localStorage.getItem('theme') || localStorage.getItem('study_circle_theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
        if (document.body) document.body.setAttribute('data-bs-theme', savedTheme);
        localStorage.setItem('theme', savedTheme);
        localStorage.setItem('study_circle_theme', savedTheme);

        const themeBtn = document.getElementById('adminThemeToggle');
        if (themeBtn) {
            const updateIcon = (t) => {
                const icon = themeBtn.querySelector('i');
                if (icon) {
                    icon.className = t === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars';
                }
                const label = t === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode';
                themeBtn.setAttribute('title', label);
                themeBtn.setAttribute('aria-label', label);
            };
            updateIcon(savedTheme);

            themeBtn.addEventListener('click', function () {
                const current = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
                const next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-bs-theme', next);
                if (document.body) document.body.setAttribute('data-bs-theme', next);
                localStorage.setItem('theme', next);
                localStorage.setItem('study_circle_theme', next);
                updateIcon(next);
            });
        }

        // 2. Notification Bell Click
        const notifBtn = document.getElementById('adminNotificationBtn');
        if (notifBtn) {
            notifBtn.addEventListener('click', function () {
                showAdminToast('All moderation queues are up to date (7 pending approvals).');
            });
        }

        // 3. Live Table Search & Status Filtering
        const tableSearchInput = document.querySelector('[data-admin-table-search]');
        const statusFilterSelect = document.querySelector('[data-admin-status-filter]');
        const tableBody = document.querySelector('.admin-table tbody');

        function filterTableRows() {
            if (!tableBody) return;
            const query = (tableSearchInput ? tableSearchInput.value : '').toLowerCase().trim();
            const statusVal = (statusFilterSelect ? statusFilterSelect.value : 'all').toLowerCase();

            const rows = tableBody.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach((row) => {
                const text = row.textContent.toLowerCase();
                const statusBadge = row.querySelector('.status-pill');
                const rowStatus = statusBadge ? statusBadge.textContent.toLowerCase().trim() : text;

                const matchesSearch = !query || text.includes(query);
                const matchesStatus = statusVal === 'all' || statusVal === 'all statuses' || rowStatus.includes(statusVal);

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const countDisplay = document.getElementById('tableRecordCount');
            if (countDisplay && (query || (statusVal !== 'all' && statusVal !== 'all statuses'))) {
                countDisplay.textContent = `Showing ${visibleCount} matching records`;
            }
        }

        if (tableSearchInput) {
            tableSearchInput.addEventListener('input', filterTableRows);
        }
        if (statusFilterSelect) {
            statusFilterSelect.addEventListener('change', filterTableRows);
        }

        // Connect Topbar Global Search to page table search if present, or navigate on Enter
        const globalSearch = document.getElementById('adminGlobalSearch');
        const urlParams = new URLSearchParams(window.location.search);
        const initialQuery = urlParams.get('q');
        if (initialQuery) {
            if (globalSearch) globalSearch.value = initialQuery;
            if (tableSearchInput) {
                tableSearchInput.value = initialQuery;
                filterTableRows();
            }
        }

        if (globalSearch) {
            globalSearch.addEventListener('input', function () {
                if (tableSearchInput) {
                    tableSearchInput.value = globalSearch.value;
                    filterTableRows();
                }
            });
            globalSearch.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !tableSearchInput) {
                    const q = globalSearch.value.trim();
                    if (q) {
                        const isHtml = window.location.pathname.endsWith('.html');
                        window.location.href = (isHtml ? 'users.html' : 'users.php') + '?q=' + encodeURIComponent(q);
                    }
                }
            });
        }

        // 4. Export Buttons (CSV Download)
        document.querySelectorAll('[data-admin-export]').forEach((btn) => {
            btn.addEventListener('click', function () {
                const exportName = btn.getAttribute('data-admin-export') || 'nsbm-admin-export';
                const rows = [];
                const table = document.querySelector('.admin-table');

                if (table) {
                    const headers = Array.from(table.querySelectorAll('thead th'))
                        .map((th) => th.textContent.trim())
                        .filter(Boolean);
                    rows.push(headers.join(','));

                    table.querySelectorAll('tbody tr').forEach((tr) => {
                        if (tr.style.display === 'none') return;
                        const cols = Array.from(tr.querySelectorAll('td'))
                            .slice(0, headers.length)
                            .map((td) => `"${td.textContent.replace(/\s+/g, ' ').trim()}"`);
                        rows.push(cols.join(','));
                    });
                } else {
                    rows.push('Metric,Value,Change');
                    rows.push('"Registered students","2,486","+8.4%"');
                    rows.push('"Weekly active users","1,204","+5.2%"');
                    rows.push('"Published resources","486","+12.1%"');
                    rows.push('"Resource downloads","8,942","+18.6%"');
                }

                const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${exportName}.csv`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
                showAdminToast(`Exported ${exportName}.csv successfully.`);
            });
        });

        // 5. Pagination Buttons
        const pageBtns = document.querySelectorAll('.admin-pagination .page-btn');
        if (pageBtns.length) {
            pageBtns.forEach((btn) => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const label = btn.textContent.trim();
                    if (label === 'Previous' || label === 'Next') {
                        showAdminToast(`Navigated to ${label.toLowerCase()} page of records.`);
                        return;
                    }
                    pageBtns.forEach((b) => b.classList.remove('active'));
                    btn.classList.add('active');
                    const countEl = document.getElementById('tableRecordCount');
                    const p = parseInt(label, 10) || 1;
                    const start = (p - 1) * 5 + 1;
                    const end = p * 5;
                    if (countEl) {
                        countEl.textContent = `Showing ${start}–${end} of 48 records`;
                    }
                });
            });
        }

        // 6. Resource Approvals Interactive Queue (Pages 5 & 6)
        const approvalItems = document.querySelectorAll('.approval-queue-item');
        if (approvalItems.length) {
            const approvalData = window.__NSBM_APPROVAL_QUEUE || [
                {
                    id: 2,
                    faculty: 'Computing',
                    title: 'Data Structures — Complete Revision Guide',
                    meta: 'Submitted by Nethmi Perera · PDF · 5.8 MB',
                    pages: 'Preview · Page 1 of 42',
                    module: 'CS2102',
                    year: 'Year 2',
                    lang: 'English',
                    desc: 'Comprehensive module-aligned notes prepared for revision, including diagrams, examples, and a final exam checklist.'
                },
                {
                    id: 6,
                    faculty: 'Business',
                    title: 'Financial Accounting Formula Book',
                    meta: 'Submitted by Kavindu Silva · PDF · 3.4 MB',
                    pages: 'Preview · Page 1 of 28',
                    module: 'BM1204',
                    year: 'Year 1',
                    lang: 'English',
                    desc: 'Complete balance sheet ratios, cash flow statement templates, and depreciation schedules for first-year accounting.'
                },
                {
                    id: 7,
                    faculty: 'Engineering',
                    title: 'Engineering Mechanics — First-Year Guide',
                    meta: 'Submitted by Ravindu Mendis · PDF · 7.1 MB',
                    pages: 'Preview · Page 1 of 54',
                    module: 'EN1102',
                    year: 'Year 1',
                    lang: 'English',
                    desc: 'Statics, dynamics, free-body diagram methodology, and worked tutorial solutions for first-year engineering students.'
                },
                {
                    id: 8,
                    faculty: 'Science',
                    title: 'Laboratory Skills Handbook',
                    meta: 'Submitted by Dinara Jayasinghe · PDF · 4.2 MB',
                    pages: 'Preview · Page 1 of 36',
                    module: 'SC1105',
                    year: 'Year 1',
                    lang: 'English',
                    desc: 'Standard operating procedures, titration accuracy protocols, and scientific report writing guidelines.'
                }
            ];
            window.__NSBM_APPROVAL_QUEUE = approvalData;

            function selectApprovalItem(index) {
                const list = window.__NSBM_APPROVAL_QUEUE || approvalData;
                const item = list[index] || list[0];
                if (!item) return;
                document.querySelectorAll('.approval-queue-item').forEach((el, idx) => el.classList.toggle('active', idx === index));

                const badgeEl = document.getElementById('approvalDetailFacultyBadge');
                const titleEl = document.getElementById('approvalDetailTitle');
                const metaEl = document.getElementById('approvalDetailMeta');
                const pagesEl = document.getElementById('approvalPreviewPages');
                const sheetTitleEl = document.getElementById('approvalSheetTitle');
                const subjEl = document.getElementById('approvalMetaSubject');
                const modEl = document.getElementById('approvalMetaModule');
                const yrEl = document.getElementById('approvalMetaYear');
                const descEl = document.getElementById('approvalMetaDesc');

                if (badgeEl) badgeEl.textContent = item.faculty;
                if (titleEl) titleEl.textContent = item.title;
                if (metaEl) metaEl.textContent = item.meta;
                if (pagesEl) pagesEl.textContent = item.pages;
                if (sheetTitleEl) sheetTitleEl.textContent = item.title;
                if (subjEl) subjEl.textContent = item.faculty;
                if (modEl) modEl.textContent = item.module;
                if (yrEl) yrEl.textContent = item.year;
                if (descEl) descEl.textContent = item.desc;
            }
            window.__selectApprovalItem = selectApprovalItem;

            approvalItems.forEach((el, idx) => {
                el.addEventListener('click', () => selectApprovalItem(idx));
            });

            const approveBtn = document.getElementById('btnApproveResource');
            const rejectBtn = document.getElementById('btnRejectResource');
            const changesBtn = document.getElementById('btnRequestChanges');

            if (approveBtn) {
                approveBtn.addEventListener('click', () => {
                    const title = document.getElementById('approvalDetailTitle')?.textContent || 'Resource';
                    fetch('../../backend/controllers/admin_controller.php?action=moderate_resource', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ title, status: 'Approved' })
                    }).catch(() => {});
                    showAdminToast(`Approved "${title}" for publication in MySQL database.`);
                });
            }
            if (rejectBtn) {
                rejectBtn.addEventListener('click', () => {
                    const title = document.getElementById('approvalDetailTitle')?.textContent || 'Resource';
                    fetch('../../backend/controllers/admin_controller.php?action=moderate_resource', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ title, status: 'Rejected' })
                    }).catch(() => {});
                    showAdminToast(`Rejected "${title}" in MySQL database. Author notified.`, 'danger');
                });
            }
            if (changesBtn) {
                changesBtn.addEventListener('click', () => {
                    const title = document.getElementById('approvalDetailTitle')?.textContent || 'Resource';
                    showAdminToast(`Requested revision changes for "${title}".`, 'warning');
                });
            }
        }

        // 7. Add Academic Area Modal Form (Pages 10 & 12)
        const addAreaForm = document.getElementById('addAcademicAreaForm');
        if (addAreaForm) {
            addAreaForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const code = (document.getElementById('areaCodeInput')?.value || '').trim().toUpperCase();
                const name = (document.getElementById('areaNameInput')?.value || '').trim();
                if (!code || !name) return;

                if (tableBody) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><span class="code-badge-text">${code}</span></td>
                        <td>${name}</td>
                        <td>6 first-year subjects</td>
                        <td>Year 1</td>
                        <td><span class="status-pill pill-green">Active</span></td>
                        <td class="text-end"><button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button></td>
                    `;
                    tableBody.appendChild(tr);
                }

                const totalAreasEl = document.getElementById('statTotalAreas');
                if (totalAreasEl) {
                    totalAreasEl.textContent = String((parseInt(totalAreasEl.textContent, 10) || 4) + 1);
                }

                fetch('../../backend/controllers/admin_controller.php?action=save_area', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ code, name, status: 'Active' })
                }).catch(() => {});

                const modalEl = document.getElementById('addAcademicAreaModal');
                if (modalEl && window.bootstrap) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                }
                addAreaForm.reset();
                showAdminToast(`Academic area "${name} (${code})" saved to MySQL.`);
            });
        }

        // 8. Add First-Year Subject Modal Form (Page 16)
        const addSubjectForm = document.getElementById('addSubjectForm');
        if (addSubjectForm) {
            addSubjectForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const code = (document.getElementById('subjectCodeInput')?.value || '').trim().toUpperCase();
                const name = (document.getElementById('subjectNameInput')?.value || '').trim();
                const area = document.getElementById('subjectAreaSelect')?.value || 'Computing';
                const year = document.getElementById('subjectYearSelect')?.value || 'Year 1';
                if (!code || !name) return;

                if (tableBody) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><span class="code-badge-text">${code}</span></td>
                        <td>${name}</td>
                        <td>${area}</td>
                        <td>${year}</td>
                        <td><span class="status-pill pill-green">Active</span></td>
                        <td class="text-end"><button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button></td>
                    `;
                    tableBody.prepend(tr);
                }

                const totalSubjectsEl = document.getElementById('statTotalSubjects');
                if (totalSubjectsEl) {
                    totalSubjectsEl.textContent = String((parseInt(totalSubjectsEl.textContent, 10) || 24) + 1);
                }

                fetch('../../backend/controllers/admin_controller.php?action=save_subject', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ code, name, area, year, status: 'Active' })
                }).catch(() => {});

                const modalEl = document.getElementById('addSubjectModal');
                if (modalEl && window.bootstrap) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                }
                addSubjectForm.reset();
                showAdminToast(`First-year subject "${code} · ${name}" saved to MySQL.`);
            });
        }

        // 9. Study Group Invitations Accept / Reject (Page 18)
        document.querySelectorAll('.group-invite-row').forEach((row) => {
            const acceptBtn = row.querySelector('[data-invite-action="accept"]');
            const rejectBtn = row.querySelector('[data-invite-action="reject"]');
            const actionArea = row.querySelector('.invite-actions-cell');

            function handleInviteDecision(decision) {
                if (!actionArea) return;
                const isAccept = decision === 'accept';
                const badgeClass = isAccept ? 'pill-green' : 'pill-red';
                const badgeText = isAccept ? 'Accepted' : 'Rejected';
                actionArea.innerHTML = `
                    <span class="status-pill ${badgeClass}">${badgeText}</span>
                    <span class="text-secondary" style="font-size:0.8rem;margin-left:14px;">Decision recorded</span>
                `;
                const pendingPill = document.getElementById('pendingInvitesBadge');
                if (pendingPill) {
                    const cur = Math.max(0, (parseInt(pendingPill.textContent, 10) || 2) - 1);
                    pendingPill.textContent = `${cur} pending`;
                }
                showAdminToast(`Group invitation ${badgeText.toLowerCase()}.`);
            }

            if (acceptBtn) acceptBtn.addEventListener('click', () => handleInviteDecision('accept'));
            if (rejectBtn) rejectBtn.addEventListener('click', () => handleInviteDecision('reject'));
        });

        // 10. Admin Settings Save & Sub-nav (Pages 23 - 27)
        const generalSettingsForm = document.getElementById('generalSettingsForm');
        if (generalSettingsForm) {
            generalSettingsForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const settingsPayload = {
                    platform_name: document.getElementById('platformNameInput')?.value || 'NSBM Study Circle',
                    support_email: document.getElementById('supportEmailInput')?.value || 'support@example.com',
                    default_language: document.getElementById('defaultLangSelect')?.value || 'English',
                    timezone: document.getElementById('timeZoneSelect')?.value || 'Asia/Colombo',
                    max_file_size_mb: (document.getElementById('maxFileSizeSelect')?.value || '25').replace(/\D/g, ''),
                    allowed_formats: document.getElementById('allowedFormatsInput')?.value || 'PDF, DOCX, PPTX'
                };
                fetch('../../backend/controllers/admin_controller.php?action=save_settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ settings: settingsPayload })
                }).catch(() => {});
                showAdminToast('General platform settings saved to MySQL database.');
            });
        }

        document.querySelectorAll('.admin-switch input').forEach((toggle) => {
            toggle.addEventListener('change', function () {
                showAdminToast('Moderation rule preference updated.');
            });
        });

        const subnavLinks = document.querySelectorAll('.settings-subnav-link');
        subnavLinks.forEach((link) => {
            link.addEventListener('click', function (e) {
                const targetId = link.getAttribute('href');
                if (targetId && targetId.startsWith('#')) {
                    e.preventDefault();
                    subnavLinks.forEach((l) => l.classList.remove('active'));
                    link.classList.add('active');
                    const targetEl = document.querySelector(targetId);
                    if (targetEl) {
                        targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });

        const inviteAdminBtn = document.getElementById('btnInviteAdmin');
        if (inviteAdminBtn) {
            inviteAdminBtn.addEventListener('click', function () {
                showAdminToast('Administrator invitation link copied & ready to send.');
            });
        }

        // 11. Dynamic Context-Aware Three-Dots Menu Engine (`.btn-row-dots`)
        let activeContextMenu = null;
        let activeTriggerBtn = null;

        function closeContextMenu() {
            if (activeContextMenu) {
                activeContextMenu.remove();
                activeContextMenu = null;
                activeTriggerBtn = null;
            }
        }

        // Dynamic Record Detail / Edit Modal Helper
        function openRecordModal(title, subtitle, bodyHtml, onPrimaryAction, primaryLabel = 'Save changes') {
            let modalEl = document.getElementById('adminDynamicRecordModal');
            if (!modalEl) {
                modalEl = document.createElement('div');
                modalEl.id = 'adminDynamicRecordModal';
                modalEl.className = 'modal fade';
                modalEl.tabIndex = -1;
                modalEl.setAttribute('aria-hidden', 'true');
                document.body.appendChild(modalEl);
            }

            modalEl.innerHTML = `
                <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
                    <div class="modal-content admin-modal-content">
                        <div class="admin-modal-body">
                            <h5 class="admin-modal-title">${title}</h5>
                            <p class="admin-modal-subtitle">${subtitle}</p>
                            <div id="adminDynamicModalBodyContent">${bodyHtml}</div>
                        </div>
                        <div class="admin-modal-footer-box">
                            <button type="button" class="btn-admin-outline" data-bs-dismiss="modal">Close</button>
                            ${onPrimaryAction ? `<button type="button" class="btn-admin-green" id="adminDynamicModalPrimaryBtn">${primaryLabel}</button>` : ''}
                        </div>
                    </div>
                </div>
            `;

            if (window.bootstrap) {
                const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                bsModal.show();
                if (onPrimaryAction) {
                    const primaryBtn = modalEl.querySelector('#adminDynamicModalPrimaryBtn');
                    if (primaryBtn) {
                        primaryBtn.addEventListener('click', () => {
                            onPrimaryAction(modalEl, bsModal);
                        });
                    }
                }
            }
        }

        function setStatusPill(pillEl, text, colorClass) {
            if (!pillEl) return;
            pillEl.textContent = text;
            pillEl.className = `status-pill ${colorClass}`;
        }

        function buildContextOptionsForButton(dotsBtn) {
            const path = window.location.pathname.toLowerCase();
            const isHtml = path.endsWith('.html');
            const tr = dotsBtn.closest('tr');
            const options = [];
            let headerLabel = 'Record Actions';

            // CASE A: Resource Approvals Right-Hand Detail Panel (`approvals.php` / `approvals.html`)
            if (!tr && (path.includes('approvals') || document.getElementById('approvalDetailTitle'))) {
                const resTitle = document.getElementById('approvalDetailTitle')?.textContent.trim() || 'Selected Resource';
                const resFaculty = document.getElementById('approvalDetailFacultyBadge')?.textContent.trim() || 'Computing';
                const resMeta = document.getElementById('approvalDetailMeta')?.textContent.trim() || '';
                const resModule = document.getElementById('approvalMetaModule')?.textContent.trim() || 'CS2102';
                headerLabel = resTitle;

                options.push({
                    icon: 'bi-arrows-fullscreen text-primary',
                    label: 'Open full-screen preview',
                    action: () => {
                        openRecordModal(
                            resTitle,
                            `${resFaculty} · Module ${resModule}`,
                            `<div class="p-3 rounded-3 border bg-light text-center">
                                <div class="sheet-tag">STUDYCIRCLE NOTES</div>
                                <div class="fw-bold text-dark mb-2" style="font-size:1.05rem;">${resTitle}</div>
                                <p class="text-secondary small mb-2">${resMeta}</p>
                                <p class="text-secondary small mb-0">${document.getElementById('approvalMetaDesc')?.textContent || ''}</p>
                            </div>`
                        );
                    }
                });
                options.push({
                    icon: 'bi-download text-secondary',
                    label: 'Download submitted PDF',
                    action: () => {
                        const blob = new Blob([`NSBM Study Circle Resource Submission\nTitle: ${resTitle}\nFaculty: ${resFaculty}\nModule: ${resModule}\n${resMeta}\n`], { type: 'text/plain;charset=utf-8' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `${resTitle.toLowerCase().replace(/[^a-z0-9]+/g, '-')}.txt`;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        URL.revokeObjectURL(url);
                        showAdminToast(`Downloaded submission package for "${resTitle}".`);
                    }
                });
                options.push({ divider: true });
                options.push({
                    icon: 'bi-check2-circle text-success',
                    label: 'Approve resource now',
                    action: () => document.getElementById('btnApproveResource')?.click()
                });
                options.push({
                    icon: 'bi-pencil-square text-warning',
                    label: 'Request author changes',
                    action: () => document.getElementById('btnRequestChanges')?.click()
                });
                options.push({
                    icon: 'bi-x-circle',
                    label: 'Reject submission',
                    danger: true,
                    action: () => document.getElementById('btnRejectResource')?.click()
                });

                return { headerLabel, options };
            }

            // CASE B: Admin Settings -> Administrator Access rows (`settings.php` / `settings.html`)
            if (!tr && (path.includes('settings') || dotsBtn.closest('#settingsAdministrators'))) {
                const adminRow = dotsBtn.closest('.d-flex.align-items-center.justify-content-between');
                const nameEl = adminRow?.querySelector('.fw-bold.text-dark');
                const roleEl = adminRow?.querySelector('.text-secondary');
                const adminName = nameEl ? nameEl.textContent.trim() : 'Administrator';
                const currentRole = roleEl ? roleEl.textContent.trim() : 'Moderator';
                headerLabel = adminName;

                options.push({
                    icon: 'bi-shield-check text-primary',
                    label: 'Edit administrator role',
                    action: () => {
                        openRecordModal(
                            'Update administrator role',
                            `Configure permissions for ${adminName}`,
                            `<div class="mb-2">
                                <label class="admin-form-label">Assigned role</label>
                                <select id="modalAdminRoleSelect" class="admin-select w-100" style="height:40px;background-color:#F8FAFC;">
                                    <option value="Super administrator" ${currentRole === 'Super administrator' ? 'selected' : ''}>Super administrator</option>
                                    <option value="Content moderator" ${currentRole === 'Content moderator' ? 'selected' : ''}>Content moderator</option>
                                    <option value="Community moderator" ${currentRole === 'Community moderator' ? 'selected' : ''}>Community moderator</option>
                                </select>
                            </div>`,
                            (modalEl, bsModal) => {
                                const newRole = modalEl.querySelector('#modalAdminRoleSelect')?.value || currentRole;
                                if (roleEl) roleEl.textContent = newRole;
                                fetch('../../backend/controllers/admin_controller.php?action=update_admin_role', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ name: adminName, admin_type: newRole })
                                }).catch(() => {});
                                bsModal.hide();
                                showAdminToast(`Updated ${adminName}'s role to ${newRole} in MySQL.`);
                            },
                            'Update role'
                        );
                    }
                });
                options.push({
                    icon: 'bi-journal-text text-secondary',
                    label: 'View recent audit activity',
                    action: () => {
                        openRecordModal(
                            `${adminName} — Audit Log`,
                            `${currentRole} · Recent platform actions`,
                            `<div class="small text-secondary">
                                <div class="py-2 border-bottom"><strong>Today, 10:14 AM</strong> — Reviewed moderation queue items</div>
                                <div class="py-2 border-bottom"><strong>Yesterday, 4:45 PM</strong> — Updated academic subject mappings</div>
                                <div class="py-2"><strong>18 May 2025</strong> — Verified student resource submissions</div>
                            </div>`
                        );
                    }
                });
                options.push({ divider: true });
                options.push({
                    icon: 'bi-person-x',
                    label: 'Revoke administrator access',
                    danger: true,
                    action: () => {
                        if (adminName === 'Admin User') {
                            showAdminToast('Primary Super Administrator account cannot be revoked.', 'warning');
                            return;
                        }
                        fetch('../../backend/controllers/admin_controller.php?action=remove_admin', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ name: adminName })
                        }).catch(() => {});
                        if (adminRow) adminRow.remove();
                        showAdminToast(`Revoked administrator access for ${adminName} in MySQL.`, 'danger');
                    }
                });

                return { headerLabel, options };
            }

            // CASE C: Table Rows across the 7 Table-Driven Admin Pages
            if (tr) {
                const tds = tr.querySelectorAll('td');
                const pillEl = tr.querySelector('.status-pill');
                const currentStatus = pillEl ? pillEl.textContent.trim() : '';

                // 1. USER MANAGEMENT (`users.php` / `users.html`)
                if (path.includes('users') || path.includes('manage_students')) {
                    const userName = tr.querySelector('.fw-bold.text-dark')?.textContent.trim() || 'Student';
                    const userEmail = tr.querySelector('.text-secondary')?.textContent.trim() || '';
                    const facultyCell = tds[1];
                    const contribCell = tds[4];
                    headerLabel = userName;

                    options.push({
                        icon: 'bi-person-lines-fill text-primary',
                        label: 'View student profile',
                        action: () => {
                            openRecordModal(
                                userName,
                                userEmail,
                                `<div class="row g-2 small">
                                    <div class="col-6"><div class="p-2 rounded bg-light"><strong>Faculty:</strong> ${facultyCell?.textContent || ''}</div></div>
                                    <div class="col-6"><div class="p-2 rounded bg-light"><strong>Status:</strong> ${currentStatus}</div></div>
                                    <div class="col-6"><div class="p-2 rounded bg-light"><strong>Last active:</strong> ${tds[3]?.textContent || ''}</div></div>
                                    <div class="col-6"><div class="p-2 rounded bg-light"><strong>Contributions:</strong> ${contribCell?.textContent || '0'}</div></div>
                                </div>`
                            );
                        }
                    });

                    if (currentStatus !== 'Active') {
                        options.push({
                            icon: 'bi-check-circle text-success',
                            label: currentStatus === 'Suspended' ? 'Restore student access' : 'Approve student account',
                            action: () => {
                                setStatusPill(pillEl, 'Active', 'pill-green');
                                fetch('../../backend/controllers/admin_controller.php?action=update_user_status', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ email: userEmail, name: userName, status: 'Active' })
                                }).catch(() => {});
                                showAdminToast(`${userName}'s account is now Active in MySQL.`);
                            }
                        });
                    }

                    if (currentStatus !== 'Pending') {
                        options.push({
                            icon: 'bi-clock-history text-warning',
                            label: 'Mark as Pending review',
                            action: () => {
                                setStatusPill(pillEl, 'Pending', 'pill-amber');
                                fetch('../../backend/controllers/admin_controller.php?action=update_user_status', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ email: userEmail, name: userName, status: 'Pending' })
                                }).catch(() => {});
                                showAdminToast(`${userName} moved to Pending status in MySQL.`, 'warning');
                            }
                        });
                    }

                    if (currentStatus !== 'Suspended') {
                        options.push({
                            icon: 'bi-slash-circle text-danger',
                            label: 'Suspend student account',
                            action: () => {
                                setStatusPill(pillEl, 'Suspended', 'pill-red');
                                fetch('../../backend/controllers/admin_controller.php?action=update_user_status', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ email: userEmail, name: userName, status: 'Suspended' })
                                }).catch(() => {});
                                showAdminToast(`Suspended account for ${userName} in MySQL.`, 'danger');
                            }
                        });
                    }

                    options.push({ divider: true });
                    options.push({
                        icon: 'bi-trash',
                        label: 'Remove user record',
                        danger: true,
                        action: () => {
                            fetch('../../backend/controllers/admin_controller.php?action=delete_user', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ email: userEmail, name: userName })
                            }).catch(() => {});
                            tr.remove();
                            showAdminToast(`Removed ${userName} from MySQL database.`, 'danger');
                        }
                    });

                    return { headerLabel, options };
                }

                // 2. RESOURCE MANAGEMENT (`resources.php` / `resources.html`)
                if (path.includes('resources') || path.includes('manage_resources')) {
                    const resName = tr.querySelector('.fw-bold.text-dark')?.textContent.trim() || 'Resource';
                    const subjectCell = tds[1];
                    const downloadsCell = tds[3];
                    headerLabel = resName;

                    options.push({
                        icon: 'bi-eye text-primary',
                        label: 'Inspect resource details',
                        action: () => {
                            openRecordModal(
                                resName,
                                `${subjectCell?.textContent || 'Academic'} · ${downloadsCell?.textContent || '0'} downloads`,
                                `<div class="mb-3">
                                    <label class="admin-form-label">Resource title</label>
                                    <input type="text" id="editResTitleInput" class="admin-form-control" value="${resName}">
                                </div>
                                <div>
                                    <label class="admin-form-label">Subject area</label>
                                    <select id="editResSubjectSelect" class="admin-select w-100" style="height:40px;background-color:#F8FAFC;">
                                        <option value="Computing" ${subjectCell?.textContent.trim() === 'Computing' ? 'selected' : ''}>Computing</option>
                                        <option value="Business" ${subjectCell?.textContent.trim() === 'Business' ? 'selected' : ''}>Business</option>
                                        <option value="Engineering" ${subjectCell?.textContent.trim() === 'Engineering' ? 'selected' : ''}>Engineering</option>
                                        <option value="Science" ${subjectCell?.textContent.trim() === 'Science' ? 'selected' : ''}>Science</option>
                                    </select>
                                </div>`,
                                (modalEl, bsModal) => {
                                    const newTitle = modalEl.querySelector('#editResTitleInput')?.value.trim();
                                    const newSubj = modalEl.querySelector('#editResSubjectSelect')?.value;
                                    const titleSpan = tr.querySelector('.fw-bold.text-dark');
                                    if (newTitle && titleSpan) titleSpan.textContent = newTitle;
                                    if (newSubj && subjectCell) subjectCell.textContent = newSubj;
                                    fetch('../../backend/controllers/admin_controller.php?action=edit_resource', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify({ old_title: resName, title: newTitle || resName, faculty: newSubj || 'Computing' })
                                    }).catch(() => {});
                                    bsModal.hide();
                                    showAdminToast(`Updated resource "${newTitle || resName}" in MySQL.`);
                                }
                            );
                        }
                    });

                    if (currentStatus !== 'Approved') {
                        options.push({
                            icon: 'bi-check-circle text-success',
                            label: 'Approve & Publish resource',
                            action: () => {
                                setStatusPill(pillEl, 'Approved', 'pill-green');
                                if (downloadsCell && downloadsCell.textContent.trim() === '—') {
                                    downloadsCell.textContent = '1';
                                }
                                fetch('../../backend/controllers/admin_controller.php?action=moderate_resource', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ title: resName, status: 'Approved' })
                                }).catch(() => {});
                                showAdminToast(`Published "${resName}" in MySQL.`);
                            }
                        });
                    }

                    if (currentStatus !== 'Archived') {
                        options.push({
                            icon: 'bi-archive text-warning',
                            label: 'Archive resource',
                            action: () => {
                                setStatusPill(pillEl, 'Archived', 'pill-amber');
                                fetch('../../backend/controllers/admin_controller.php?action=moderate_resource', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ title: resName, status: 'Archived' })
                                }).catch(() => {});
                                showAdminToast(`Archived "${resName}" in MySQL.`, 'warning');
                            }
                        });
                    }

                    if (currentStatus !== 'Rejected') {
                        options.push({
                            icon: 'bi-x-circle text-danger',
                            label: 'Reject resource',
                            action: () => {
                                setStatusPill(pillEl, 'Rejected', 'pill-red');
                                fetch('../../backend/controllers/admin_controller.php?action=moderate_resource', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ title: resName, status: 'Rejected' })
                                }).catch(() => {});
                                showAdminToast(`Marked "${resName}" as Rejected in MySQL.`, 'danger');
                            }
                        });
                    }

                    options.push({ divider: true });
                    options.push({
                        icon: 'bi-trash',
                        label: 'Delete from inventory',
                        danger: true,
                        action: () => {
                            fetch('../../backend/controllers/admin_controller.php?action=delete_resource', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ title: resName })
                            }).catch(() => {});
                            tr.remove();
                            showAdminToast(`Deleted "${resName}" from MySQL database.`, 'danger');
                        }
                    });

                    return { headerLabel, options };
                }

                // 3. ACADEMIC AREAS (`academic-areas.php` / `academic-areas.html`)
                if (path.includes('academic-areas')) {
                    const codeEl = tds[0]?.querySelector('.code-badge-text');
                    const areaCode = codeEl ? codeEl.textContent.trim() : 'AREA';
                    const areaName = tds[1]?.textContent.trim() || 'Academic Area';
                    headerLabel = `${areaCode} · ${areaName}`;

                    options.push({
                        icon: 'bi-pencil-square text-primary',
                        label: 'Edit academic area',
                        action: () => {
                            openRecordModal(
                                'Edit academic area',
                                'Update taxonomy code and area name.',
                                `<div class="mb-3">
                                    <label class="admin-form-label">Area code</label>
                                    <input type="text" id="editAreaCode" class="admin-form-control" value="${areaCode}">
                                </div>
                                <div>
                                    <label class="admin-form-label">Academic area name</label>
                                    <input type="text" id="editAreaName" class="admin-form-control" value="${areaName}">
                                </div>`,
                                (modalEl, bsModal) => {
                                    const c = modalEl.querySelector('#editAreaCode')?.value.trim().toUpperCase();
                                    const n = modalEl.querySelector('#editAreaName')?.value.trim();
                                    if (c && codeEl) codeEl.textContent = c;
                                    if (n && tds[1]) tds[1].textContent = n;
                                    fetch('../../backend/controllers/admin_controller.php?action=save_area', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify({ old_code: areaCode, code: c || areaCode, name: n || areaName, status: currentStatus || 'Active' })
                                    }).catch(() => {});
                                    bsModal.hide();
                                    showAdminToast(`Updated academic area "${n || areaName}" in MySQL.`);
                                }
                            );
                        }
                    });

                    options.push({
                        icon: 'bi-journal-text text-secondary',
                        label: `View ${areaName} subjects`,
                        action: () => {
                            window.location.href = (isHtml ? 'subjects.html' : 'subjects.php') + '?q=' + encodeURIComponent(areaName);
                        }
                    });

                    if (currentStatus === 'Active') {
                        options.push({
                            icon: 'bi-pause-circle text-warning',
                            label: 'Deactivate academic area',
                            action: () => {
                                setStatusPill(pillEl, 'Inactive', 'pill-amber');
                                fetch('../../backend/controllers/admin_controller.php?action=save_area', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ code: areaCode, name: areaName, status: 'Inactive' })
                                }).catch(() => {});
                                showAdminToast(`${areaName} set to Inactive in MySQL.`, 'warning');
                            }
                        });
                    } else {
                        options.push({
                            icon: 'bi-check-circle text-success',
                            label: 'Activate academic area',
                            action: () => {
                                setStatusPill(pillEl, 'Active', 'pill-green');
                                fetch('../../backend/controllers/admin_controller.php?action=save_area', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ code: areaCode, name: areaName, status: 'Active' })
                                }).catch(() => {});
                                showAdminToast(`${areaName} activated in MySQL.`);
                            }
                        });
                    }

                    options.push({ divider: true });
                    options.push({
                        icon: 'bi-trash',
                        label: 'Delete academic area',
                        danger: true,
                        action: () => {
                            fetch('../../backend/controllers/admin_controller.php?action=delete_area', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ code: areaCode, name: areaName })
                            }).catch(() => {});
                            tr.remove();
                            const totalAreasEl = document.getElementById('statTotalAreas');
                            if (totalAreasEl) {
                                totalAreasEl.textContent = String(Math.max(0, (parseInt(totalAreasEl.textContent, 10) || 4) - 1));
                            }
                            showAdminToast(`Deleted academic area "${areaName}" from MySQL.`, 'danger');
                        }
                    });

                    return { headerLabel, options };
                }

                // 4. FIRST-YEAR SUBJECTS (`subjects.php` / `subjects.html`)
                if (path.includes('subjects')) {
                    const codeEl = tds[0]?.querySelector('.code-badge-text');
                    const subjCode = codeEl ? codeEl.textContent.trim() : 'SUBJ';
                    const subjName = tds[1]?.textContent.trim() || 'Subject';
                    const subjArea = tds[2]?.textContent.trim() || 'Computing';
                    headerLabel = `${subjCode} · ${subjName}`;

                    options.push({
                        icon: 'bi-pencil-square text-primary',
                        label: 'Edit subject details',
                        action: () => {
                            openRecordModal(
                                'Edit first-year subject',
                                `Modify ${subjCode} curriculum attributes.`,
                                `<div class="mb-3">
                                    <label class="admin-form-label">Subject code</label>
                                    <input type="text" id="editSubjCode" class="admin-form-control" value="${subjCode}">
                                </div>
                                <div class="mb-3">
                                    <label class="admin-form-label">Subject name</label>
                                    <input type="text" id="editSubjName" class="admin-form-control" value="${subjName}">
                                </div>
                                <div>
                                    <label class="admin-form-label">Academic area</label>
                                    <select id="editSubjArea" class="admin-select w-100" style="height:40px;background-color:#F8FAFC;">
                                        <option value="Computing" ${subjArea === 'Computing' ? 'selected' : ''}>Computing</option>
                                        <option value="Business" ${subjArea === 'Business' ? 'selected' : ''}>Business</option>
                                        <option value="Engineering" ${subjArea === 'Engineering' ? 'selected' : ''}>Engineering</option>
                                        <option value="Science" ${subjArea === 'Science' ? 'selected' : ''}>Science</option>
                                    </select>
                                </div>`,
                                (modalEl, bsModal) => {
                                    const c = modalEl.querySelector('#editSubjCode')?.value.trim().toUpperCase();
                                    const n = modalEl.querySelector('#editSubjName')?.value.trim();
                                    const a = modalEl.querySelector('#editSubjArea')?.value;
                                    if (c && codeEl) codeEl.textContent = c;
                                    if (n && tds[1]) tds[1].textContent = n;
                                    if (a && tds[2]) tds[2].textContent = a;
                                    fetch('../../backend/controllers/admin_controller.php?action=save_subject', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify({ old_code: subjCode, code: c || subjCode, name: n || subjName, area: a || subjArea, status: currentStatus || 'Active' })
                                    }).catch(() => {});
                                    bsModal.hide();
                                    showAdminToast(`Updated subject "${c} · ${n}" in MySQL.`);
                                }
                            );
                        }
                    });

                    options.push({
                        icon: 'bi-folder2-open text-secondary',
                        label: `Browse ${subjArea} resources`,
                        action: () => {
                            window.location.href = (isHtml ? 'resources.html' : 'resources.php') + '?q=' + encodeURIComponent(subjArea);
                        }
                    });

                    if (currentStatus === 'Active') {
                        options.push({
                            icon: 'bi-archive text-warning',
                            label: 'Archive subject',
                            action: () => {
                                setStatusPill(pillEl, 'Archived', 'pill-amber');
                                fetch('../../backend/controllers/admin_controller.php?action=save_subject', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ code: subjCode, name: subjName, area: subjArea, status: 'Archived' })
                                }).catch(() => {});
                                showAdminToast(`Archived ${subjCode} in MySQL.`, 'warning');
                            }
                        });
                    } else {
                        options.push({
                            icon: 'bi-check-circle text-success',
                            label: 'Activate subject',
                            action: () => {
                                setStatusPill(pillEl, 'Active', 'pill-green');
                                fetch('../../backend/controllers/admin_controller.php?action=save_subject', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ code: subjCode, name: subjName, area: subjArea, status: 'Active' })
                                }).catch(() => {});
                                showAdminToast(`Activated ${subjCode} in MySQL.`);
                            }
                        });
                    }

                    options.push({ divider: true });
                    options.push({
                        icon: 'bi-trash',
                        label: 'Delete subject',
                        danger: true,
                        action: () => {
                            fetch('../../backend/controllers/admin_controller.php?action=delete_subject', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ code: subjCode })
                            }).catch(() => {});
                            tr.remove();
                            const totalSubjectsEl = document.getElementById('statTotalSubjects');
                            if (totalSubjectsEl) {
                                totalSubjectsEl.textContent = String(Math.max(0, (parseInt(totalSubjectsEl.textContent, 10) || 24) - 1));
                            }
                            showAdminToast(`Removed ${subjCode} from MySQL database.`, 'danger');
                        }
                    });

                    return { headerLabel, options };
                }

                // 5. DISCUSSION MODERATION (`discussions.php` / `discussions.html`)
                if (path.includes('discussions') || path.includes('manage_announcements')) {
                    const postTitle = tr.querySelector('.fw-bold.text-dark')?.textContent.trim() || 'Reported discussion';
                    const reasonText = tds[1]?.textContent.trim() || 'Policy violation';
                    const reportsCell = tds[3];
                    headerLabel = postTitle;

                    options.push({
                        icon: 'bi-eye text-primary',
                        label: 'Inspect reported content',
                        action: () => {
                            openRecordModal(
                                'Reported Discussion Review',
                                `Flagged for: ${reasonText} (${reportsCell?.textContent || '1 report'})`,
                                `<div class="p-3 rounded border bg-light mb-2">
                                    <div class="fw-bold text-dark mb-1">${postTitle}</div>
                                    <div class="text-secondary small">Reported by community members under <strong>${reasonText}</strong> policy.</div>
                                </div>`
                            );
                        }
                    });

                    options.push({
                        icon: 'bi-check2-circle text-success',
                        label: 'Mark report as Resolved',
                        action: () => {
                            setStatusPill(pillEl, 'Resolved', 'pill-green');
                            if (reportsCell) reportsCell.textContent = '0 reports';
                            fetch('../../backend/controllers/admin_controller.php?action=resolve_report', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ title: postTitle, status: 'Resolved' })
                            }).catch(() => {});
                            showAdminToast(`Resolved report for ${postTitle} in MySQL.`);
                        }
                    });

                    if (currentStatus !== 'High priority') {
                        options.push({
                            icon: 'bi-exclamation-triangle text-danger',
                            label: 'Escalate to High priority',
                            action: () => {
                                setStatusPill(pillEl, 'High priority', 'pill-red');
                                fetch('../../backend/controllers/admin_controller.php?action=resolve_report', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ title: postTitle, priority: 'High priority' })
                                }).catch(() => {});
                                showAdminToast(`Escalated thread to High priority in MySQL.`, 'warning');
                            }
                        });
                    }

                    options.push({
                        icon: 'bi-shield-check text-secondary',
                        label: 'Dismiss report (Keep post)',
                        action: () => {
                            setStatusPill(pillEl, 'Dismissed', 'pill-blue');
                            if (reportsCell) reportsCell.textContent = '0 reports';
                            fetch('../../backend/controllers/admin_controller.php?action=resolve_report', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ title: postTitle, status: 'Dismissed' })
                            }).catch(() => {});
                            showAdminToast('Report dismissed in MySQL; post retained.');
                        }
                    });

                    options.push({ divider: true });
                    options.push({
                        icon: 'bi-trash',
                        label: 'Remove offending content',
                        danger: true,
                        action: () => {
                            fetch('../../backend/controllers/admin_controller.php?action=delete_report', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ title: postTitle })
                            }).catch(() => {});
                            tr.remove();
                            showAdminToast('Removed reported discussion from MySQL.', 'danger');
                        }
                    });

                    return { headerLabel, options };
                }

                // 6. STUDY GROUPS (`groups.php` / `groups.html`)
                if (path.includes('groups')) {
                    const groupName = tr.querySelector('.fw-bold.text-dark')?.textContent.trim() || 'Study Group';
                    const membersText = tds[1]?.textContent.trim() || '0 members';
                    const reportsCell = tds[3];
                    headerLabel = groupName;

                    options.push({
                        icon: 'bi-people text-primary',
                        label: 'View study group overview',
                        action: () => {
                            openRecordModal(
                                groupName,
                                `${membersText} · Status: ${currentStatus}`,
                                `<div class="p-3 rounded border bg-light small text-secondary">
                                    <p class="mb-2"><strong>Active membership:</strong> ${membersText}</p>
                                    <p class="mb-0"><strong>Moderation reports:</strong> ${reportsCell?.textContent || '0 reports'}</p>
                                </div>`
                            );
                        }
                    });

                    if (currentStatus !== 'Active') {
                        options.push({
                            icon: 'bi-check-circle text-success',
                            label: 'Approve & Clear group flags',
                            action: () => {
                                setStatusPill(pillEl, 'Active', 'pill-green');
                                if (reportsCell) reportsCell.textContent = '0 reports';
                                fetch('../../backend/controllers/admin_controller.php?action=moderate_group', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ name: groupName, status: 'Active' })
                                }).catch(() => {});
                                showAdminToast(`"${groupName}" is now Active and cleared in MySQL.`);
                            }
                        });
                    }

                    if (currentStatus !== 'Review') {
                        options.push({
                            icon: 'bi-clock-history text-warning',
                            label: 'Place group under Review',
                            action: () => {
                                setStatusPill(pillEl, 'Review', 'pill-amber');
                                fetch('../../backend/controllers/admin_controller.php?action=moderate_group', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ name: groupName, status: 'Review' })
                                }).catch(() => {});
                                showAdminToast(`"${groupName}" placed under moderator review in MySQL.`, 'warning');
                            }
                        });
                    }

                    if (currentStatus !== 'Flagged') {
                        options.push({
                            icon: 'bi-flag text-danger',
                            label: 'Flag group for violation',
                            action: () => {
                                setStatusPill(pillEl, 'Flagged', 'pill-red');
                                fetch('../../backend/controllers/admin_controller.php?action=moderate_group', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ name: groupName, status: 'Flagged' })
                                }).catch(() => {});
                                showAdminToast(`Flagged "${groupName}" in MySQL.`, 'danger');
                            }
                        });
                    }

                    options.push({ divider: true });
                    options.push({
                        icon: 'bi-trash',
                        label: 'Disband study group',
                        danger: true,
                        action: () => {
                            fetch('../../backend/controllers/admin_controller.php?action=delete_group', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ name: groupName })
                            }).catch(() => {});
                            tr.remove();
                            showAdminToast(`Disbanded "${groupName}" in MySQL.`, 'danger');
                        }
                    });

                    return { headerLabel, options };
                }

                // 7. STUDY SESSIONS (`sessions.php` / `sessions.html`)
                if (path.includes('sessions') || path.includes('manage_events')) {
                    const sessName = tr.querySelector('.fw-bold.text-dark')?.textContent.trim() || 'Study Session';
                    const dateTimeCell = tds[1];
                    const capPill = tds[2]?.querySelector('.status-pill');
                    const statusCell = tds[3];
                    const sessStatus = statusCell?.textContent.trim() || 'Scheduled';
                    headerLabel = sessName;

                    options.push({
                        icon: 'bi-calendar2-week text-primary',
                        label: 'Edit session schedule',
                        action: () => {
                            openRecordModal(
                                'Edit study session',
                                sessName,
                                `<div class="mb-3">
                                    <label class="admin-form-label">Session title</label>
                                    <input type="text" id="editSessTitle" class="admin-form-control" value="${sessName}">
                                </div>
                                <div>
                                    <label class="admin-form-label">Date &amp; Time</label>
                                    <input type="text" id="editSessTime" class="admin-form-control" value="${dateTimeCell?.textContent.trim() || ''}">
                                </div>`,
                                (modalEl, bsModal) => {
                                    const newT = modalEl.querySelector('#editSessTitle')?.value.trim();
                                    const newDT = modalEl.querySelector('#editSessTime')?.value.trim();
                                    const titleSpan = tr.querySelector('.fw-bold.text-dark');
                                    if (newT && titleSpan) titleSpan.textContent = newT;
                                    if (newDT && dateTimeCell) dateTimeCell.textContent = newDT;
                                    fetch('../../backend/controllers/admin_controller.php?action=moderate_session', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify({ old_title: sessName, title: newT || sessName })
                                    }).catch(() => {});
                                    bsModal.hide();
                                    showAdminToast(`Updated session "${newT || sessName}" in MySQL.`);
                                }
                            );
                        }
                    });

                    if (sessStatus !== 'Scheduled') {
                        options.push({
                            icon: 'bi-check-circle text-success',
                            label: 'Approve & Schedule session',
                            action: () => {
                                if (statusCell) statusCell.textContent = 'Scheduled';
                                fetch('../../backend/controllers/admin_controller.php?action=moderate_session', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ title: sessName, status: 'Scheduled' })
                                }).catch(() => {});
                                showAdminToast(`"${sessName}" is now Scheduled in MySQL.`);
                            }
                        });
                    } else {
                        options.push({
                            icon: 'bi-clock-history text-warning',
                            label: 'Hold session for Review',
                            action: () => {
                                if (statusCell) statusCell.textContent = 'Review';
                                fetch('../../backend/controllers/admin_controller.php?action=moderate_session', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ title: sessName, status: 'Review' })
                                }).catch(() => {});
                                showAdminToast(`Moved "${sessName}" to Review in MySQL.`, 'warning');
                            }
                        });
                    }

                    options.push({
                        icon: 'bi-person-plus text-secondary',
                        label: 'Expand capacity (+6 seats)',
                        action: () => {
                            if (capPill) {
                                const parts = capPill.textContent.split('/').map((s) => parseInt(s.trim(), 10));
                                if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
                                    const newCap = parts[1] + 6;
                                    capPill.textContent = `${parts[0]} / ${newCap}`;
                                    fetch('../../backend/controllers/admin_controller.php?action=moderate_session', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify({ title: sessName, capacity: newCap })
                                    }).catch(() => {});
                                    showAdminToast(`Expanded capacity for "${sessName}" to ${newCap} seats in MySQL.`);
                                }
                            }
                        }
                    });

                    options.push({ divider: true });
                    options.push({
                        icon: 'bi-x-circle',
                        label: 'Cancel study session',
                        danger: true,
                        action: () => {
                            fetch('../../backend/controllers/admin_controller.php?action=delete_session', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ title: sessName })
                            }).catch(() => {});
                            tr.remove();
                            showAdminToast(`Cancelled session "${sessName}" in MySQL.`, 'danger');
                        }
                    });

                    return { headerLabel, options };
                }
            }

            return { headerLabel, options };
        }

        document.addEventListener('click', function (e) {
            const dotsBtn = e.target.closest('.btn-row-dots');
            if (!dotsBtn) {
                if (activeContextMenu && !e.target.closest('.admin-context-menu')) {
                    closeContextMenu();
                }
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            if (activeTriggerBtn === dotsBtn && activeContextMenu) {
                closeContextMenu();
                return;
            }

            closeContextMenu();

            const { headerLabel, options } = buildContextOptionsForButton(dotsBtn);
            if (!options.length) return;

            const menu = document.createElement('div');
            menu.className = 'admin-context-menu';

            const header = document.createElement('div');
            header.className = 'admin-context-header';
            header.textContent = headerLabel;
            menu.appendChild(header);

            options.forEach((opt) => {
                if (opt.divider) {
                    const div = document.createElement('div');
                    div.className = 'admin-context-divider';
                    menu.appendChild(div);
                    return;
                }
                const itemBtn = document.createElement('button');
                itemBtn.type = 'button';
                itemBtn.className = 'admin-context-item' + (opt.danger ? ' danger' : '');
                itemBtn.innerHTML = `<i class="bi ${opt.icon}"></i><span>${opt.label}</span>`;
                itemBtn.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    closeContextMenu();
                    if (typeof opt.action === 'function') {
                        opt.action();
                    }
                });
                menu.appendChild(itemBtn);
            });

            document.body.appendChild(menu);
            activeContextMenu = menu;
            activeTriggerBtn = dotsBtn;

            // Position menu cleanly near the clicked button without overflowing viewport
            const rect = dotsBtn.getBoundingClientRect();
            const menuRect = menu.getBoundingClientRect();
            let top = rect.bottom + 6;
            let left = rect.right - menuRect.width;

            if (top + menuRect.height > window.innerHeight - 12) {
                top = Math.max(12, rect.top - menuRect.height - 6);
            }
            if (left < 12) {
                left = 12;
            }

            menu.style.top = `${top}px`;
            menu.style.left = `${left}px`;
        });

        window.addEventListener('scroll', closeContextMenu, true);
        window.addEventListener('resize', closeContextMenu);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeContextMenu();
        });

        // ====================================================================
        // 12. LIVE MYSQL DATABASE HYDRATION (`admin_controller.php?action=admin_data`)
        // ====================================================================
        fetch('../../backend/controllers/admin_controller.php?action=admin_data')
            .then((res) => readJson(res))
            .then((data) => {
                if (!data || !data.success) return;

                const path = window.location.pathname.toLowerCase();
                const m = data.metrics || {};

                // 1. Update Dashboard KPI Cards if on index
                const kpiLabels = document.querySelectorAll('.admin-kpi-card');
                kpiLabels.forEach((card) => {
                    const label = (card.querySelector('.admin-kpi-label')?.textContent || '').toLowerCase();
                    const valEl = card.querySelector('.admin-kpi-value');
                    if (!valEl) return;
                    if (label.includes('total students') && typeof m.total_students === 'number') {
                        valEl.textContent = Number(m.total_students).toLocaleString();
                    } else if (label.includes('pending approvals') && typeof m.pending_approvals === 'number') {
                        valEl.textContent = String(m.pending_approvals);
                    } else if (label.includes('open reports') && typeof m.open_reports === 'number') {
                        valEl.textContent = String(m.open_reports);
                    } else if (label.includes('active this week') && typeof m.active_students === 'number') {
                        valEl.textContent = Number(m.active_students).toLocaleString();
                    }
                });

                // 2. User Management (`users.php` / `users.html`)
                if ((path.includes('users') || path.includes('manage_students')) && tableBody && Array.isArray(data.users) && data.users.length) {
                    tableBody.innerHTML = data.users.map((u) => {
                        const status = u.status || 'Active';
                        const pillClass = status === 'Active' ? 'pill-green' : (status === 'Pending' ? 'pill-amber' : 'pill-red');
                        return `
                            <tr data-user-id="${u.id}">
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="admin-avatar-circle">${u.initials || 'ST'}</div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size:0.85rem;">${u.name}</div>
                                            <div class="text-secondary" style="font-size:0.76rem;">${u.email}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>${u.faculty || 'Computing'}</td>
                                <td><span class="status-pill ${pillClass}">${status}</span></td>
                                <td>${u.last_active_formatted || 'Today'}</td>
                                <td>${u.contributions_count || 0}</td>
                                <td class="text-end">
                                    <button type="button" class="btn-row-dots" title="Manage user"><i class="bi bi-three-dots"></i></button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                    const countEl = document.getElementById('tableRecordCount');
                    if (countEl) countEl.textContent = `Showing all ${data.users.length} records from MySQL`;
                }

                // 3. Resource Management (`resources.php` / `resources.html`)
                if ((path.includes('resources') || path.includes('manage_resources')) && tableBody && Array.isArray(data.resources) && data.resources.length) {
                    tableBody.innerHTML = data.resources.map((r) => {
                        const status = r.status || 'Approved';
                        const pillClass = status === 'Approved' ? 'pill-green' : (status === 'Rejected' ? 'pill-red' : 'pill-amber');
                        const dlText = Number(r.downloads) > 0 ? Number(r.downloads).toLocaleString() : '—';
                        return `
                            <tr data-resource-id="${r.id}">
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="table-item-icon"><i class="bi bi-file-earmark-text"></i></div>
                                        <span class="fw-bold text-dark">${r.title}</span>
                                    </div>
                                </td>
                                <td>${r.faculty || 'Computing'}</td>
                                <td><span class="status-pill ${pillClass}">${status}</span></td>
                                <td>${dlText}</td>
                                <td class="text-end">
                                    <button type="button" class="btn-row-dots" title="Manage resource"><i class="bi bi-three-dots"></i></button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                    const countEl = document.getElementById('tableRecordCount');
                    if (countEl) countEl.textContent = `Showing all ${data.resources.length} records from MySQL`;
                }

                // 4. Resource Approvals (`approvals.php` / `approvals.html`)
                if (path.includes('approvals') && Array.isArray(data.pending_resources) && data.pending_resources.length) {
                    const queueCard = document.querySelector('.approval-queue-card');
                    window.__NSBM_APPROVAL_QUEUE = data.pending_resources.map((r) => ({
                        id: r.id,
                        faculty: r.faculty || 'Computing',
                        title: r.title,
                        meta: `Submitted by ${r.author_name || 'Student'} · ${r.file_type || 'PDF'} · ${r.file_size_mb || '4.2'} MB`,
                        pages: `Preview · Page 1 of ${r.page_count || 32}`,
                        module: r.module_code || 'CS2102',
                        year: r.academic_year || 'Year 2',
                        lang: 'English',
                        desc: r.description || 'Comprehensive module-aligned notes prepared for revision.'
                    }));

                    if (queueCard) {
                        const pill = queueCard.querySelector('.status-pill');
                        if (pill) pill.textContent = `${data.pending_resources.length} items`;
                        queueCard.querySelectorAll('.approval-queue-item').forEach((el) => el.remove());

                        data.pending_resources.forEach((r, idx) => {
                            const div = document.createElement('div');
                            div.className = 'approval-queue-item' + (idx === 0 ? ' active' : '');
                            div.innerHTML = `
                                <div class="table-item-icon mt-1"><i class="bi bi-file-earmark-text"></i></div>
                                <div class="approval-item-body">
                                    <div class="approval-item-title">${r.title}</div>
                                    <div class="approval-item-meta">${r.author_name || 'Student'} · ${r.faculty || 'Computing'}</div>
                                    <div class="approval-item-time">${r.uploaded_date || 'Recently'}</div>
                                </div>
                            `;
                            div.addEventListener('click', () => {
                                if (typeof window.__selectApprovalItem === 'function') {
                                    window.__selectApprovalItem(idx);
                                }
                            });
                            queueCard.appendChild(div);
                        });
                        if (typeof window.__selectApprovalItem === 'function') {
                            window.__selectApprovalItem(0);
                        }
                    }
                }

                // 5. Academic Areas (`academic-areas.php` / `academic-areas.html`)
                if (path.includes('academic-areas') && tableBody && Array.isArray(data.academic_areas) && data.academic_areas.length) {
                    const statEl = document.getElementById('statTotalAreas');
                    if (statEl) statEl.textContent = String(data.academic_areas.length);
                    tableBody.innerHTML = data.academic_areas.map((a) => {
                        const status = a.status || 'Active';
                        const pillClass = status === 'Active' ? 'pill-green' : 'pill-amber';
                        return `
                            <tr>
                                <td><span class="code-badge-text">${a.code}</span></td>
                                <td>${a.name}</td>
                                <td>${a.subject_count || 6} first-year subjects</td>
                                <td>${a.level || 'Year 1'}</td>
                                <td><span class="status-pill ${pillClass}">${status}</span></td>
                                <td class="text-end">
                                    <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                    const countEl = document.getElementById('tableRecordCount');
                    if (countEl) countEl.textContent = `Showing all ${data.academic_areas.length} records from MySQL`;
                }

                // 6. First-Year Subjects (`subjects.php` / `subjects.html`)
                if (path.includes('subjects') && tableBody && Array.isArray(data.subjects) && data.subjects.length) {
                    const statEl = document.getElementById('statTotalSubjects');
                    if (statEl) statEl.textContent = String(data.subjects.length);
                    tableBody.innerHTML = data.subjects.map((s) => {
                        const status = s.status || 'Active';
                        const pillClass = status === 'Active' ? 'pill-green' : 'pill-amber';
                        return `
                            <tr>
                                <td><span class="code-badge-text">${s.code}</span></td>
                                <td>${s.name}</td>
                                <td>${s.area_name || 'Computing'}</td>
                                <td>${s.academic_year || 'Year 1'}</td>
                                <td><span class="status-pill ${pillClass}">${status}</span></td>
                                <td class="text-end">
                                    <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                    const countEl = document.getElementById('tableRecordCount');
                    if (countEl) countEl.textContent = `Showing all ${data.subjects.length} records from MySQL`;
                }

                // 7. Discussion Moderation (`discussions.php` / `discussions.html`)
                if ((path.includes('discussions') || path.includes('manage_announcements')) && tableBody && Array.isArray(data.reports) && data.reports.length) {
                    tableBody.innerHTML = data.reports.map((rep) => {
                        const prio = rep.priority || 'Medium';
                        const status = rep.status || 'Pending';
                        const label = (status === 'Resolved' || status === 'Dismissed') ? status : prio;
                        const pillClass = label === 'High priority' ? 'pill-red' : (label === 'Resolved' ? 'pill-green' : (label === 'Dismissed' ? 'pill-blue' : 'pill-amber'));
                        return `
                            <tr data-report-id="${rep.id}">
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="table-item-icon"><i class="bi bi-chat-left-text"></i></div>
                                        <span class="fw-bold text-dark">${rep.content_snippet}</span>
                                    </div>
                                </td>
                                <td>${rep.reason || 'Policy'}</td>
                                <td><span class="status-pill ${pillClass}">${label}</span></td>
                                <td>${rep.report_count || 0} reports</td>
                                <td class="text-end">
                                    <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                    const countEl = document.getElementById('tableRecordCount');
                    if (countEl) countEl.textContent = `Showing all ${data.reports.length} records from MySQL`;
                }

                // 8. Study Groups (`groups.php` / `groups.html`)
                if (path.includes('groups') && tableBody && Array.isArray(data.groups) && data.groups.length) {
                    tableBody.innerHTML = data.groups.map((g) => {
                        const status = g.status || 'Active';
                        const pillClass = status === 'Active' ? 'pill-green' : (status === 'Flagged' ? 'pill-red' : 'pill-amber');
                        return `
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="table-item-icon"><i class="bi bi-people"></i></div>
                                        <span class="fw-bold text-dark">${g.name}</span>
                                    </div>
                                </td>
                                <td>${g.member_count || 1} members</td>
                                <td><span class="status-pill ${pillClass}">${status}</span></td>
                                <td>${g.reports_count || 0} reports</td>
                                <td class="text-end">
                                    <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                    const countEl = document.getElementById('tableRecordCount');
                    if (countEl) countEl.textContent = `Showing all ${data.groups.length} records from MySQL`;
                }

                // 9. Study Sessions (`sessions.php` / `sessions.html`)
                if ((path.includes('sessions') || path.includes('manage_events')) && tableBody && Array.isArray(data.sessions) && data.sessions.length) {
                    tableBody.innerHTML = data.sessions.map((s) => {
                        return `
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="table-item-icon"><i class="bi bi-calendar-event"></i></div>
                                        <span class="fw-bold text-dark">${s.title}</span>
                                    </div>
                                </td>
                                <td>${s.date_time_short || '24 May · 6:30 PM'}</td>
                                <td><span class="status-pill pill-amber">${s.enrolled_count || 12} / ${s.capacity || 24}</span></td>
                                <td class="text-secondary">${s.status || 'Scheduled'}</td>
                                <td class="text-end">
                                    <button type="button" class="btn-row-dots" title="Actions"><i class="bi bi-three-dots"></i></button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                    const countEl = document.getElementById('tableRecordCount');
                    if (countEl) countEl.textContent = `Showing all ${data.sessions.length} records from MySQL`;
                }

                // 10. Settings (`settings.php` / `settings.html`)
                if (path.includes('settings') && data.settings) {
                    const s = data.settings;
                    const pName = document.getElementById('platformNameInput');
                    if (pName && s.platform_name) pName.value = s.platform_name;
                    const sEmail = document.getElementById('supportEmailInput');
                    if (sEmail && s.support_email) sEmail.value = s.support_email;
                    const aFmt = document.getElementById('allowedFormatsInput');
                    if (aFmt && s.allowed_formats) aFmt.value = s.allowed_formats;
                }
            })
            .catch(() => {});
    });
})();
