/**
 * NSBM STUDY CIRCLE - STUDY GROUPS JAVASCRIPT
 * Live search, category filtering, join toggle & interactive animations
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Elements
    const searchInput = document.getElementById('groupSearchInput');
    const categoryBtns = document.querySelectorAll('.category-pill-btn');
    const groupsContainer = document.getElementById('studyGroupsGrid');
    const totalCountBadge = document.getElementById('totalGroupsCount');
    const createGroupForm = document.getElementById('createStudyGroupForm');
    const joinBtn = document.getElementById('joinGroupBtn');

    let currentCategory = 'All';
    let searchQuery = '';

    // 2. Category Filter Switcher
    if (categoryBtns.length > 0) {
        categoryBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                categoryBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentCategory = btn.getAttribute('data-category') || 'All';
                filterGroups();
            });
        });
    }

    // 3. Search Input Listener (Debounced)
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                searchQuery = e.target.value.trim().toLowerCase();
                filterGroups();
            }, 180);
        });
    }

    // 4. Client-side Filter with Smooth Animation
    function filterGroups() {
        const cards = document.querySelectorAll('.study-card-item');
        let visibleCount = 0;

        cards.forEach(card => {
            const cardCat = (card.getAttribute('data-category') || '').toLowerCase();
            const cardName = (card.getAttribute('data-name') || '').toLowerCase();
            const cardDesc = (card.getAttribute('data-desc') || '').toLowerCase();
            const cardCode = (card.getAttribute('data-code') || '').toLowerCase();

            const matchesCategory = (currentCategory.toLowerCase() === 'all' || cardCat === currentCategory.toLowerCase() || cardName.includes(currentCategory.toLowerCase()));
            const matchesSearch = !searchQuery || cardName.includes(searchQuery) || cardDesc.includes(searchQuery) || cardCode.includes(searchQuery) || cardCat.includes(searchQuery);

            if (matchesCategory && matchesSearch) {
                card.style.display = 'flex';
                card.classList.add('animate-fade-in-up');
                visibleCount++;
            } else {
                card.style.display = 'none';
                card.classList.remove('animate-fade-in-up');
            }
        });

        // Show empty message if nothing found
        let emptyNotice = document.getElementById('noGroupsNotice');
        if (visibleCount === 0) {
            if (!emptyNotice && groupsContainer) {
                emptyNotice = document.createElement('div');
                emptyNotice.id = 'noGroupsNotice';
                emptyNotice.className = 'col-12 text-center py-5';
                emptyNotice.innerHTML = `
                    <div class="py-4">
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <h5 class="fw-bold mt-3 mb-1">No study groups found</h5>
                        <p class="text-muted small">Try searching with different keywords or switch categories.</p>
                        <button class="btn btn-outline-secondary btn-sm mt-2" onclick="resetFilters()">Reset Filters</button>
                    </div>
                `;
                groupsContainer.appendChild(emptyNotice);
            }
            if (emptyNotice) emptyNotice.style.display = 'block';
        } else if (emptyNotice) {
            emptyNotice.style.display = 'none';
        }

        if (totalCountBadge) {
            totalCountBadge.textContent = `${visibleCount} communities found`;
        }
    }

    // Window helper to reset
    window.resetFilters = function() {
        if (searchInput) searchInput.value = '';
        searchQuery = '';
        if (categoryBtns.length > 0) {
            categoryBtns.forEach(b => b.classList.remove('active'));
            categoryBtns[0].classList.add('active');
        }
        currentCategory = 'All';
        filterGroups();
    };

    // 5. Join Group Interaction (Detail Page)
    if (joinBtn) {
        joinBtn.addEventListener('click', async () => {
            const groupId = joinBtn.getAttribute('data-group-id') || 1;
            const isCurrentlyJoined = joinBtn.classList.contains('joined');
            const action = isCurrentlyJoined ? 'leave' : 'join';

            // Optimistic toggle
            if (!isCurrentlyJoined) {
                joinBtn.classList.add('joined');
                joinBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Joined (Leave)';
            } else {
                joinBtn.classList.remove('joined');
                joinBtn.innerHTML = 'Join this group';
            }

            try {
                const response = await fetch(`../backend/controllers/study_group_controller.php?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: groupId })
                });
                const data = await response.json();
                
                // Update member count display if present
                const memberCountEl = document.getElementById('detailsMemberCount');
                if (memberCountEl && data.success) {
                    let currentCount = parseInt(memberCountEl.textContent.replace(/\D/g, ''), 10) || 48;
                    currentCount = isCurrentlyJoined ? Math.max(1, currentCount - 1) : currentCount + 1;
                    memberCountEl.innerHTML = `<i class="bi bi-people me-1"></i> ${currentCount} members`;
                }

                // Show toast or snackbar
                showGroupToast(data.message || (isCurrentlyJoined ? 'Left group.' : 'Joined study group!'));
            } catch (err) {
                console.error(err);
            }
        });
    }

    // 6. Create Study Group Form
    if (createGroupForm) {
        createGroupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = createGroupForm.querySelector('button[type="submit"]');
            const origText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';

            const formData = new FormData(createGroupForm);
            const payload = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('../backend/controllers/study_group_controller.php?action=create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const res = await response.json();
                if (res.success) {
                    showGroupToast('Study Group created successfully! Redirecting...');
                    setTimeout(() => {
                        window.location.href = `study-group-details.php?id=${res.group_id || 1}`;
                    }, 800);
                } else {
                    alert(res.message || 'Could not create study group.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origText;
                }
            } catch (err) {
                console.error(err);
                submitBtn.disabled = false;
                submitBtn.innerHTML = origText;
            }
        });
    }

    // Simple toast helper
    function showGroupToast(msg) {
        let toast = document.getElementById('studyGroupToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'studyGroupToast';
            toast.style.cssText = `
                position: fixed;
                bottom: 24px;
                right: 24px;
                background: #0f172a;
                color: #ffffff;
                padding: 12px 24px;
                border-radius: 10px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                z-index: 9999;
                font-weight: 600;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                gap: 8px;
                transform: translateY(100px);
                transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            `;
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="bi bi-info-circle-fill text-success"></i> ${msg}`;
        toast.style.transform = 'translateY(0)';
        setTimeout(() => {
            toast.style.transform = 'translateY(120px)';
        }, 3200);
    }
});

