/* Notification bell: dropdown, unread badge and periodic refresh. */
(function () {
    'use strict';

    var BASE = window.APP_URL || '';
    var bell = document.getElementById('notificationBell');
    if (!bell) { return; }

    var panel = document.getElementById('notificationPanel');

    bell.addEventListener('click', function (event) {
        event.stopPropagation();
        panel.parentElement.classList.toggle('open');
    });

    function refresh() {
        fetch(BASE + '/backend/api/notifications.php')
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.ok) { return; }
                var dot = bell.querySelector('.dot');
                if (dot) {
                    dot.textContent = data.unread;
                    dot.style.display = data.unread > 0 ? 'inline-block' : 'none';
                }
                if (!panel) { return; }
                panel.innerHTML = data.items.length
                    ? data.items.map(function (item) {
                        return '<a href="#"><strong>' + item.title + '</strong><br><small>' + (item.body || '') + '</small></a>';
                    }).join('')
                    : '<a href="#">No notifications yet</a>';
            })
            .catch(function () { /* silent */ });
    }

    refresh();
    setInterval(refresh, 30000);
})();
