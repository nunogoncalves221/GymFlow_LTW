function applySelectFilter(param, value) {
    var url = new URL(window.location.href);
    if (value) url.searchParams.set(param, value); else url.searchParams.delete(param);
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function () {

    // ── Flash message auto-dismiss ─────────────────────────────────────────
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

    // ── Report form: character counter + submit confirmation ───────────────
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

    // ── Password confirmation validator ────────────────────────────────────
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

    // ── Staggered card entrance animation ─────────────────────────────────
    document.querySelectorAll('.class-card').forEach(function (card, i) {
        card.style.animationDelay = (i * 0.045) + 's';
    });

    // ── Live search — filters class cards client-side ──────────────────────
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

    function escapeHtml(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    // ── Trainer dashboard calendar modal ──────────────────────────────────
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

    // ── Profile picture preview ────────────────────────────────────────────
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

    // ── Class cards index page modal ──────────────────────────────
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

    // ── View toggle (Classes / Schedule) ─────────────────────────
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

});