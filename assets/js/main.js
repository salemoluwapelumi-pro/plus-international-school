/* Core website interactions: navigation, hero slider, counters, modals, toasts. */
(function () {
    'use strict';

    /* ------------------------------------------------------------ nav --- */
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.querySelector('.nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', nav.classList.contains('open'));
        });
    }

    document.querySelectorAll('.nav .dropdown > a').forEach(function (link) {
        link.addEventListener('click', function (event) {
            if (window.innerWidth <= 900 || link.getAttribute('href') === '#') {
                event.preventDefault();
                link.parentElement.classList.toggle('open');
            }
        });
    });

    document.addEventListener('click', function (event) {
        document.querySelectorAll('.nav .dropdown.open').forEach(function (item) {
            if (!item.contains(event.target)) { item.classList.remove('open'); }
        });
        document.querySelectorAll('.user-menu.open').forEach(function (item) {
            if (!item.contains(event.target)) { item.classList.remove('open'); }
        });
    });

    document.querySelectorAll('[data-user-menu]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.stopPropagation();
            button.closest('.user-menu').classList.toggle('open');
        });
    });

    /* --------------------------------------------------- hero slider --- */
    var slides = document.querySelectorAll('.hero-slide');
    if (slides.length > 1) {
        var index = 0;
        setInterval(function () {
            slides[index].classList.remove('active');
            index = (index + 1) % slides.length;
            slides[index].classList.add('active');
        }, 6000);
    }

    /* ------------------------------------------------------- reveals --- */
    var revealables = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && revealables.length) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        revealables.forEach(function (element) { observer.observe(element); });
    } else {
        revealables.forEach(function (element) { element.classList.add('visible'); });
    }

    /* ------------------------------------------------------ counters --- */
    document.querySelectorAll('[data-count]').forEach(function (element) {
        var target = parseInt(element.getAttribute('data-count'), 10) || 0;
        var started = false;
        function run() {
            if (started) { return; }
            started = true;
            var current = 0;
            var step = Math.max(1, Math.round(target / 60));
            var timer = setInterval(function () {
                current = Math.min(target, current + step);
                element.textContent = current.toLocaleString() + (element.dataset.suffix || '');
                if (current >= target) { clearInterval(timer); }
            }, 24);
        }
        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function (entries, obs) {
                if (entries[0].isIntersecting) { run(); obs.disconnect(); }
            }).observe(element);
        } else { run(); }
    });

    /* -------------------------------------------------------- modals --- */
    window.openModal = function (id) {
        var modal = document.getElementById(id);
        if (modal) { modal.classList.add('open'); document.body.style.overflow = 'hidden'; }
    };
    window.closeModal = function (id) {
        var modal = document.getElementById(id);
        if (modal) { modal.classList.remove('open'); document.body.style.overflow = ''; }
    };
    document.querySelectorAll('[data-modal-open]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            window.openModal(button.getAttribute('data-modal-open'));
        });
    });
    document.querySelectorAll('[data-modal-close]').forEach(function (button) {
        button.addEventListener('click', function () {
            var backdrop = button.closest('.modal-backdrop');
            if (backdrop) { backdrop.classList.remove('open'); document.body.style.overflow = ''; }
        });
    });
    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
        backdrop.addEventListener('click', function (event) {
            if (event.target === backdrop) { backdrop.classList.remove('open'); document.body.style.overflow = ''; }
        });
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.open').forEach(function (backdrop) {
                backdrop.classList.remove('open');
            });
            document.body.style.overflow = '';
        }
    });

    /* -------------------------------------------------------- toasts --- */
    window.toast = function (message, type, title) {
        var stack = document.querySelector('.toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.className = 'toast-stack';
            document.body.appendChild(stack);
        }
        var element = document.createElement('div');
        element.className = 'toast ' + (type || 'info');
        element.innerHTML = '<strong></strong><span></span>';
        element.querySelector('strong').textContent = title || (type === 'error' ? 'Something went wrong' : 'Done');
        element.querySelector('span').textContent = message;
        stack.appendChild(element);
        setTimeout(function () { element.remove(); }, 4800);
    };

    /* ------------------------------------------------------ gallery ---- */
    document.querySelectorAll('.gallery-item').forEach(function (item) {
        item.addEventListener('click', function () {
            var caption = item.getAttribute('data-caption') || 'School gallery';
            var body = document.getElementById('galleryModalBody');
            if (!body) { return; }
            var image = item.querySelector('img');
            body.innerHTML = image
                ? '<img src="' + image.src + '" alt="' + caption + '" style="border-radius:12px">'
                : '<div class="placeholder" style="height:320px">' + caption + '</div>';
            var titleEl = document.getElementById('galleryModalTitle');
            if (titleEl) { titleEl.textContent = caption; }
            window.openModal('galleryModal');
        });
    });

    /* ---------------------------------------------- smooth anchor nav --- */
    document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(function (link) {
        link.addEventListener('click', function (event) {
            var target = document.querySelector(link.getAttribute('href'));
            if (target) {
                event.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                if (nav) { nav.classList.remove('open'); }
            }
        });
    });

    /* ------------------------------------------------ ajax form post --- */
    document.querySelectorAll('form[data-ajax]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var button = form.querySelector('[type="submit"]');
            var original = button ? button.innerHTML : '';
            if (button) { button.disabled = true; button.innerHTML = '<span class="spinner"></span> Please wait…'; }

            fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form) })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.ok) {
                        window.toast(data.message || 'Submitted successfully.', 'success');
                        form.reset();
                        if (data.redirect) { setTimeout(function () { window.location.href = data.redirect; }, 900); }
                    } else {
                        window.toast(data.error || 'Please check the form and try again.', 'error');
                    }
                })
                .catch(function () { window.toast('Network error. Please try again.', 'error'); })
                .finally(function () { if (button) { button.disabled = false; button.innerHTML = original; } });
        });
    });
})();
