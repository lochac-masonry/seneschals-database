(function () {
    var emailInput = document.querySelector('input[name="senDetails[email]"]');
    var countrySelect = document.querySelector('select[name="senDetails[country]"]');
    var auTemplate = document.querySelector('template#emailHintAU');
    var nzTemplate = document.querySelector('template#emailHintNZ');
    if (!emailInput || !countrySelect || !auTemplate || !nzTemplate) {
        return;
    }

    var hintElement = null;

    function updateHint() {
        if (hintElement) {
            hintElement.parentNode.removeChild(hintElement);
        }
        var template = countrySelect.value === 'AU' ? auTemplate : nzTemplate;
        hintElement = template.content.firstElementChild.cloneNode(true);
        emailInput.parentNode.appendChild(hintElement);
    }
    countrySelect.addEventListener('change', updateHint);
    updateHint();
})();
