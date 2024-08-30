$(document).ready(function () {
    let currentCell; // To keep track of the currently selected schedule slot

    // Handle class selection change
    $('#class-select').change(function () {
        var class_id = $(this).val();
        if (class_id) {
            loadSchedule(class_id);
            $('#schedule-container').show();
        } else {
            $('#schedule-container').hide();
        }
    });

    function loadSchedule(class_id) {
        $('#schedule-table tbody td.schedule-slot').each(function () {
            $(this).find('.schedule-content').html('');
            $(this).removeData('subject-id');
            $(this).removeData('teacher-id');
        });

        fetch('fetch_schedule.php?class_id=' + encodeURIComponent(class_id))
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                } else {
                    data.forEach(entry => {
                        var cell = $('td.schedule-slot[data-day="' + entry.day + '"][data-time="' + entry.time_slot + '"]');
                        cell.find('.schedule-content').html('<strong>' + entry.subject_name + '</strong><br>' + entry.teacher_name);
                        cell.data('subject-id', entry.subject_id);
                        cell.data('teacher-id', entry.teacher_id);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading schedule:', error);
            });
    }

    // Open the modal when a schedule slot is clicked
    $('.schedule-slot').click(function () {
        currentCell = $(this); // Store the current cell
        var day = currentCell.data('day');
        var timeSlot = currentCell.data('time');
        var class_id = $('#class-select').val();

        // Set the hidden fields with the day and time
        $('#modal-day').val(day);
        $('#modal-time').val(timeSlot);
        $('#modal-class-id').val(class_id);

        // Load existing data into modal if available
        var subject_id = currentCell.data('subject-id');
        var teacher_id = currentCell.data('teacher-id');
        $('#subject-select').val(subject_id);
        $('#teacher-select').val(teacher_id);

        // Check teacher availability and disable unavailable options
        checkTeacherAvailability(day, timeSlot);

        // Show the modal
        $('#modal').show();
    });

    // Event listener for the close button
    $('.close').click(function () {
        $('#modal').hide(); // Close the modal
    });

    // Optional: Close the modal if the user clicks outside the modal content
    $(window).click(function (event) {
        if (event.target.id === 'modal') {
            $('#modal').hide(); // Close the modal
        }
    });

    // Handle form submission and auto-save the schedule
    $('#modal-form').submit(function (event) {
        event.preventDefault(); // Prevent the form from submitting normally

        var subject_id = $('#subject-select').val();
        var teacher_id = $('#teacher-select').val();
        var day = $('#modal-day').val();
        var timeSlot = $('#modal-time').val();

        var subject_name = $('#subject-select option:selected').text();
        var teacher_name = $('#teacher-select option:selected').text();

        // Update the current cell with new data
        currentCell.find('.schedule-content').html('<strong>' + subject_name + '</strong><br>' + teacher_name);
        currentCell.data('subject-id', subject_id);
        currentCell.data('teacher-id', teacher_id);

        // Auto-save the updated schedule
        saveSchedule(day, timeSlot, subject_id, teacher_id);

        // Close the modal
        $('#modal').hide();
    });

    // Handle the Remove button click
    $('#delete-schedule-btn').click(function () {
        var day = $('#modal-day').val();
        var timeSlot = $('#modal-time').val();
        var class_id = $('#modal-class-id').val();

        // Clear the content of the cell
        currentCell.find('.schedule-content').html('');
        currentCell.removeData('subject-id');
        currentCell.removeData('teacher-id');

        // Remove the schedule entry from the database
        deleteSchedule(class_id, day, timeSlot);

        // Close the modal
        $('#modal').hide();
    });

    function saveSchedule(day, timeSlot, subject_id, teacher_id) {
        var class_id = $('#class-select').val();
        var scheduleData = {
            day: day,
            time_slot: timeSlot,
            subject_id: subject_id,
            teacher_id: teacher_id
        };

        fetch('save_schedule.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                class_id: class_id,
                schedule: [scheduleData]
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Schedule saved successfully!');
                } else {
                    console.log('Failed to save schedule.');
                }
            })
            .catch(error => {
                console.error('Error saving schedule:', error);
            });
    }

    function deleteSchedule(class_id, day, timeSlot) {
        fetch('delete_schedule.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                class_id: class_id,
                day: day,
                time_slot: timeSlot
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Schedule deleted successfully!');
                } else {
                    console.log('Failed to delete schedule.');
                }
            })
            .catch(error => {
                console.error('Error deleting schedule:', error);
            });
    }

    // Function to check teacher availability
    function checkTeacherAvailability(day, timeSlot) {
        fetch('check_teacher_availability.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                day: day,
                time_slot: timeSlot
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reset all options to be selectable
                    $('#teacher-select option').removeAttr('disabled').removeClass('disabled-option');

                    // Loop through each teacher option in the dropdown
                    $('#teacher-select option').each(function () {
                        const teacher_id = $(this).val(); // Get the teacher ID from the option
                        const unavailableIDs = data.unavailable_teachers.map(id => id.toString());

                        // Disable the option if the teacher is unavailable
                        if (unavailableIDs.includes(teacher_id)) {
                            $(this).attr('disabled', true).addClass('disabled-option');
                        }
                    });
                } else {
                    console.log('Failed to check availability:', data.error);
                }
            })
            .catch(error => {
                console.error('Error checking teacher availability:', error);
            });
    }

    // Prevent the selection of disabled options but allow re-selection
    $('#teacher-select').on('mousedown', function (e) {
        $(this).find('option:disabled').each(function () {
            if ($(this).is(':selected')) {
                e.preventDefault();
            }
        });
    });

    // Allow clicking on the dropdown to select a different teacher
    $('#teacher-select').on('click', function () {
        // Enable the dropdown for interaction
        $(this).removeAttr('disabled');
    });
});
