/* Dashboard shell behaviour: sidebar, tabs, modals with remote data, confirmations. */
(function () {
    'use strict';

    var BASE = window.APP_URL || '';

    var menuButton = document.querySelector('.menu-btn');
    var sidebar = document.querySelector('.dash-sidebar');
    if (menuButton && sidebar) {
        menuButton.addEventListener('click', function () { sidebar.classList.toggle('open'); });
        document.addEventListener('click', function (event) {
            if (window.innerWidth <= 900 && sidebar.classList.contains('open') &&
                !sidebar.contains(event.target) && !menuButton.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    /* --------------------------------------------------------- tabs --- */
    document.querySelectorAll('.tabs').forEach(function (group) {
        group.querySelectorAll('.tab-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                var target = button.getAttribute('data-tab');
                group.querySelectorAll('.tab-btn').forEach(function (other) { other.classList.remove('active'); });
                button.classList.add('active');
                document.querySelectorAll('.tab-pane').forEach(function (pane) {
                    if (pane.getAttribute('data-pane')) {
                        pane.classList.toggle('active', pane.getAttribute('data-pane') === target);
                    }
                });
            });
        });
    });

    /* --------------------------------- modals prefilled from a row --- */
    document.querySelectorAll('[data-edit-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            var modalId = button.getAttribute('data-edit-modal');
            var modal = document.getElementById(modalId);
            if (!modal) { return; }
            Object.keys(button.dataset).forEach(function (key) {
                if (key.indexOf('field') === 0) {
                    var name = key.slice(5).toLowerCase();
                    var input = modal.querySelector('[name="' + name + '"]');
                    if (input) { input.value = button.dataset[key]; }
                }
            });
            var title = modal.querySelector('[data-modal-title]');
            if (title && button.dataset.title) { title.textContent = button.dataset.title; }
            window.openModal(modalId);
        });
    });

    /* ------------------------------------------------ confirmations --- */
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.getAttribute('data-confirm'))) { event.preventDefault(); }
        });
    });

    /* ------------------------------------------- table quick search --- */
    document.querySelectorAll('[data-table-search]').forEach(function (input) {
        input.addEventListener('input', function () {
            var table = document.querySelector(input.getAttribute('data-table-search'));
            if (!table) { return; }
            var needle = input.value.toLowerCase();
            table.querySelectorAll('tbody tr').forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().indexOf(needle) > -1 ? '' : 'none';
            });
        });
    });

    /* -------------------------------------------- generate password --- */
    document.querySelectorAll('[data-generate-password]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.querySelector(button.getAttribute('data-generate-password'));
            if (!input) { return; }
            var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789@#';
            var value = '';
            for (var i = 0; i < 10; i++) { value += chars.charAt(Math.floor(Math.random() * chars.length)); }
            input.type = 'text';
            input.value = value;
        });
    });

    /* ------------------------------------ live payment queue refresh --- */
    var queue = document.getElementById('paymentQueueCount');
    if (queue) {
        setInterval(function () {
            fetch(BASE + '/backend/api/payments/status.php')
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.ok) { queue.textContent = data.pending_count; }
                })
                .catch(function () { /* keep the last value */ });
        }, 20000);
    }
})();
