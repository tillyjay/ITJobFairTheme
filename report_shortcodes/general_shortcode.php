<?php
function handle_event_summary_data() {
    check_ajax_referer('wp_rest', '_wpnonce');

    //assigns the relevent data from the api request
    $eventData = $_POST['event_data'];
    $ticketData = $_POST['ticket_data'];
    $eventId = intval($_POST['event_id']);
    
    //
    //Sort the tickets into industry and students
    //

    //STUDENT
    // Initialize an empty array to hold student data
    $students = array();

    // Check if the attendees key exists and is an array
    if (isset($ticketData['attendees']) && is_array($ticketData['attendees'])) {
        foreach ($ticketData['attendees'] as $student) {
            // Check if the ticket title is 'Student'
            if (isset($student['ticket']['title']) && $student['ticket']['title'] === 'Student') {
                $students[] = $student;
            }
        }
    }

    //INDUSTRY
    // Initialize an empty array to hold industry data
    $industries = array();
    $industries_going = array();

    //Distinguish between going and not going Industries
    // Check if the attendees key exists and is an array
    if (isset($ticketData['attendees']) && is_array($ticketData['attendees'])) {
        foreach ($ticketData['attendees'] as $industry) {
            // Check if the ticket title is 'Industry'
            if (isset($industry['ticket']['title']) && $industry['ticket']['title'] === 'Industry') {
                $industries[] = $industry;
            }
            if (isset($industry['ticket']['title']) && $industry['ticket']['title'] === 'Industry' && $industry['rsvp_going'] === 'true') {
                $industries_going[] = $industry;
            }
        }
    }

    //
    //Get the total number of representatives for each industry with a "going" status
    //

    $total_representatives = 0;

    // Check if the attendees key exists and is an array
    if (isset($ticketData['attendees']) && is_array($ticketData['attendees'])) {
        foreach ($ticketData['attendees'] as $attendee) {
            // Check if the ticket title is 'Industry', RSVP status is 'true', and the representatives key exists
            if (isset($attendee['ticket']['title']) && $attendee['ticket']['title'] === 'Industry' && $attendee['rsvp_going'] === 'true' &&
                isset($attendee['information']['How many company representatives will be joining?'])) {
                 
                // Add the number of representatives to the total
                $total_representatives += intval($attendee['information']['How many company representatives will be joining?']);
            }
        }
    }


    //
    //Determin which year the student is in.
    //If the grad date is any year other than the year the event is in, it assumes they are year 1.
    $year_1_students = [];
    $year_2_students = [];
    $event_year = $eventData['start_date_details']['year'];
    // Check if the attendees key exists and is an array
    if (isset($ticketData['attendees']) && is_array($ticketData['attendees'])) {
        foreach ($ticketData['attendees'] as $attendee) {
            // Check if the ticket title is 'Student'
            if (isset($attendee['ticket']['title']) && $attendee['ticket']['title'] === 'Student') {
                // Get the expected graduation year
                $grad_year = $attendee['information']['What is your expected (or completed) year of graduation?'];
                
                // Differentiate between year 1 and year 2 students
                if ($grad_year == $event_year) {
                    $year_2_students[] = $attendee;
                } else {
                    $year_1_students[] = $attendee;
                }
            }
        }
    }


    //Builds the tables for viewing the data.

    if (isset($eventData['error'])) {
        echo '<p>' . $eventData['error'] . '</p>';
    } else {
		//Event Info Table
        echo '<div class="container">';
		echo '<table class="table mt-4" style="width: 600px;">';
		echo '<thead>';
		echo '<tr>';
		echo '<th class="table-info" scope="col" colspan="2">Event Info</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		echo '<tr><th scope="row">When:</th><td>Start: ' . esc_html($eventData["start_date"]) . ' End: ' . esc_html($eventData["end_date"]) . '</td></tr>';
		echo '<tr><th scope="row">What:</th><td>' . esc_html($eventData["title"]) . '</td></tr>';
		echo '<tr><th scope="row">Where:</th><td>' . esc_html($eventData["venue"]["venue"]) . '</td></tr>';
		echo '</tbody>';
		echo '</table>';

		
        echo '<h2 class="summary-header">Attendance</h2>';

        // Student Tickets Table
		echo '<table class="table mt-4" style="width: 600px;">';
		echo '<thead>';
		echo '<tr>';
		echo '<th class="table-info" scope="col" colspan="3">Students</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		echo '<tr>';
		echo '<th scope="col">First-year Students</th>';
		echo '<th scope="col">Second-year Students</th>';
		echo '<th scope="col">Total</th>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>' . count($year_1_students) . '</td>';
		echo '<td>' . count($year_2_students) . '</td>';
		echo '<td>' . count($students) . '</td>';
		echo '</tr>';
		echo '</tbody>';
		echo '</table>';

        // Industry Tickets Table
		echo '<table class="table mt-4" style="width: 600px;">';
		echo '<thead>';
		echo '<tr>';
		echo '<th class="table-info" scope="col" colspan="3">Organizations</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		echo '<tr>';
		echo '<th scope="col">Number of Organizations</th>';
		echo '<th scope="col">Number of Organizations going</th>';
		echo '<th scope="col">Number of Organization representatives going</th>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>' . count($industries) . '</td>';
		echo '<td>' . count($industries_going) . '</td>';
		echo '<td>' . esc_html($total_representatives) . '</td>';
		echo '</tr>';
		echo '</tbody>';
		echo '</table>';

    }
    wp_die();
}
add_action('wp_ajax_handle_event_summary_data', 'handle_event_summary_data');
add_action('wp_ajax_nopriv_handle_event_summary_data', 'handle_event_summary_data');




