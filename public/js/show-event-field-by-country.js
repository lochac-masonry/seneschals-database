(function () {
    var groupSelect = document.querySelector('select[name="eventGroup[groupid]"]');
    var notifyInsurerInput = document.querySelector('input[name="eventGroup[notifyInsurer]"]');
    if (!groups || !groupSelect || !notifyInsurerInput) {
        return;
    }

    function setVisibility() {
        var country = (groups.find((group) => group.id == Number(groupSelect.value || '0')) || {}).country;
        // Set the visibility on the parent - the label contains the checkbox.
        notifyInsurerInput.parentElement.style.display = country === 'NZ' ? 'inherit' : 'none';
    }

    groupSelect.addEventListener('change', setVisibility);
    setVisibility();
})();
