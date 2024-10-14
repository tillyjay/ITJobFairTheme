<?php
//This is the unique handle data function where you will formulate your data output.
function handle_industry_student_worksheet() {
	//this creates the link of where your going to pull your "$_POST" data from. 
    check_ajax_referer('wp_rest', '_wpnonce');
    $eventId = intval($_POST['event_id']);
    $attendees = $_POST['attendees'];
    
	//dump the data onto the report page if you want to see the structure.
	//var_dump($attendees);
	    
    // Initialize variables to hold data for report
    $reportData = [];

    // Iterate over attendees to collect data for those with 'Student' in their ticket title
    foreach ($attendees['attendees'] as $attendee) {
        if (strpos($attendee['ticket']['title'], 'Student') !== false && $attendee['rsvp_going'] === 'true') {
            // Collect information for each attendee
            $info = [];
            $info['student_id'] = $attendee['information']['Student ID'];
            $info['name'] = $attendee['title'];
            $info['email'] = $attendee['email'];
            $info['program'] = $attendee['information']['Program'];
            $info['grad_year'] = $attendee['information']['What is your expected (or completed) year of graduation?'];
            $info['campus'] = $attendee['information']['Campus attended'];

            // Add attendee information to report data
            $reportData[] = $info;
        }
    }
		
		
    if (isset($attendees['error'])) {
        echo '<p>' . $attendees['error'] . '</p>';
    } else {
       	// Put all your report results here. i.e; a table.
       	echo '<div class="container">';

		// Display student information
		echo '<table class="table mt-4 table-striped">';
		echo '<thead>';
		echo '<tr>';
		echo '<th class="table-info" scope="col">Student ID</th>';
		echo '<th class="table-info" scope="col">Name</th>';
		echo '<th class="table-info" scope="col">Email</th>';
		echo '<th class="table-info" scope="col">Program</th>';
		echo '<th class="table-info" scope="col">Grad Year</th>';
		echo '<th class="table-info" scope="col">Campus</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		// Generate table rows for each attendee
		foreach ($reportData as $row) {
			echo '<tr>';
			echo '<td>' . htmlspecialchars($row['student_id']) . '</td>';
			echo '<td>' . htmlspecialchars($row['name']) . '</td>';
			echo '<td>' . htmlspecialchars($row['email']) . '</td>';
			echo '<td>' . htmlspecialchars($row['program']) . '</td>';
			echo '<td>' . htmlspecialchars($row['grad_year']) . '</td>';
			echo '<td>' . htmlspecialchars($row['campus']) . '</td>';
			echo '</tr>';
		}
        echo '</tbody>';
        echo '</table>';
		
		
       echo '</div>';   
    }
wp_die();
}
//ensure in the next report that these names are unique.
add_action('wp_ajax_handle_industry_student_worksheet', 'handle_industry_student_worksheet');
add_action('wp_ajax_nopriv_handle_industry_student_worksheet', 'handle_industry_student_worksheet');


function industry_student_worksheet_shortcode() {
//returns ALL event data, I'm just using this to get the title and ID's of each even. 
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
    <input type="hidden" name="action" value="handle_tickets">
    <input type="submit" name="export" value="Export to CSV" class="button button-primary">
    <button type="button" id="view-live-data" class="button button-secondary">View Data Live</button>
</form>

<h1 class="mt-4">Industry Student Worksheet Report:</h1>
<!-- this is the div element that our script at the bottom is targeting	 -->
<div id="live-data"></div>

<script>
jQuery(document).ready(function($) {
//when our "view live data" button is clicked
$('#view-live-data').click(function() {
    //grab the event id from select drop down.
    var eventId = $('#events').val();
    $.ajax({
        //this is where you will need to change the URL to adjust your needs
        //determined that you can limit the attendees returned by inputing the related post ID like so.
        //refrence the events calendar API docs for more url fields for further specifying results
        url: tickets_root + 'attendees?post_id=' + eventId,
        type: 'GET',
        //authorization
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', nonce);
        },
        success: function(response) {
            // After fetching the data, send it to the WordPress AJAX handler 
            // ie) send the data to the "handle" function defined at the top.
            $.ajax({
                url: ajax_url,
                type: 'POST',
                data: {
                    //when the "handle" function changes be sure to update the name here.
                    action: 'handle_industry_student_worksheet',
                    //determins what name you get the data by, example:  $_POST['attendees'];
                    attendees: response,
                    event_id: eventId,
                    _wpnonce: nonce
                },
                success: function(data) {
                    $('#live-data').html(data);
                },
                error: function(error) {
                    $('#live-data').html('<p>Error processing data. Please try again.</p>');
                }
            });
        },
        error: function(error) {
            $('#live-data').html('<p>Error retrieving data. Please try again.</p>');
        }
    });
});
});

</script>
<?php
}
add_shortcode('view_industry_student_worksheet', 'industry_student_worksheet_shortcode');