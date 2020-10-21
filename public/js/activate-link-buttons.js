(function () {
    document.querySelectorAll('[data-link]').forEach(function (button) {
        button.addEventListener('click', function () {
            var link = document.createElement('a');
            link.href = button.attributes['data-link'].value;
            link.click();
        });
    });
})();
