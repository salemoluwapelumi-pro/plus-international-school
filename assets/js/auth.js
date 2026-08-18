/* Authentication helpers for the shared portal login.
   Every role uses the same login form; the server returns the role and
   redirectToDashboard(role) sends the user to the right dashboard. */
(function () {
    'use strict';

    var BASE = (window.APP_URL || '');

    var DASHBOARDS = {
        superadmin: '/admin/dashboard.php',
        subadmin: '/admin/dashboard.php',
        cashier: '/cashier/dashboard.php',
        teacher: '/teacher/dashboard.php',
        student: '/student/dashboard.php',
        parent: '/parent/dashboard.php'
    };

    /**
     * Redirects a signed-in user to the dashboard that matches their role.
     * @param {string} role superadmin | subadmin | cashier | teacher | student | parent
     */
    function redirectToDashboard(role) {
        var path = DASHBOARDS[role] || '/portal/index.php';
        window.location.href = BASE + path;
        return path;
    }

    function post(url, payload) {
        return fetch(BASE + url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function (response) { return response.json(); });
    }

    function showError(message) {
        var box = document.getElementById('authMessage');
        if (box) {
            box.className = 'alert alert-error';
            box.textContent = message;
            box.style.display = 'block';
        } else if (window.toast) {
            window.toast(message, 'error');
        }
    }

    function showSuccess(message) {
        var box = document.getElementById('authMessage');
        if (box) {
            box.className = 'alert alert-success';
            box.textContent = message;
            box.style.display = 'block';
        }
    }

    function busy(button, isBusy, label) {
        if (!button) { return; }
        if (isBusy) {
            button.dataset.label = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner"></span> ' + (label || 'Please wait…');
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.label || label || 'Submit';
        }
    }

    /* ------------------------------------------------ shared login --- */
    var loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var button = loginForm.querySelector('[type="submit"]');
            busy(button, true, 'Signing in…');
            post('/backend/api/auth/login.php', {
                identifier: loginForm.identifier.value.trim(),
                password: loginForm.password.value
            }).then(function (data) {
                if (data.ok) {
                    showSuccess('Welcome back, ' + data.user.full_name + '. Redirecting…');
                    redirectToDashboard(data.user.role);
                } else {
                    showError(data.error || 'Invalid login details.');
                    busy(button, false, 'Sign in');
                }
            }).catch(function () {
                showError('Unable to reach the server. Please try again.');
                busy(button, false, 'Sign in');
            });
        });
    }

    /* -------------------------------------- student self-registration --- */
    var registerForm = document.getElementById('studentRegisterForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var button = registerForm.querySelector('[type="submit"]');
            var payload = {};
            new FormData(registerForm).forEach(function (value, key) { payload[key] = value; });
            if (payload.password !== payload.password_confirm) {
                showError('The two passwords do not match.');
                return;
            }
            busy(button, true, 'Creating account…');
            post('/backend/api/auth/register.php', payload).then(function (data) {
                if (data.ok) {
                    showSuccess('Account created. Signing you in…');
                    setTimeout(function () { redirectToDashboard('student'); }, 800);
                } else {
                    showError(data.error || 'Registration failed.');
                    busy(button, false, 'Create account');
                }
            }).catch(function () {
                showError('Unable to reach the server.');
                busy(button, false, 'Create account');
            });
        });
    }

    /* ------------------------------------------------ forgot password --- */
    var forgotForm = document.getElementById('forgotForm');
    if (forgotForm) {
        forgotForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var button = forgotForm.querySelector('[type="submit"]');
            busy(button, true, 'Sending…');
            post('/backend/api/auth/reset-password.php', { email: forgotForm.email.value.trim() })
                .then(function (data) {
                    showSuccess(data.message || 'If the email exists, a reset link has been sent.');
                    if (data.reset_link) {
                        var hint = document.getElementById('resetLinkHint');
                        if (hint) {
                            hint.style.display = 'block';
                            hint.innerHTML = 'Development mode — reset link: <a href="' + data.reset_link + '">' + data.reset_link + '</a>';
                        }
                    }
                    busy(button, false, 'Send reset link');
                })
                .catch(function () {
                    showError('Unable to reach the server.');
                    busy(button, false, 'Send reset link');
                });
        });
    }

    /* --------------------------------------------- password visibility --- */
    document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.getAttribute('data-toggle-password'));
            if (!input) { return; }
            input.type = input.type === 'password' ? 'text' : 'password';
            button.textContent = input.type === 'password' ? 'Show' : 'Hide';
        });
    });

    window.redirectToDashboard = redirectToDashboard;
})();
