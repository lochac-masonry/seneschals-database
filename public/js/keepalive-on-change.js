(function () {
    document.addEventListener('change', function () {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/tools/keep-alive');

        function checkStatus() {
            if (xhr.status !== 200) {
                window.alert(
                    'Your login session may have expired. If you have work in progress, '
                    + 'please copy it to a safe place and refresh the page.'
                )
            }
        }
        xhr.addEventListener('load', checkStatus);
        xhr.addEventListener('error', checkStatus);
        xhr.send();
    });
})();
