/* Fees payment: Paystack checkout, Remita option and bank-transfer submission. */
(function () {
    'use strict';

    var BASE = window.APP_URL || '';
    var form = document.getElementById('feePaymentForm');
    if (!form) { return; }

    var amountField = form.querySelector('[name="amount"]');
    var expectedNote = document.getElementById('expectedFeeNote');

    /* Fee amount follows the selected class + term. */
    function refreshExpectedFee() {
        var classId = form.class_id.value;
        var term = form.term.value;
        if (!classId || !term) { return; }
        fetch(BASE + '/backend/api/payments/fee.php?class_id=' + classId + '&term=' + encodeURIComponent(term))
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.ok && expectedNote) {
                    expectedNote.textContent = data.amount > 0
                        ? 'Fee for this class and term: ₦' + Number(data.amount).toLocaleString()
                        : 'No fee has been published for this class and term yet.';
                    if (data.amount > 0 && !amountField.value) { amountField.value = data.amount; }
                }
            });
    }
    form.class_id.addEventListener('change', refreshExpectedFee);
    form.term.addEventListener('change', refreshExpectedFee);
    refreshExpectedFee();

    function record(payload, onDone) {
        fetch(BASE + '/backend/api/payments/submit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.ok) {
                    window.toast('Payment recorded. The cashier will approve it shortly.', 'success', 'Payment received');
                    setTimeout(function () { window.location.href = BASE + '/student/payments/history.php'; }, 1200);
                } else {
                    window.toast(data.error || 'Could not record the payment.', 'error');
                }
                if (onDone) { onDone(); }
            }).catch(function () {
                window.toast('Network error while recording the payment.', 'error');
                if (onDone) { onDone(); }
            });
    }

    function formData() {
        var payload = {};
        new FormData(form).forEach(function (value, key) { payload[key] = value; });
        return payload;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var payload = formData();
        var button = form.querySelector('[type="submit"]');
        var channel = payload.channel;

        if (Number(payload.amount) <= 0) {
            window.toast('Enter the amount you want to pay.', 'error');
            return;
        }

        button.disabled = true;

        if (channel === 'paystack' && window.PaystackPop && window.PAYSTACK_PUBLIC_KEY) {
            var handler = window.PaystackPop.setup({
                key: window.PAYSTACK_PUBLIC_KEY,
                email: payload.email,
                amount: Math.round(Number(payload.amount) * 100),
                currency: 'NGN',
                metadata: {
                    custom_fields: [
                        { display_name: 'Student', variable_name: 'student', value: payload.student_name },
                        { display_name: 'Term', variable_name: 'term', value: payload.term }
                    ]
                },
                callback: function (response) {
                    payload.gateway_ref = response.reference;
                    record(payload, function () { button.disabled = false; });
                },
                onClose: function () {
                    button.disabled = false;
                    window.toast('Payment window closed before completion.', 'error');
                }
            });
            handler.openIframe();
            return;
        }

        if (channel === 'remita') {
            fetch(BASE + '/backend/api/payments/remita.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).then(function (response) { return response.json(); })
                .then(function (data) {
                    button.disabled = false;
                    if (data.ok) {
                        window.toast('Remita RRR generated: ' + data.rrr, 'success', 'Remita');
                        var box = document.getElementById('remitaResult');
                        if (box) {
                            box.style.display = 'block';
                            box.innerHTML = 'Your Remita Retrieval Reference (RRR) is <strong>' + data.rrr +
                                '</strong>. Pay at any bank or on remita.net, then upload your proof of payment.';
                        }
                    } else {
                        window.toast(data.error || 'Remita is not available right now.', 'error');
                    }
                });
            return;
        }

        /* Bank transfer / cash — submitted for cashier verification. */
        var body = new FormData(form);
        fetch(BASE + '/backend/api/payments/submit.php', { method: 'POST', body: body })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                button.disabled = false;
                if (data.ok) {
                    window.toast('Payment submitted for cashier approval.', 'success');
                    setTimeout(function () { window.location.href = BASE + '/student/payments/history.php'; }, 1200);
                } else {
                    window.toast(data.error || 'Submission failed.', 'error');
                }
            }).catch(function () {
                button.disabled = false;
                window.toast('Network error.', 'error');
            });
    });

    /* Show the fields that belong to the chosen channel. */
    form.querySelectorAll('[name="channel"]').forEach(function (input) {
        input.addEventListener('change', function () {
            document.querySelectorAll('[data-channel]').forEach(function (block) {
                block.style.display = block.getAttribute('data-channel') === input.value ? 'block' : 'none';
            });
        });
    });
})();