function export_event_summary_to_csv() {
    if (!isset($_POST['event_id']) || !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'wp_rest')) {
        wp_die('Invalid request.');
    }

    $eventId = intval($_POST['event_id']);
    $eventData = get_event_data($eventId);  // Fetch event data
    $ticketData = get_ticket_data($eventId);  // Fetch ticket data
	var_dump($eventData);
    $csv_data = [];
    $csv_data[] = ['Event Info', $eventData];
    $csv_data[] = ['When', 'Start: ' . $eventData["start_date"], 'End: ' . $eventData["end_date"]];
    $csv_data[] = ['What', $eventData["title"]];
    $csv_data[] = ['Where', $eventData["venue"]];
	$csv_data[] = ['Ticket Info', $ticketData];
	var_dump($ticketData);
    // $csv_data[] = [];
    // $csv_data[] = ['Attendance'];
    // $csv_data[] = ['First-year Students', 'Second-year Students', 'Total Students'];
    // $csv_data[] = [count($eventData['year_1_students']), count($eventData['year_2_students']), count($eventData['students'])];
    // $csv_data[] = ['Number of Organizations', 'Number of Organizations going', 'Number of Organization representatives going'];
    // $csv_data[] = [count($ticketData['industries']), count($ticketData['industries_going']), $ticketData['total_representatives']];

    // Output CSV headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="event_summary.csv"');

    // Output CSV content
    $output = fopen('php://output', 'w');
    foreach ($csv_data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}
add_action('admin_post_export_event_summary_to_csv', 'export_event_summary_to_csv');
add_action('admin_post_nopriv_export_event_summary_to_csv', 'export_event_summary_to_csv');

function get_event_data($eventId) {
    $url = tribe_events_rest_url() . "events/$eventId";
    
    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        return [];
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (isset($data['error'])) {
        return [];
    }
    
    // Process event data
    $eventData = [];
    $eventData['start_date'] = $data['start_date'];
    $eventData['end_date'] = $data['end_date'];
    $eventData['title'] = $data['title'];
    $eventData['venue'] = $data['venue']['venue'];

    return $eventData;
}


function get_ticket_data($eventId) {
    $url = tribe_tickets_rest_url() . "attendees?post_id=".$eventId;
    $args['headers'] = [
		'X-WP-Nonce' =>  wp_create_nonce("wp_rest"),
	];
	var_dump(wp_create_nonce("wp_rest"));
    $response = wp_remote_get($url, $args);
    if (is_wp_error($response)) {
        return [];
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (isset($data['error'])) {
        return [];
    }
    return $data;
}



function general_summary_shortcode() {
    //This is a built in function via the events calendar to get all events.
    $events = get_all_events(); 
    ?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <label for="events">Choose an event:</label>
        <select name="event_id" id="events">
            <?php
            foreach ($events as $event) {
                echo "<option value='" . $event["id"] . "'>" . $event["title"] . "</option>";
            }
            ?>
        </select>
        <p>Export Event Tickets data to CSV or View Data Live.</p>
        <input type="hidden" name="action" value="export_event_summary_to_csv">
        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('wp_rest'); ?>">
        <input type="submit" name="export" value="Export to CSV" class="button button-primary">
        <button type="button" id="view-live-data" class="button button-secondary">View Data Live</button>
    </form>

    <h1>General Summary Information:</h1>
    <div id="live-data"></div>

<script>
jQuery(document).ready(function($) {
    $('#view-live-data').click(function() {
        var eventId = $('#events').val();
        var eventData, ticketData;

        // Function to handle the combined data
        function handleCombinedData() {
            $.ajax({
                url: ajax_url,
                type: 'POST',
                data: {
                    action: 'handle_event_summary_data',
                    event_data: eventData,
                    ticket_data: ticketData,
                    event_id: eventId,
                    _wpnonce: nonce
                },
                success: function(response) {
                    $('#live-data').html(response);
                },
                error: function(error) {
                    $('#live-data').html('<p>Error processing data. Please try again.</p>');
                }
            });
        }

        // Fetch event data
        $.ajax({
            url: events_root + 'events/' + eventId,
            type: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', nonce);
            },
            success: function(response) {
                eventData = response;

                // Fetch ticket data
                $.ajax({
                    url: tickets_root + 'attendees?post_id=' + eventId,
                    type: 'GET',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', nonce);
                    },
                    success: function(response) {
                        ticketData = response;
                        handleCombinedData();
                    },
                    error: function(error) {
                        $('#live-data').html('<p>Error retrieving ticket data. Please try again.</p>');
                    }
                });
            },
            error: function(error) {
                $('#live-data').html('<p>Error retrieving event data. Please try again.</p>');
            }
        });
    });
});

</script>
    <?php
}
add_shortcode('view_general_summary', 'general_summary_shortcode');
    
