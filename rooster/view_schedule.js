$(document).ready(function() {
    $('#class-select').change(function() {
        var class_id = $(this).val();
        console.log('Selected class ID:', class_id);
        if (class_id) {
            clearSchedule(); // Clear the current schedule before loading the new one
            loadSchedule(class_id);
            $('#schedule-container').show();
        } else {
            $('#schedule-container').hide();
        }
    });

    function clearSchedule() {
        // Clear the current schedule by emptying all the cells
        $('#schedule-table tbody td.schedule-slot').each(function() {
            $(this).find('.schedule-content').html(''); // Clear content
            $(this).removeData('subject-id'); // Clear any stored data
            $(this).removeData('teacher-id'); // Clear any stored data
        });
    }

    function loadSchedule(class_id) {
        console.log('Fetching schedule for class ID:', class_id);

        fetch('fetch_schedule.php?class_id=' + encodeURIComponent(class_id))
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                } else {
                    console.log('Schedule data:', data);
                    data.forEach(entry => {
                        var cell = $('td.schedule-slot[data-day="' + entry.day + '"][data-time="' + entry.time_slot + '"]');
                        cell.find('.schedule-content').html('<strong>' + entry.subject_name + '</strong><br>' + entry.teacher_name);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading schedule:', error);
            });
    }
});
