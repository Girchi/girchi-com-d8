$('#edit-field-politician-value').on('change', (e) => {
    if (e.target.checked) {
        $('.form-checkbox-input').addClass('checked');
    } else {
        $('.form-checkbox-input').removeClass('checked');
    }
})
