// Equipment reservation form toggle and auto-advance
document.addEventListener('DOMContentLoaded', function () {
    function closeReservePanel(targetId, btn, defaultLabel) {
        var panel = document.getElementById(targetId);
        if (panel) panel.classList.remove('is-open');
        if (btn) { btn.textContent = defaultLabel; btn.classList.remove('is-open'); }
    }

    document.querySelectorAll('.equip-reserve-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form  = document.getElementById(btn.dataset.target);
            var other = document.getElementById(btn.dataset.other);
            if (!form) return;
            // Close the "Reserve Now" panel if open
            var nowBtn = btn.closest('.equip-reserve-wrap').querySelector('.equip-reserve-now-toggle');
            closeReservePanel(btn.dataset.other, nowBtn, 'Reserve Now');
            var isOpen = form.classList.toggle('is-open');
            btn.textContent = isOpen ? 'Cancel' : 'Reserve a slot';
            btn.classList.toggle('is-open', isOpen);
            var card = btn.closest('.equip-card');
            if (card) card.classList.toggle('equip-card--reserving', isOpen);
        });
    });

    document.querySelectorAll('.equip-reserve-now-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = document.getElementById(btn.dataset.target);
            if (!form) return;
            // Close the "Reserve a slot" panel if open
            var slotBtn = btn.closest('.equip-reserve-wrap').querySelector('.equip-reserve-toggle');
            closeReservePanel(btn.dataset.other, slotBtn, 'Reserve a slot');
            if (slotBtn) {
                var card = slotBtn.closest('.equip-card');
                if (card) card.classList.remove('equip-card--reserving');
            }
            var isOpen = form.classList.toggle('is-open');
            btn.textContent = isOpen ? 'Cancel' : 'Reserve Now';
            btn.classList.toggle('is-open', isOpen);
        });
    });

    document.querySelectorAll('.equip-res-edit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = document.getElementById(btn.dataset.target);
            if (!form) return;
            var isOpen = form.classList.toggle('is-open');
            btn.textContent = isOpen ? 'Close' : 'Edit';
            btn.classList.toggle('is-open', isOpen);
        });
    });

    // Equipment filter pills - client-side filtering
    var equipPills = document.querySelectorAll('.equip-pill');
    if (equipPills.length) {
        var equipCards = document.querySelectorAll('#equip-grid .equip-card');
        var equipEmpty = document.getElementById('equip-empty');
        var equipGrid  = document.getElementById('equip-grid');

        function applyEquipFilter(type) {
            equipPills.forEach(function (p) {
                p.classList.toggle('active', (p.dataset.filter || '') === type);
            });
            var visible = 0;
            equipCards.forEach(function (card) {
                var show = !type || card.dataset.type === type;
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            if (equipEmpty) equipEmpty.style.display = visible === 0 ? '' : 'none';
            if (equipGrid)  equipGrid.style.display  = visible === 0 ? 'none' : '';
            var url = new URL(window.location.href);
            if (type) url.searchParams.set('type', type);
            else url.searchParams.delete('type');
            history.replaceState(null, '', url.toString());
        }

        equipPills.forEach(function (pill) {
            pill.addEventListener('click', function (e) {
                e.preventDefault();
                applyEquipFilter(pill.dataset.filter || '');
            });
        });

        applyEquipFilter(new URLSearchParams(window.location.search).get('type') || '');
    }

    document.addEventListener('input', function (e) {
        var input = e.target;
        if (!input.classList.contains('equip-res-tiny')) return;
        if (input.value.length >= parseInt(input.maxLength)) {
            var all  = Array.from(input.closest('form').querySelectorAll('.equip-res-tiny'));
            var next = all[all.indexOf(input) + 1];
            if (next) { next.focus(); next.select(); }
        }
    });
});

function applySelectFilter(param, value) {
    var url = new URL(window.location.href);
    if (value) url.searchParams.set(param, value); else url.searchParams.delete(param);
    window.location.href = url.toString();
}

function escapeHtml(str) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

function openDisputeModal(id, data) {
    document.getElementById('modal-id').value = id;
    document.getElementById('modal-status').value = data.status;
    document.getElementById('modal-response').value = data.response;
    document.getElementById('modal-meta').innerHTML =
        '<strong>' + escapeHtml(data.author) + '</strong> &mdash; ' + escapeHtml(data.category) +
        ' &mdash; <span class="dispute-date">' + escapeHtml(data.date) + '</span>';
    document.getElementById('modal-desc').textContent = data.desc;
    document.getElementById('dispute-modal').classList.add('is-open');
}

function closeDisputeModal() {
    document.getElementById('dispute-modal').classList.remove('is-open');
}

