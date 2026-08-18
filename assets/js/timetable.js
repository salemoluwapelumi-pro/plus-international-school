/* Timetable management: slot editing modal and day/class switching. */
(function () {
    'use strict';

    document.querySelectorAll('[data-slot]').forEach(function (cell) {
        cell.addEventListener('click', function () {
            var modal = document.getElementById('slotModal');
            if (!modal) { return; }
            var form = modal.querySelector('form');
            form.day_of_week.value = cell.getAttribute('data-day');
            form.period.value = cell.getAttribute('data-period');
            form.subject_id.value = cell.getAttribute('data-subject') || '';
            form.teacher_id.value = cell.getAttribute('data-teacher') || '';
            form.start_time.value = cell.getAttribute('data-start') || '';
            form.end_time.value = cell.getAttribute('data-end') || '';
            var slotId = cell.getAttribute('data-slot-id') || '';
            form.querySelector('[name="slot_id"]').value = slotId;
            var deleteButton = modal.querySelector('[data-delete-slot]');
            if (deleteButton) { deleteButton.style.display = slotId ? 'inline-flex' : 'none'; }
            var title = modal.querySelector('[data-modal-title]');
            if (title) {
                title.textContent = cell.getAttribute('data-day') + ' · Period ' + cell.getAttribute('data-period');
            }
            window.openModal('slotModal');
        });
    });

    var daySelect = document.getElementById('timetableDay');
    if (daySelect) {
        daySelect.addEventListener('change', function () { daySelect.form.submit(); });
    }
    var classSelect = document.getElementById('timetableClass');
    if (classSelect) {
        classSelect.addEventListener('change', function () { classSelect.form.submit(); });
    }
})();
