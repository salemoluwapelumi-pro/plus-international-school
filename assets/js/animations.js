/* Extra interactive animations for the public site. */
(function () {
    'use strict';

    /* Sticky header shadow on scroll. */
    var header = document.querySelector('.site-header');
    if (header) {
        window.addEventListener('scroll', function () {
            header.style.boxShadow = window.scrollY > 20
                ? '0 6px 22px rgba(0,0,0,.28)'
                : '0 2px 14px rgba(0,0,0,.18)';
        });
    }

    /* Testimonial rotator. */
    var quotes = document.querySelectorAll('[data-quote]');
    if (quotes.length > 1) {
        var index = 0;
        quotes.forEach(function (quote, position) { quote.style.display = position === 0 ? 'block' : 'none'; });
        setInterval(function () {
            quotes[index].style.display = 'none';
            index = (index + 1) % quotes.length;
            quotes[index].style.display = 'block';
            quotes[index].classList.add('animate-pop');
            setTimeout(function () { quotes[index].classList.remove('animate-pop'); }, 400);
        }, 7000);
    }

    /* Back-to-top button. */
    var top = document.createElement('button');
    top.className = 'btn btn-primary no-print';
    top.textContent = '↑';
    top.setAttribute('aria-label', 'Back to top');
    top.style.cssText = 'position:fixed;right:22px;bottom:22px;width:46px;height:46px;padding:0;border-radius:50%;display:none;z-index:150';
    document.body.appendChild(top);
    top.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
    window.addEventListener('scroll', function () {
        top.style.display = window.scrollY > 500 ? 'flex' : 'none';
    });
})();
