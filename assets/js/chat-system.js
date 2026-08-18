/* Teacher ↔ student chat: contact list, polling for new messages, sending. */
(function () {
    'use strict';

    var app = document.getElementById('chatApp');
    if (!app) { return; }

    var BASE = window.APP_URL || '';
    var scroll = document.getElementById('chatScroll');
    var composer = document.getElementById('chatComposer');
    var peerId = app.getAttribute('data-peer') ? parseInt(app.getAttribute('data-peer'), 10) : 0;
    var meId = parseInt(app.getAttribute('data-me'), 10);
    var lastId = 0;

    function bubble(message) {
        var element = document.createElement('div');
        element.className = 'bubble ' + (parseInt(message.sender_id, 10) === meId ? 'me' : 'them');
        var text = document.createElement('div');
        text.textContent = message.body;
        var time = document.createElement('time');
        time.textContent = new Date(message.created_at.replace(' ', 'T')).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        element.appendChild(text);
        element.appendChild(time);
        return element;
    }

    function append(messages) {
        if (!messages.length) { return; }
        messages.forEach(function (message) {
            scroll.appendChild(bubble(message));
            lastId = Math.max(lastId, parseInt(message.id, 10));
        });
        scroll.scrollTop = scroll.scrollHeight;
    }

    function poll() {
        if (!peerId) { return; }
        fetch(BASE + '/backend/api/chat/receive.php?peer=' + peerId + '&after=' + lastId)
            .then(function (response) { return response.json(); })
            .then(function (data) { if (data.ok) { append(data.messages); } })
            .catch(function () { /* retry on the next tick */ });
    }

    if (scroll) {
        scroll.querySelectorAll('.bubble[data-id]').forEach(function (element) {
            lastId = Math.max(lastId, parseInt(element.getAttribute('data-id'), 10));
        });
        scroll.scrollTop = scroll.scrollHeight;
    }

    if (composer) {
        composer.addEventListener('submit', function (event) {
            event.preventDefault();
            var input = composer.querySelector('input[name="body"]');
            var body = input.value.trim();
            if (!body) { return; }
            input.value = '';
            fetch(BASE + '/backend/api/chat/send.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ receiver_id: peerId, body: body })
            }).then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.ok) { append([data.message]); } else { window.toast(data.error || 'Message not sent.', 'error'); }
                })
                .catch(function () { window.toast('Message not sent — check your connection.', 'error'); });
        });
    }

    setInterval(poll, 3000);

    var search = document.getElementById('contactSearch');
    if (search) {
        search.addEventListener('input', function () {
            var needle = search.value.toLowerCase();
            document.querySelectorAll('.contact').forEach(function (contact) {
                contact.style.display = contact.textContent.toLowerCase().indexOf(needle) > -1 ? '' : 'none';
            });
        });
    }

    var backButton = document.getElementById('chatBack');
    if (backButton) {
        backButton.addEventListener('click', function () { app.classList.add('show-list'); });
    }
})();
