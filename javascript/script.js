function applySelectFilter(param, value) {
    var url = new URL(window.location.href);
    if (value) url.searchParams.set(param, value); else url.searchParams.delete(param);
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function () {

    // Staggered card entrance animation
    document.querySelectorAll('.class-card').forEach(function (card, i) {
        card.style.animationDelay = (i * 0.045) + 's';
    });

    // Live search — filters class cards client-side without a page reload
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

});
