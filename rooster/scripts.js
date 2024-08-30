$(document).ready(function() {
    let currentCell; // To keep track of the currently selected schedule slot

    // Handle class selection change
    $('#class-select').change(function() {
        var class_id = $(this).val();
        if (class_id) {
            loadSchedule(class_id);
            $('#schedule-container').show();
        } else {
            $('#schedule-container').hide();
        }
    });

    function loadSchedule(class_id) {
        $('#schedule-table tbody td.schedule-slot').each(function() {
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
    $('.schedule-slot').click(function() {
        currentCell = $(this);
        var day = currentCell.data('day');
        var timeSlot = currentCell.data('time');
        var class_id = $('#class-select').val();

        $('#modal-day').val(day);
        $('#modal-time').val(timeSlot);
        $('#modal-class-id').val(class_id);

        var subject_id = currentCell.data('subject-id');
        var teacher_id = currentCell.data('teacher-id');
        $('#subject-select').val(subject_id);
        $('#teacher-select').val(teacher_id);

        checkTeacherAvailability(day, timeSlot);

        $('#modal').show();
    });

    // Event listener for the close button
    $('.close').click(function() {
        $('#modal').hide();
    });

    // Close the modal if the user clicks outside the modal content
    $(window).click(function(event) {
        if (event.target.id === 'modal') {
            $('#modal').hide();
        }
    });

    // Handle form submission and auto-save the schedule
    $('#modal-form').submit(function(event) {
        event.preventDefault();

        var subject_id = $('#subject-select').val();
        var teacher_id = $('#teacher-select').val();
        var day = $('#modal-day').val();
        var timeSlot = $('#modal-time').val();

        var subject_name = $('#subject-select option:selected').text();
        var teacher_name = $('#teacher-select option:selected').text();

        currentCell.find('.schedule-content').html('<strong>' + subject_name + '</strong><br>' + teacher_name);
        currentCell.data('subject-id', subject_id);
        currentCell.data('teacher-id', teacher_id);

        saveSchedule(day, timeSlot, subject_id, teacher_id);

        $('#modal').hide();
    });

    // Handle the Remove button click
    $('#delete-schedule-btn').click(function() {
        var day = $('#modal-day').val();
        var timeSlot = $('#modal-time').val();
        var class_id = $('#modal-class-id').val();

        currentCell.find('.schedule-content').html('');
        currentCell.removeData('subject-id');
        currentCell.removeData('teacher-id');

        deleteSchedule(class_id, day, timeSlot);

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

    // Update teacher options based on the selected subject
    $('#subject-select').change(function() {
        var subject_id = $(this).val();
        updateTeacherOptions(subject_id);
    });

    function updateTeacherOptions(subject_id) {
        if (!subject_id) {
            $('#teacher-select').html('<option value="">--Select Teacher--</option>');
            return;
        }

        // Fetch the teachers linked to the selected subject
        fetch('fetch_teachers.php?subject_id=' + subject_id)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.teachers) {
                    let teacherOptions = '<option value="">--Select Teacher--</option>';
                    data.teachers.forEach(teacher => {
                        teacherOptions += `<option value="${teacher.id}">${teacher.username}</option>`;
                    });
                    $('#teacher-select').html(teacherOptions);

                    // Call check availability to disable options based on availability
                    const day = $('#modal-day').val();
                    const timeSlot = $('#modal-time').val();
                    checkTeacherAvailability(day, timeSlot);
                } else {
                    $('#teacher-select').html('<option value="">No teachers assigned</option>');
                }
            })
            .catch(error => {
                console.error('Error fetching teachers:', error);
                $('#teacher-select').html('<option value="">--Select Teacher--</option>');
            });
    }

    // Function to check teacher availability and manage disabled state
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
                const unavailableIDs = data.unavailable_teachers.map(id => id.toString());

                // Loop through each teacher option in the dropdown
                $('#teacher-select option').each(function() {
                    const teacher_id = $(this).val(); // Get the teacher ID from the option

                    // Disable the option if the teacher is unavailable
                    if (unavailableIDs.includes(teacher_id)) {
                        $(this).attr('disabled', true).addClass('disabled-option');
                        console.log('Disabling teacher ID:', teacher_id);
                    } else {
                        $(this).removeAttr('disabled').removeClass('disabled-option');
                        console.log('Enabling teacher ID:', teacher_id);
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

    // Prevent the selection of disabled options when the dropdown is clicked
    $('#teacher-select').on('mousedown', function(e) {
        $(this).find('option:disabled').each(function() {
            // Prevent clicking on disabled options
            if ($(this).is(':selected')) {
                e.preventDefault();
            }
        });
    });

    // Other modal handling code...
});