document.addEventListener('DOMContentLoaded', function () {

    // Flash message auto-dismiss
    document.querySelectorAll('.flash').forEach(function (flash) {
        var timer = setTimeout(function () { dismissFlash(flash); }, 4000);
        flash.style.cursor = 'pointer';
        flash.title = 'Click to dismiss';
        flash.addEventListener('click', function () {
            clearTimeout(timer);
            dismissFlash(flash);
        });
    });

    function dismissFlash(el) {
        el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        el.style.opacity    = '0';
        el.style.transform  = 'translateY(-6px)';
        setTimeout(function () {
            if (el.parentNode) el.parentNode.removeChild(el);
        }, 420);
    }

    // Back links
    document.querySelectorAll('a.back-link[href="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            history.back();
        });
    });

    // Confirm dialogs for buttons and forms
    document.querySelectorAll('button[data-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm)) e.preventDefault();
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(this.dataset.confirm)) e.preventDefault();
        });
    });

    // Auto-submit selects
    document.querySelectorAll('select[data-auto-submit]').forEach(function (sel) {
        sel.addEventListener('change', function () { this.form.submit(); });
    });

    // Filter selects
    document.querySelectorAll('select[data-filter-param]').forEach(function (sel) {
        sel.addEventListener('change', function () {
            applySelectFilter(this.dataset.filterParam, this.value);
        });
    });

    // Report form character counter and submit confirmation
    var reportTextarea = document.querySelector('.disputes-page .dispute-textarea');
    if (reportTextarea) {
        var counter = document.createElement('span');
        counter.className = 'dispute-char-counter';
        counter.setAttribute('aria-live', 'polite');
        reportTextarea.parentNode.appendChild(counter);

        function updateCounter() {
            var len = reportTextarea.value.length;
            var min = 10;
            counter.textContent = len < min
                ? (min - len) + ' more character' + (min - len !== 1 ? 's' : '') + ' required'
                : len + ' character' + (len !== 1 ? 's' : '');
            counter.classList.toggle('dispute-char-counter--ok',  len >= min);
            counter.classList.toggle('dispute-char-counter--bad', len > 0 && len < min);
        }
        reportTextarea.addEventListener('input', updateCounter);
        updateCounter();

        var reportForm = reportTextarea.closest('form');
        if (reportForm) {
            reportForm.addEventListener('submit', function (e) {
                var category = reportForm.querySelector('select[name="category"]');
                var catLabel = category && category.options[category.selectedIndex]
                    ? category.options[category.selectedIndex].text
                    : 'Unknown';
                if (!confirm('Submit a report under "' + catLabel + '"?\n\nAn admin will review it shortly.')) {
                    e.preventDefault();
                }
            });
        }
    }

    // Password confirmation validator
    var newPw     = document.getElementById('newPw');
    var confirmPw = document.getElementById('confirmPw');

    if (newPw && confirmPw) {
        var pwMsg = document.createElement('span');
        pwMsg.className = 'pw-match-msg';
        confirmPw.parentNode.appendChild(pwMsg);

        function checkPasswords() {
            var a = newPw.value;
            var b = confirmPw.value;
            if (!b) {
                pwMsg.textContent = '';
                confirmPw.setCustomValidity('');
                return;
            }
            if (a === b) {
                pwMsg.textContent = '✓ Passwords match';
                pwMsg.className   = 'pw-match-msg pw-match-msg--ok';
                confirmPw.setCustomValidity('');
            } else {
                pwMsg.textContent = '✗ Passwords do not match';
                pwMsg.className   = 'pw-match-msg pw-match-msg--bad';
                confirmPw.setCustomValidity('Passwords do not match');
            }
        }

        newPw.addEventListener('input', checkPasswords);
        confirmPw.addEventListener('input', checkPasswords);
    }

    // Staggered card entrance animation
    document.querySelectorAll('.class-card').forEach(function (card, i) {
        card.style.animationDelay = (i * 0.045) + 's';
    });

    // Live search - filters class cards client-side
    var searchInput  = document.getElementById('class-search');
    var resultsCount = document.getElementById('filter-results-count');
    var cards        = Array.from(document.querySelectorAll('.class-card'));
    var grid         = document.querySelector('.classes-grid');

    if (searchInput && cards.length) {
        var phpCount  = resultsCount ? resultsCount.textContent.trim() : null;
        var searchMsg = null;

        searchInput.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            var visible = 0;

            cards.forEach(function (card) {
                var matches = !q || card.textContent.toLowerCase().includes(q);
                card.style.display = matches ? '' : 'none';
                if (matches) visible++;
            });

            if (resultsCount) {
                if (q) {
                    resultsCount.textContent = visible + ' class' + (visible !== 1 ? 'es' : '') + ' found';
                    resultsCount.style.display = '';
                } else {
                    resultsCount.textContent = phpCount || '';
                    resultsCount.style.display = phpCount ? '' : 'none';
                }
            }

            if (!searchMsg) {
                searchMsg = document.createElement('p');
                searchMsg.className = 'no-results';
                searchMsg.style.display = 'none';
                grid.prepend(searchMsg);
            }
            searchMsg.innerHTML = 'No classes match &ldquo;<strong>' + escapeHtml(q) + '</strong>&rdquo;. <a href="#" class="filter-clear-inline" id="clear-search-btn">Clear search</a>';
            searchMsg.style.display = (visible === 0 && q) ? '' : 'none';

            var clearBtn = document.getElementById('clear-search-btn');
            if (clearBtn) {
                clearBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    searchInput.value = '';
                    searchInput.dispatchEvent(new Event('input'));
                });
            }
        });
    }

    // Trainer dashboard calendar modal
    const modal = document.getElementById('class-modal');
    if (modal) {
        document.querySelectorAll('.cal-class-card').forEach(function (card) {
            card.addEventListener('click', function () {
                document.getElementById('modal-name').textContent     = this.dataset.name;
                document.getElementById('modal-time').textContent     = this.dataset.time;
                document.getElementById('modal-duration').textContent = this.dataset.duration + ' min';
                document.getElementById('modal-enrolled').textContent = this.dataset.enrolled + ' / ' + this.dataset.capacity;
                document.getElementById('modal-class-id').value       = this.dataset.id;

                var daySelect = document.getElementById('modal-reschedule-day');
                for (var i = 0; i < daySelect.options.length; i++) {
                    daySelect.options[i].selected = daySelect.options[i].value === this.dataset.day;
                }
                document.getElementById('modal-reschedule-time').value = this.dataset.time;

                var roster = JSON.parse(this.dataset.roster);
                var tbody  = document.getElementById('modal-roster');
                tbody.innerHTML = '';
                if (roster.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="2" style="color:var(--text-muted);text-align:center">No members enrolled yet</td></tr>';
                } else {
                    roster.forEach(function (m) {
                        var tr = document.createElement('tr');
                        tr.innerHTML = '<td>' + m.name + '</td><td>' + m.username + '</td>';
                        tbody.appendChild(tr);
                    });
                }

                modal.style.display = 'flex';
            });
        });

        document.getElementById('modal-close').addEventListener('click', function () {
            modal.style.display = 'none';
        });

        modal.addEventListener('click', function (e) {
            if (e.target === this) this.style.display = 'none';
        });
    }

    // Profile picture preview
    const editBtn  = document.getElementById('avatar-edit-btn');
    const editMenu = document.getElementById('avatar-edit-menu');
    const fileInput = document.getElementById('profile_picture');
    const saveBtn   = document.getElementById('avatar-upload-btn');

    if (editBtn && editMenu) {
        editBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            editMenu.classList.toggle('open');
            editBtn.classList.toggle('active');
        });

        document.addEventListener('click', function () {
            editMenu.classList.remove('open');
            editBtn.classList.remove('active');
        });

        editMenu.addEventListener('click', function (e) { e.stopPropagation(); });
    }

    if (fileInput && saveBtn) {
        fileInput.addEventListener('change', function () {
            if (fileInput.files[0]) saveBtn.style.display = 'block';
        });
    }

    // Class cards index page modal
    var classInfoModal = document.getElementById('class-info-modal');
    if (classInfoModal) {
        document.querySelectorAll('.class-card[data-sessions]').forEach(function (card) {
            card.addEventListener('click', function () {
                document.getElementById('cim-name').textContent        = this.dataset.name;
                document.getElementById('cim-type').textContent        = this.dataset.type;
                document.getElementById('cim-description').textContent = this.dataset.description;
                document.getElementById('cim-trainers').textContent    = this.dataset.trainers;

                var sessions = JSON.parse(this.dataset.sessions);
                var tbody = document.getElementById('cim-sessions');
                tbody.innerHTML = '';
                sessions.forEach(function (s) {
                    var tr = document.createElement('tr');
                    tr.innerHTML = '<td>' + s.day + '</td>' +
                        '<td>' + s.time + '</td>' +
                        '<td>' + s.duration + ' min</td>' +
                        '<td>' + s.enrolled + ' / ' + s.capacity + '</td>' +
                        '<td><a href="/pages/class.php?id=' + s.id + '" class="btn-see-more">See more</a></td>';
                    tbody.appendChild(tr);
                });

                classInfoModal.style.display = 'flex';
            });
        });

        document.getElementById('class-modal-close').addEventListener('click', function () {
            classInfoModal.style.display = 'none';
        });

        classInfoModal.addEventListener('click', function (e) {
            if (e.target === this) this.style.display = 'none';
        });
    }

    // View toggle between Classes and Schedule
    var btnViewClasses  = document.getElementById('btn-view-classes');
    var btnViewSchedule = document.getElementById('btn-view-schedule');
    var viewClassesEl   = document.getElementById('view-classes');
    var viewScheduleEl  = document.getElementById('view-schedule');

    if (btnViewClasses && btnViewSchedule) {
        if (sessionStorage.getItem('classView') === 'schedule') {
            btnViewSchedule.classList.add('active');
            btnViewClasses.classList.remove('active');
            viewClassesEl.style.display = 'none';
            viewScheduleEl.style.display = '';
        }

        btnViewClasses.addEventListener('click', function () {
            sessionStorage.setItem('classView', 'classes');
            btnViewClasses.classList.add('active');
            btnViewSchedule.classList.remove('active');
            viewClassesEl.style.display = '';
            viewScheduleEl.style.display = 'none';
        });

        btnViewSchedule.addEventListener('click', function () {
            sessionStorage.setItem('classView', 'schedule');
            btnViewSchedule.classList.add('active');
            btnViewClasses.classList.remove('active');
            viewClassesEl.style.display = 'none';
            viewScheduleEl.style.display = '';
        });
    }

    // Dispute modal
    var disputeModal = document.getElementById('dispute-modal');
    if (disputeModal) {
        document.querySelectorAll('[data-dispute-id]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                openDisputeModal(parseInt(this.dataset.disputeId), JSON.parse(this.dataset.dispute));
            });
        });

        document.querySelectorAll('.close-dispute-modal').forEach(function (btn) {
            btn.addEventListener('click', function () { closeDisputeModal(); });
        });

        disputeModal.addEventListener('click', function (e) {
            if (e.target === this) closeDisputeModal();
        });
    }

    // Nutrition plans modal
    var planModal = document.getElementById('plan-modal');
    if (planModal) {
        document.getElementById('btn-new-plan').addEventListener('click', function () {
            planModal.hidden = false;
        });
        document.getElementById('btn-cancel-plan').addEventListener('click', function () {
            planModal.hidden = true;
        });
        planModal.addEventListener('click', function (e) {
            if (e.target === planModal) planModal.hidden = true;
        });

        var mealCount = 3;
        document.getElementById('btn-add-meal').addEventListener('click', function () {
            var i = mealCount++;
            var container = document.getElementById('meals-container');
            var row = document.createElement('div');
            row.className = 'meal-row';
            row.innerHTML =
                '<span class="meal-index">' + (i + 1) + '</span>' +
                '<input type="text"   name="meals[' + i + '][name]"        placeholder="Meal name"     maxlength="80">' +
                '<input type="text"   name="meals[' + i + '][time_of_day]" placeholder="e.g. 13:00"    maxlength="20">' +
                '<input type="number" name="meals[' + i + '][calories]"    placeholder="kcal" min="0" max="5000">' +
                '<input type="text"   name="meals[' + i + '][notes]"       placeholder="Notes (optional)" maxlength="200">';
            container.appendChild(row);
        });

        document.querySelectorAll('.btn-meals-toggle').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                var planId = btn.dataset.plan;
                var box = document.getElementById('meals-' + planId);
                if (!box.hidden) { box.hidden = true; btn.textContent = 'View Meals'; return; }
                box.hidden = false;
                btn.textContent = 'Hide Meals';
                if (box.dataset.loaded) return;
                box.dataset.loaded = '1';
                try {
                    var res = await fetch('/actions/action_get_meals.php?plan_id=' + planId);
                    var data = await res.json();
                    if (!data.meals || data.meals.length === 0) {
                        box.innerHTML = '<p class="hint">No meals added.</p>';
                        return;
                    }
                    var html = '<table class="meals-table"><thead><tr><th>Meal</th><th>Time</th><th>kcal</th><th>Notes</th></tr></thead><tbody>';
                    data.meals.forEach(function (m) {
                        html += '<tr>' +
                            '<td>' + escapeHtml(m.name) + '</td>' +
                            '<td>' + escapeHtml(m.time_of_day) + '</td>' +
                            '<td>' + (m.calories > 0 ? m.calories : '—') + '</td>' +
                            '<td>' + escapeHtml(m.notes) + '</td>' +
                            '</tr>';
                    });
                    html += '</tbody></table>';
                    box.innerHTML = html;
                } catch (e) {
                    box.innerHTML = '<p class="hint">Could not load meals.</p>';
                }
            });
        });
    }

});
