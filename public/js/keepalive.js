(function () {
    /**
     * Make a background request periodically to update the user's session and
     * keep it from being garbage-collected.
     */
    const interval = setInterval(function () {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/tools/keep-alive');

        function checkStatus() {
            if (xhr.status !== 200) {
                clearInterval(interval);
                window.alert(
                    'Your login session may have expired. If you have work in progress, '
                    + 'please copy it to a safe place and refresh the page.'
                )
            }
        }
        xhr.addEventListener('load', checkStatus);
        xhr.addEventListener('error', checkStatus);
        xhr.send();
    }, 5 * 60 * 1000);
})();
