<?php
//This is the unique handle data function where you will formulate your data output.
function handle_organization_data() {
	//this creates the link of where your going to pull your "$_POST" data from. 
    check_ajax_referer('wp_rest', '_wpnonce');
    $eventId = intval($_POST['event_id']);
    $attendees = $_POST['attendees'];
    
	//dump the data onto the report page if you want to see the structure.
	//var_dump($attendees);

// Display tables with counts
        // Initialize counters
        $industryCount = 0;
        $industryRepCount = 0;
    
    // Iterate over attendees to count those with 'Industry' in their ticket title and RSVP going
    foreach ($attendees['attendees'] as $attendee) {

        // Check if attendee has 'Industry' in their ticket title and is RSVP going
        if (strpos($attendee['ticket']['title'], 'Industry')!== false && $attendee['rsvp_going'] === 'true') {
            $industryCount++;

            // Extract number of company representatives from attendee's information
            $companyRepString = $attendee['information']['How many company representatives will be joining?'];
            $companyRepNum = intval($companyRepString); // Convert string to integer

            // Increment by number of company representatives for attendees who are RSVP going
            $industryRepCount += $companyRepNum;
        }
    }


// Display Main Report 	
	 // Define mapping for headers 
    $specificHeaderMapping = [
        "What is your primary phone number?" => "Phone Number",
        "What is their position within the organization?" => "Position within Organization",
        "How would you describe your organization?" => "Description",
        "What is your organization's website?" => "Website",
        "How many company representatives will be joining?" => "Rep Count"
    ];

    // Initialize variables to hold data for report
    $reportData = [];

    // Iterate over attendees to collect data for those with 'Industry' in their ticket title and RSVP going
    foreach ($attendees['attendees'] as $attendee) {
        if (strpos($attendee['ticket']['title'], 'Industry')!== false && $attendee['rsvp_going'] === 'true') {
 

		// Collect information for each attendee
		$info = [];
		foreach ($attendee['information'] as $key => $value) {
			// Check if key is "What is your organization name?"
			if ($key === 'What is your organization name?') {
				// Prepend organization name
				$info['organization_name'] = $value; 
			} else {
				$info[$key] = $value;
			}
		}
		// Add attendee's title and email to info array
		$info['title'] = $attendee['title'];
		$info['email'] = $attendee['email']; 

		// Add attendee information to report data
		$reportData[] = $info;

				}
			}


	
    if (isset($attendees['error'])) {
        echo '<p>' . $attendees['error'] . '</p>';
    } else {
       // Put all your report results here. i.e; a table.
       echo '<div class="container">';

		// Display industry registration count (RSVP going)
        echo '<table class="table mt-4" style="width: 200px;">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="table-info" scope="col">Organizations</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        echo '<tr>';
        echo '<td>'.$industryCount.'</td>'; 
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';

		// Display industry representative count (RSVP going)
        echo '<table class="table mt-4" style="width: 200px;">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="table-info" scope="col">Representatives</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        echo '<tr>';
        echo '<td>'.$industryRepCount.'</td>'; 
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';

		// Display main report with industry registration information (RSVP going)
		echo '<table class="table mt-4 table-striped">';
		echo '<thead>';
		echo '<tr>';
		echo '<th class="table-info">Organization Name</th>'; 
		echo '<th class="table-info">Rep Name</th>'; 
		echo '<th class="table-info">Email</th>'; 
		foreach ($reportData[0] as $key => $value) {
			// Use specific header mapping for keys
			if (isset($specificHeaderMapping[$key])) {
				echo '<th class="table-info">'. htmlspecialchars($specificHeaderMapping[$key]). '</th>';
			} else {
				// Exclude 'title', 'email', and 'organization_name' from headers
				if ($key!== 'title' && $key!== 'email' && $key!== 'organization_name') {
					echo '<th class="table-info">'. htmlspecialchars($key). '</th>';
				}
			}
		}
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		// Generate table rows for each attendee
		// Initialize index to track current position in $reportData
		$index = 0; 
		foreach ($reportData as $row) {
			echo '<tr>';
			// Display organization name first
			echo '<td>'. htmlspecialchars($row['organization_name']). '</td>'; 
			// Display attendee's name
			echo '<td>'. htmlspecialchars($row['title']). '</td>'; 
			// Display attendee's email
			echo '<td>'. htmlspecialchars($row['email']). '</td>'; 
			// Process remaining keys in order they appear in the $reportData array
			foreach ($row as $key => $value) {
				if ($key!== 'title' && $key!== 'email' && $key!== 'organization_name') {
					echo '<td>'. htmlspecialchars($value). '</td>';
				}
			}
			echo '</tr>';
			$index++; 
		}
		echo '</tbody>';
		echo '</table>';




        echo '</div>';
    }
    wp_die();
}
//ensure in the next report that these names are unique.
add_action('wp_ajax_handle_organization_data', 'handle_organization_data');
add_action('wp_ajax_nopriv_handle_organization_data', 'handle_organization_data');


function organization_shortcode() {
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

    <h1 class="mt-4">Organization Reports:</h1>
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
                        action: 'handle_organization_data',
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
add_shortcode('view_organization', 'organization_shortcode');