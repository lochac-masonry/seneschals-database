(function () {
    document.querySelectorAll('[data-copyable]').forEach(function (node) {
        var buttonText = node.getAttribute('data-copyable') || 'Copy to clipboard';
        var button = document.createElement('button');
        button.textContent = buttonText;
        button.onclick = function () {
            if (document.body.createTextRange) {
                var range = document.body.createTextRange();
                range.moveToElementText(node);
                range.select();
            } else if (window.getSelection) {
                var selection = window.getSelection();
                var range = document.createRange();
                range.selectNodeContents(node);
                selection.removeAllRanges();
                selection.addRange(range);
            }
            document.execCommand('copy');
        };

        var buttonContainer = document.createElement('div');
        buttonContainer.appendChild(button);

        node.parentElement.insertBefore(buttonContainer, node);
    });
})();
