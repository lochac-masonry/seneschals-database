(function () {
    var groupSelect = document.querySelector('select[name="eventGroup[groupid]"]');
    var notifyInsurerInput = document.querySelector('input[name="eventGroup[notifyInsurer]"]');
    if (!groups || !groupSelect || !notifyInsurerInput) {
        return;
    }

    function setVisibility() {
        var country = (groups.find((group) => group.id == Number(groupSelect.value || '0')) || {}).country;
        notifyInsurerInput.parentElement.hidden = country !== 'NZ';
    }

    groupSelect.addEventListener('change', setVisibility);
    setVisibility();
})();
