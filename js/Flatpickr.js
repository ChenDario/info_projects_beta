let fromDatePicker, toDatePicker;

fromDatePicker = flatpickr("#from_date", {
    dateFormat: "Y-m-d",
    locale: "it",
    onChange: function(selectedDates) {
        if (selectedDates.length > 0) {
            toDatePicker.set('minDate', selectedDates[0]);
        } else {
            toDatePicker.set('minDate', null);
        }
    }
});

toDatePicker = flatpickr("#to_date", {
    dateFormat: "Y-m-d",
    locale: "it",
    onChange: function(selectedDates) {
        if (selectedDates.length > 0) {
            fromDatePicker.set('maxDate', selectedDates[0]);
        } else {
            fromDatePicker.set('maxDate', null);
        }
    }
});
