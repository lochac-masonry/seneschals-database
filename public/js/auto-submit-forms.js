(function () {
    document.querySelectorAll('form.form--auto-submit').forEach(function (form) {
        form.addEventListener('change', function () {
            form.submit();
        });
        var submitButton = form.querySelector('input[type="submit"]');
        if (submitButton) {
            submitButton.hidden = true;
        }
    });
})();
