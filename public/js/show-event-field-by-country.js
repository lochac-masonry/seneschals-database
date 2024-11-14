(function () {
    const groupSelect = document.querySelector('select[name="eventGroup[groupid]"]');
    const countrySpecificNodes = document.querySelectorAll('[data-country]');
    if (!groups || !groupSelect || !countrySpecificNodes.length) {
        return;
    }

    function setVisibility() {
        const country = groups.find((group) => group.id == Number(groupSelect.value || '0'))?.country;

        for (const node of countrySpecificNodes) {
            if (node instanceof HTMLElement) {
                node.style.display = node.getAttribute('data-country') === country ? 'inherit' : 'none';
            }
        }
    }

    groupSelect.addEventListener('change', setVisibility);
    setVisibility();
})();
