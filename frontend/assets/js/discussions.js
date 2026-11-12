/**
 * NSBM STUDY CIRCLE - DISCUSSIONS PLATFORM SCRIPT
 * Interactive search, filtering, upvoting, character counting & answer posting
 */

(function () {
    'use strict';

    const API_BASE = (function () {
        const inFrontend = window.location.pathname.includes('/frontend/');
        if (inFrontend) {
            return '../backend/controllers/discussion_controller.php';
        }
        return 'backend/controllers/discussion_controller.php';
    })();

    // Fallback discussions for offline / file:/// mode
    const FALLBACK_DISCUSSIONS = [
        {
            id: 1,
            faculty: 'Computing',
            category: 'Computing',
            category_badge: 'Computing',
            title: 'How do you choose between merge sort and quicksort in practice?',
            body: 'I understand the theory, but I am unsure which constraints matter most in a real implementation.',
            author_name: 'A. Perera',
            author_initials: 'AP',
            upvotes: 18,
            answers_count: 12,
            time_ago: '2 hours ago'
        },
        {
            id: 2,
            faculty: 'Business',
            category: 'Accounting',
            category_badge: 'Accounting',
            title: 'Can someone explain the difference between cash flow and profit?',
            body: "I'm revising this topic and would appreciate a simple explanation with a practical example.",
            author_name: 'A. Perera',
            author_initials: 'AP',
            upvotes: 15,
            answers_count: 8,
            time_ago: '5 hours ago'
        },
        {
            id: 3,
            faculty: 'Science',
            category: 'Science',
            category_badge: 'Science',
            title: 'How do you document a reliable laboratory experiment?',
            body: 'I understand the theory, but I am unsure which constraints matter most in a real implementation.',
            author_name: 'A. Perera',
            author_initials: 'AP',
            upvotes: 11,
            answers_count: 6,
            time_ago: 'Yesterday'
        },
        {
            id: 4,
            faculty: 'Computing',
            category: 'Databases',
            category_badge: 'Databases',
            title: 'Why does normalization sometimes increase query complexity?',
            body: "I'm revising this topic and would appreciate a simple explanation with a practical example.",
            author_name: 'A. Perera',
            author_initials: 'AP',
            upvotes: 9,
            answers_count: 4,
            time_ago: 'Yesterday'
        },
        {
            id: 5,
            faculty: 'Computing',
            category: 'Academic Skills',
            category_badge: 'Academic Skills',
            title: 'Best way to structure a literature review for a research proposal?',
            body: 'I understand the theory, but I am unsure which constraints matter most in a real implementation.',
            author_name: 'A. Perera',
            author_initials: 'AP',
            upvotes: 7,
            answers_count: 3,
            time_ago: '2 days ago'
        }
    ];

    function showToast(message, isSuccess = true) {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        const icon = isSuccess ? 'bi-check-circle-fill text-success' : 'bi-info-circle-fill text-warning';
        const toastEl = document.createElement('div');
        toastEl.className = 'toast align-items-center text-bg-dark border-0 show shadow';
        toastEl.setAttribute('role', 'alert');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi ${icon}"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        container.appendChild(toastEl);
        setTimeout(() => {
            toastEl.classList.remove('show');
            setTimeout(() => toastEl.remove(), 400);
        }, 3000);
    }

    // Parse a fetch() response as JSON. Some hosts (e.g. InfinityFree's browser
    // check) can answer with an HTML page instead, so never trust response.json().
    async function readJson(response) {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            showToast('Something went wrong loading data. Please refresh the page and try again.', false);
            const err = new Error('Non-JSON response from server');
            err.nonJson = true;
            throw err;
        }
    }

    // =========================================================================
    // 1. DISCUSSIONS LIST PAGE
    // =========================================================================
    function initDiscussionListPage() {
        const listContainer = document.getElementById('discussionsListContainer');
        if (!listContainer) return;

        const searchInput = document.getElementById('discussionSearchInput');
        const filterTabs = document.querySelectorAll('.discussion-filter-tab');
        const subjectLinks = document.querySelectorAll('.subject-browse-item');

        let activeFilter = 'recent';
        let activeCategory = 'all';
        let debounceTimer = null;

        async function fetchDiscussions() {
            const query = (searchInput ? searchInput.value : '').trim();
            const params = new URLSearchParams({
                action: 'list',
                filter: activeFilter,
                category: activeCategory,
                q: query
            });

            try {
                const response = await fetch(`${API_BASE}?${params.toString()}`);
                if (response.ok) {
                    const data = await readJson(response);
                    if (data.success && Array.isArray(data.discussions)) {
                        renderDiscussions(data.discussions);
                        return;
                    }
                }
            } catch (err) {
                // Offline fallback
            }

            // Client-side fallback
            let filtered = FALLBACK_DISCUSSIONS.filter(item => {
                if (activeCategory !== 'all') {
                    const cat = (item.category_badge || item.category || '').toLowerCase();
                    if (cat !== activeCategory.toLowerCase()) return false;
                }
                if (activeFilter === 'unanswered' && item.answers_count > 0) return false;
                if (query) {
                    const m = (item.title + ' ' + item.body).toLowerCase();
                    if (!m.includes(query.toLowerCase())) return false;
                }
                return true;
            });

            if (activeFilter === 'popular') {
                filtered.sort((a, b) => b.upvotes - a.upvotes);
            }

            renderDiscussions(filtered);
        }

        function renderDiscussions(items) {
            const isPhp = window.location.pathname.endsWith('.php');
            const detailBase = isPhp ? 'discussion-details.php' : 'discussion-details.html';

            if (items.length === 0) {
                listContainer.innerHTML = `
                    <div class="card border p-5 text-center bg-white rounded-3">
                        <i class="bi bi-chat-left-dots text-muted fs-1 mb-2"></i>
                        <h5 class="fw-bold text-dark">No discussions found</h5>
                        <p class="text-secondary small mb-3">Try asking the community or clearing your search filters.</p>
                        <button type="button" class="btn btn-outline-success btn-sm m-auto" id="resetDiscussionsFilter">
                            Reset filters
                        </button>
                    </div>
                `;
                const resetBtn = document.getElementById('resetDiscussionsFilter');
                if (resetBtn) {
                    resetBtn.onclick = () => {
                        if (searchInput) searchInput.value = '';
                        activeFilter = 'recent';
                        activeCategory = 'all';
                        filterTabs.forEach(t => t.classList.toggle('active', t.getAttribute('data-filter') === 'recent'));
                        subjectLinks.forEach(s => s.classList.remove('active'));
                        fetchDiscussions();
                    };
                }
                return;
            }

            let html = '';
            items.forEach((d, idx) => {
                const category = d.category_badge || d.category || d.faculty || 'Computing';
                const catClass = 'category-' + category.toLowerCase().replace(/[^a-z0-9]/g, '');
                const delay = (idx * 0.05).toFixed(2);

                html += `
                    <a href="${detailBase}?id=${d.id}" class="discussion-card animate-fade-in-up" style="animation-delay: ${delay}s;">
                        <div class="discussion-stats-box">
                            <div class="stat-item">
                                <div class="stat-count">${d.upvotes}</div>
                                <div class="stat-text">helpful</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-count">${d.answers_count}</div>
                                <div class="stat-text">answers</div>
                            </div>
                        </div>

                        <div class="discussion-content-box">
                            <span class="discussion-category-pill ${catClass}">${category}</span>
                            <h3 class="discussion-item-title">${d.title}</h3>
                            <p class="discussion-item-snippet">${d.body}</p>
                            <div class="discussion-item-meta">
                                Asked by ${d.author_name} · ${d.time_ago}
                            </div>
                        </div>

                        <i class="bi bi-arrow-right discussion-arrow-icon"></i>
                    </a>
                `;
            });

            listContainer.innerHTML = html;
        }

        // Tab click handlers
        filterTabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                filterTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                activeFilter = tab.getAttribute('data-filter') || 'recent';
                fetchDiscussions();
            });
        });

        // Subject sidebar click handlers
        subjectLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const wasActive = link.classList.contains('active');
                subjectLinks.forEach(s => s.classList.remove('active'));
                if (!wasActive) {
                    link.classList.add('active');
                    activeCategory = link.getAttribute('data-subject') || 'all';
                } else {
                    activeCategory = 'all';
                }
                fetchDiscussions();
            });
        });

        // Search input handler
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchDiscussions, 250);
            });
        }

        // "Ask a Question" Modal form handling
        const askForm = document.getElementById('askQuestionForm');
        if (askForm) {
            askForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitBtn = askForm.querySelector('button[type="submit"]');
                const orig = submitBtn ? submitBtn.innerHTML : 'Submit';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Posting...';
                }

                try {
                    const fd = new FormData(askForm);
                    fd.append('action', 'ask');

                    const response = await fetch(API_BASE, { method: 'POST', body: fd });
                    const res = await readJson(response);

                    if (res.success) {
                        showToast(res.message || 'Question posted successfully!', true);
                        askForm.reset();
                        const modalEl = document.getElementById('askQuestionModal');
                        if (modalEl && window.bootstrap) {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                        if (res.redirect) {
                            setTimeout(() => { window.location.href = res.redirect; }, 800);
                        } else {
                            fetchDiscussions();
                        }
                    } else {
                        showToast(res.message || 'Failed to post question', false);
                    }
                } catch (err) {
                    if (err && err.nonJson) return; // friendly toast already shown
                    showToast('Question posted (offline demo mode)!', true);
                    const modalEl = document.getElementById('askQuestionModal');
                    if (modalEl && window.bootstrap) {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = orig;
                    }
                }
            });
        }

        // Initial fetch
        fetchDiscussions();

        // Auto-open modal if arrived with ?action=ask or ?action=start
        const urlParams = new URLSearchParams(window.location.search);
        const actionParam = urlParams.get('action');
        if (actionParam === 'ask' || actionParam === 'start') {
            const modalEl = document.getElementById('askQuestionModal');
            if (modalEl && window.bootstrap) {
                setTimeout(() => {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }, 200);
            }
        }
    }

    // =========================================================================
    // 2. DISCUSSION DETAIL PAGE
    // =========================================================================
    function initDiscussionDetailPage() {
        const detailContainer = document.getElementById('discussionDetailContainer');
        if (!detailContainer) return;

        const urlParams = new URLSearchParams(window.location.search);
        const questionId = parseInt(urlParams.get('id') || '1', 10);

        // Character counter on Answer textarea
        const answerTextarea = document.getElementById('answerTextarea');
        const charCounter = document.getElementById('charCounter');
        if (answerTextarea && charCounter) {
            answerTextarea.addEventListener('input', () => {
                const len = answerTextarea.value.length;
                charCounter.textContent = `${len}/2000 characters`;
                if (len > 2000) {
                    charCounter.classList.add('text-danger');
                } else {
                    charCounter.classList.remove('text-danger');
                }
            });
        }

        // Upvote Question Button
        const upvoteBtn = document.getElementById('questionUpvoteBtn');
        const upvoteCountEl = document.getElementById('questionUpvoteCount');
        if (upvoteBtn && upvoteCountEl) {
            upvoteBtn.addEventListener('click', async () => {
                if (upvoteBtn.classList.contains('voted')) return;
                upvoteBtn.classList.add('voted');
                let count = parseInt(upvoteCountEl.textContent, 10);
                upvoteCountEl.textContent = count + 1;
                showToast('Marked this question as helpful!', true);

                try {
                    const fd = new FormData();
                    fd.append('action', 'upvote_question');
                    fd.append('id', questionId);
                    await fetch(API_BASE, { method: 'POST', body: fd });
                } catch (e) {}
            });
        }

        // Answer Submission Form
        const postAnswerForm = document.getElementById('postAnswerForm');
        const answersList = document.getElementById('answersListContainer');
        const answersCountHeader = document.getElementById('answersCountHeader');

        if (postAnswerForm) {
            postAnswerForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const body = (answerTextarea ? answerTextarea.value : '').trim();
                if (!body) {
                    showToast('Please type your explanation before submitting.', false);
                    return;
                }

                const submitBtn = postAnswerForm.querySelector('button[type="submit"]');
                const orig = submitBtn ? submitBtn.innerHTML : 'Post answer';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Posting...';
                }

                try {
                    const fd = new FormData();
                    fd.append('action', 'answer');
                    fd.append('discussion_id', questionId);
                    fd.append('body', body);

                    const resp = await fetch(API_BASE, { method: 'POST', body: fd });
                    const res = await readJson(resp);

                    if (res.success) {
                        showToast('Your answer has been posted!', true);
                        answerTextarea.value = '';
                        if (charCounter) charCounter.textContent = '0/2000 characters';

                        // Append new answer to list dynamically with smooth animation
                        if (answersList) {
                            const newCard = document.createElement('div');
                            newCard.className = 'answer-card animate-fade-in-up';
                            newCard.innerHTML = `
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="author-circle-avatar bg-success text-white">YOU</div>
                                    <div>
                                        <div class="answer-author-name">Student Contributor</div>
                                        <div class="answer-author-role">Student member · Just now</div>
                                    </div>
                                </div>
                                <div class="answer-body-text">${body}</div>
                                <div class="answer-actions-bar">
                                    <button class="btn-answer-helpful" type="button"><i class="bi bi-arrow-up"></i> Helpful (0)</button>
                                    <button class="btn-answer-reply" type="button">Reply</button>
                                </div>
                            `;
                            answersList.appendChild(newCard);

                            if (answersCountHeader && res.answers_count) {
                                answersCountHeader.textContent = `${res.answers_count} Answers`;
                            }
                        }
                    } else {
                        showToast(res.message || 'Failed to submit answer', false);
                    }
                } catch (err) {
                    if (err && err.nonJson) return; // friendly toast already shown
                    // Local fallback
                    showToast('Your answer has been posted (Demo)!', true);
                    if (answersList) {
                        const newCard = document.createElement('div');
                        newCard.className = 'answer-card animate-fade-in-up';
                        newCard.innerHTML = `
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="author-circle-avatar bg-success text-white">YOU</div>
                                <div>
                                    <div class="answer-author-name">Student Contributor</div>
                                    <div class="answer-author-role">Student member · Just now</div>
                                </div>
                            </div>
                            <div class="answer-body-text">${body}</div>
                            <div class="answer-actions-bar">
                                <button class="btn-answer-helpful" type="button"><i class="bi bi-arrow-up"></i> Helpful (0)</button>
                                <button class="btn-answer-reply" type="button">Reply</button>
                            </div>
                        `;
                        answersList.appendChild(newCard);
                    }
                    answerTextarea.value = '';
                    if (charCounter) charCounter.textContent = '0/2000 characters';
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = orig;
                    }
                }
            });
        }

        // Attach helpful upvote listeners on existing answers
        document.querySelectorAll('.btn-answer-helpful').forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.classList.contains('voted')) return;
                btn.classList.add('voted');
                const match = btn.textContent.match(/\d+/);
                const current = match ? parseInt(match[0], 10) : 0;
                btn.innerHTML = `<i class="bi bi-arrow-up"></i> Helpful (${current + 1})`;
                showToast('Marked this answer as helpful!', true);
            });
        });

        // Reply button smooth scroll to answer form
        document.querySelectorAll('.btn-answer-reply').forEach(btn => {
            btn.addEventListener('click', () => {
                const formCard = document.getElementById('postAnswerCardSection');
                if (formCard) {
                    formCard.scrollIntoView({ behavior: 'smooth' });
                    if (answerTextarea) answerTextarea.focus();
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initDiscussionListPage();
        initDiscussionDetailPage();
    });
})();

