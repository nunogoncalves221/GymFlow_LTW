document.addEventListener('DOMContentLoaded', function () {

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
    const fileInput = document.getElementById('profile_picture');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const btn = document.getElementById('avatar-upload-btn');
            if (fileInput.files[0] && btn) btn.style.display = 'inline-block';
        });
    }

});
