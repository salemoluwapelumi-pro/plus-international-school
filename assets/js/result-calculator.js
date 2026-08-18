/* Live result computation in the browser — mirrors backend/includes/ResultCalculator.php.
   CA1 (10) + CA2 (10) + Assignment (10) + Exam (70) = 100. */
(function () {
    'use strict';

    var BANDS = [
        { min: 75, grade: 'A1', remark: 'Excellent' },
        { min: 70, grade: 'B2', remark: 'Very Good' },
        { min: 65, grade: 'B3', remark: 'Good' },
        { min: 60, grade: 'C4', remark: 'Credit' },
        { min: 55, grade: 'C5', remark: 'Credit' },
        { min: 50, grade: 'C6', remark: 'Credit' },
        { min: 45, grade: 'D7', remark: 'Pass' },
        { min: 40, grade: 'E8', remark: 'Weak Pass' },
        { min: 0, grade: 'F9', remark: 'Fail' }
    ];

    function clamp(value, max) {
        var number = parseFloat(value);
        if (isNaN(number) || number < 0) { return 0; }
        return Math.min(number, max);
    }

    function computeTotal(ca1, ca2, assignment, exam) {
        return Math.round((clamp(ca1, 10) + clamp(ca2, 10) + clamp(assignment, 10) + clamp(exam, 70)) * 100) / 100;
    }

    function gradeFor(total) {
        for (var i = 0; i < BANDS.length; i++) {
            if (total >= BANDS[i].min) { return BANDS[i]; }
        }
        return BANDS[BANDS.length - 1];
    }

    function recalcRow(row) {
        var total = computeTotal(
            row.querySelector('[name="ca1[]"]').value,
            row.querySelector('[name="ca2[]"]').value,
            row.querySelector('[name="assignment[]"]').value,
            row.querySelector('[name="exam[]"]').value
        );
        var band = gradeFor(total);
        row.querySelector('.cell-total').textContent = total.toFixed(2);
        row.querySelector('.cell-grade').textContent = band.grade;
        row.querySelector('.cell-remark').textContent = band.remark;
        row.querySelector('.cell-grade').className = 'cell-grade badge ' +
            (total >= 60 ? 'badge-green' : total >= 40 ? 'badge-amber' : 'badge-red');
        return total;
    }

    function recalcAll() {
        var rows = document.querySelectorAll('#resultEntryTable tbody tr');
        var sum = 0;
        rows.forEach(function (row) { sum += recalcRow(row); });
        var average = rows.length ? sum / rows.length : 0;
        var summary = document.getElementById('resultSummary');
        if (summary) {
            summary.querySelector('[data-total]').textContent = sum.toFixed(2);
            summary.querySelector('[data-average]').textContent = average.toFixed(2);
            summary.querySelector('[data-grade]').textContent = gradeFor(average).grade;
            summary.querySelector('[data-subjects]').textContent = rows.length;
        }
    }

    document.querySelectorAll('#resultEntryTable input').forEach(function (input) {
        input.addEventListener('input', recalcAll);
    });
    if (document.getElementById('resultEntryTable')) { recalcAll(); }

    window.ResultCalculator = { computeTotal: computeTotal, gradeFor: gradeFor, recalcAll: recalcAll };

    /* ---------------------------------------------------- print sheet --- */
    var printButton = document.getElementById('printResult');
    if (printButton) {
        printButton.addEventListener('click', function () { window.print(); });
    }

    /* ------------------------------------------- student result lookup --- */
    var checkForm = document.getElementById('resultCheckForm');
    if (checkForm) {
        checkForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var params = new URLSearchParams(new FormData(checkForm)).toString();
            window.location.href = checkForm.getAttribute('action') + '?' + params;
        });
    }
})();
