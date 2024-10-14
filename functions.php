<?php
/**
 * IT Job Fair functions and definitions
 */

 if ( ! function_exists( 'itjobfair_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various
	 * WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme
	 * hook, which runs before the init hook. The init hook is too late
	 * for some features, such as indicating support post thumbnails.
	 */
	function itjobfair_setup() {

		/**
		 * Add default posts and comments RSS feed links to <head>.
		 */
		add_theme_support( 'automatic-feed-links' );

		/**
		 * Enable support for post thumbnails and featured images.
		 */
		add_theme_support( 'post-thumbnails' );

		/**
		 * Add support for two custom navigation menus.
		 */
		register_nav_menus( array(
			'primary'   => __( 'Primary Menu', 'itjobfair' ),
			'secondary' => __( 'Secondary Menu', 'itjobfair' ),
		) );

		/**
		 * Enable support for the following post formats:
		 * aside, gallery, quote, image, and video
		 */
		add_theme_support( 'post-formats', array( 'aside', 'gallery', 'quote', 'image', 'video' ) );
	}
endif; // itjobfair_setup
add_action( 'after_setup_theme', 'itjobfair_setup' );

// Enqueue Bootstrap CSS and JS
function enqueue_bootstrap_admin() {
    // Check if we are on one of the event report admin pages
    $screen = get_current_screen();
    if (strpos($screen->id, 'event-reports') !== false) {
        // Enqueue Bootstrap CSS
    wp_enqueue_style('bootstrap_styles', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');


        // Enqueue Bootstrap JS and dependencies
        wp_enqueue_script('jquery'); // Bootstrap requires jQuery
    wp_enqueue_script('bootstrap_scripts', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array(), null, true);	
    }
}
add_action('admin_enqueue_scripts', 'enqueue_bootstrap_admin');

//
// Add Reports Dashboard Menu
//
// Add menu items to the admin dashboard
function add_event_reports_menu() {

    // Main menu item
    add_menu_page(
        'General Summary', // Page title
        'Event Reports', // Menu title
        'manage_options', // Capability
        'event-reports', // Menu slug
        '', // Function to display page content
        'dashicons-chart-line', // Icon URL
        7 // Position
    );

    // Submenu items
    add_submenu_page(
        'event-reports', // Parent menu slug
        'General Summary', // Submenu page title
        'General Summary', // Submenu menu title
        'manage_options', // Capability
        'event-reports', // Submenu slug
        'display_general_summary_page' // Function to display page content
    );
    
    add_submenu_page(
        'event-reports', 
        'Organizations', 
        'Organizations', 
        'manage_options', 
        'event-reports-organizations', 
        'display_organizations_page' 
    );
    add_submenu_page(
        'event-reports', 
        'Students', 
        'Students',
        'manage_options', 
        'event-reports-students', 
        'display_students_page' 
    );
//     add_submenu_page(
//         'event-reports', 
//         'Positions', 
//         'Positions', 
//         'manage_options', 
//         'event-reports-positions', 
//         'display_positions_page' 
//     );
//     add_submenu_page(
//         'event-reports', 
//         'Career Development', 
//         'Career Development', 
//         'manage_options', 
//         'event-reports-career-development', 
//         'display_career_development_page' 
//     );
    add_submenu_page(
        'event-reports', 
        'Industry Student Worksheet',
        'Industry Student Worksheet', 
        'manage_options', 
        'event-reports-industry-student-worksheet', 
        'display_industry_student_worksheet_page' 
    );
//     add_submenu_page(
//         'event-reports', 
//         'Mentor Requests', 
//         'Mentor Requests', 
//         'manage_options', 
//         'event-reports-mentor-requests', 
//         'display_mentor_requests_page' 
//     );
//     add_submenu_page(
//         'event-reports', 
//         'Export Contacts', 
//         'Export Contacts', 
//         'manage_options', 
//         'event-reports-export-contacts', 
//         'display_export_contacts_page' 
//     );
}
add_action('admin_menu', 'add_event_reports_menu');

//
//Helper functions for report generation
//
function get_all_events(){
    //url to get all events
    $url = "https://bbird.org/itjobfair_wp/wp-json/tribe/events/v1/events";
    
    // Make API request
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return array('error' => 'Failed to retrieve Events');
    }

    $events = json_decode(wp_remote_retrieve_body($response), true);

    return $events["events"];
}
add_action('get_events', 'get_all_events');

// Function to fetch and process registration counts
function get_the_event_details($eventId) {
    // Construct the API request URL
    $url = "https://bbird.org/itjobfair_wp/wp-json/tribe/events/v1/events/{$eventId}";

    // Make API request
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return array('error' => 'Failed to retrieve Event');
    }

    $event = json_decode(wp_remote_retrieve_body($response), true);

    return $event;
}
add_action('get_event', 'get_the_event_details');


// Include report shortcodes
include('report_shortcodes/general_shortcode.php');
include('report_shortcodes/organization_shortcode.php');
include('report_shortcodes/student_shortcode.php');
include('report_shortcodes/industry_student_worksheet_shortcode.php');



// Function to display the page content for each menu item
function display_general_summary_page() {
    echo '<div class="wrap">';
    echo '<h1>'. esc_html__('General Summary', 'text-domain'). '</h1>';
    echo do_shortcode('[view_general_summary]');
    echo '</div>';
}

function display_organizations_page() {
    echo '<div class="wrap">';
    echo '<h1>'. esc_html__('Organizations', 'text-domain'). '</h1>';
    echo do_shortcode('[view_organization]');
    echo '</div>';
}

function display_students_page() {
    echo '<div class="wrap">';
    echo '<h1>'. esc_html__('Students', 'text-domain'). '</h1>';
    echo do_shortcode('[view_student]');
    echo '</div>';
}

function display_positions_page() {
    echo '<div class="wrap">';
    echo '<h1>'. esc_html__('Positions', 'text-domain'). '</h1>';
  
    echo '</div>';
}

function display_career_development_page() {
    echo '<div class="wrap">';
    echo '<h1>'. esc_html__('Career Development', 'text-domain'). '</h1>';

    echo '</div>';
}

function display_industry_student_worksheet_page() {
    echo '<div class="wrap">';
    echo '<h1>'. esc_html__('Industry Student Worksheet', 'text-domain'). '</h1>';
  	echo do_shortcode('[view_industry_student_worksheet]');
    echo '</div>';
}

function display_mentor_requests_page() {
    echo '<div class="wrap">';
    echo '<h1>'. esc_html__('Mentor Requests', 'text-domain'). '</h1>';
 
    echo '</div>';
}

function display_export_contacts_page() {
    echo '<div class="wrap">';
    echo '<h1>'. esc_html__('Export Contacts', 'text-domain'). '</h1>';

    echo '</div>';
}
// (plugin short code can be used to display reports on the page)


//
// Font Awesome icons
//
function enqueue_font_awesome() {
    wp_enqueue_style('font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
}
add_action('wp_enqueue_scripts', 'enqueue_font_awesome');



//
// Custom shortcodes
//
// Dynamic Email shortcode
function dynamic_email_shortcode($atts) {
    // Extract attributes
	// Default values
    $atts = shortcode_atts(array(
        'email' => 'industry.itjobfair@nscc.ca', 
        'subject' => 'Industry', 
    ), $atts);

    // Build email link
    $email_link = '<a href="mailto:'. esc_attr($atts['email']). '?subject='. esc_attr($atts['subject']). '">'. esc_html($atts['email']). '</a>';

    // Return HTML structure with dynamic email link
    return '<div class="icon-text-box">'.
           '<figure class="wp-block-image is-resized email-icon"><img src="https://bbird.org/itjobfair_wp/wp-content/uploads/2024/05/email-icon.png" alt="" class="wp-image-193" style="width:auto;height:25px"/></figure>'.
           '<p class="contact-email email-contact">'. $email_link. '</p>'.
           '</div>';
}
add_shortcode('dynamic_email', 'dynamic_email_shortcode');


// Dynamic Icon Text Box shortcode
function icon_text_box_shortcode($atts) {
    // Extract attributes
    $atts = shortcode_atts(array(
		// Default values
        'image_url' => 'https://bbird.org/itjobfair_wp/wp-content/uploads/2024/05/pin.png',
        'text' => 'Civic Address', 
    ), $atts);

    // Build HTML structure with dynamic image and text
    $html = '<div class="icon-text-box">'.
            '<figure class="wp-block-image is-resized pin-icon"><img src="'. esc_attr($atts['image_url']).'" alt="Address Pin" class="wp-image-277" style="width:auto;height:35px"/></figure>'.
            '<p class="civic-address">'. esc_html($atts['text']).'</p>'.
            '</div>';

    // Return HTML structure
    return $html;
}
add_shortcode('icon_text_box', 'icon_text_box_shortcode');


// Map Contact shortcode
function google_maps_shortcode($atts) {
    // Extract attributes
	// Default values
    $atts = shortcode_atts(array(
        'width' => '100%', 
        'height' => '500', 
        'location' => '5685 Leeds St (NSCC)',
    ), $atts);

    // Build iframe HTML structure with dynamic attributes
    $iframe = '<div style="width: 100%;"><iframe width="'. esc_attr($atts['width']).'" height="'. esc_attr($atts['height']).'" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height='. esc_attr($atts['height']).'&amp;hl=en&amp;q='. urlencode($atts['location']).'&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe></div>';

    // Return iframe HTML structure
    return $iframe;
}
add_shortcode('google_maps', 'google_maps_shortcode');


// Button shortcode
function custom_button_shortcode($atts) {
    // Extract attributes
    // Default values
    $atts = shortcode_atts(array(
        'text' => 'Industry Registration',
        'url' => '#'
    ), $atts);

    // Output button HTML without the.is-style-outline class
    $output = '<div class="wp-block-button has-custom-font-size main-button">';
    $output.= '<a class="wp-block-button__link has-text-color has-background has-link-color wp-element-button text-center" href="'. esc_url($atts['url']). '">'. $atts['text']. ' ';
   // Wrap icon in a span with a custom class
   $output.= '<span class="icon-wrapper">';
   $output.= '<i class="fa fa-angle-double-right" aria-hidden="true"></i>'; 
   $output.= '</span>';
    $output.= '</a></div>';

    return $output;
}
add_shortcode('custom_button', 'custom_button_shortcode');


//Map Event shortcode
function map_shortcode($atts) {
    // Shortcode attributes, e.g., the event ID
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts, 'event_map');

    if (empty($atts['id'])) {
        return 'Event ID is required.';
    }

    $event_id = $atts['id'];

    //built in function to verify the provided ID points to an event
    $is_event = tribe_is_event($event_id);
    if($is_event){
        //built in function with the events calendar plugin to output the map of an event given an ID.
        return "<div class='event_map'>".tribe_get_embedded_map($event_id).'</div>';
    }else{
        return "<p>Event ID Not Found</p>";
    }
}
add_shortcode('event_map', 'map_shortcode');


// Event Address shortcode
function event_location_shortcode($atts) {
    // Shortcode attributes, e.g., the event ID
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts, 'event_location_date');

    if (empty($atts['id'])) {
        return 'Event ID is required.';
    }

    $event_id = $atts['id'];

    //built in function to verify the provided ID points to an event
    $is_event = tribe_is_event($event_id);
    if($is_event){
        //built in function with the events calendar plugin to output the venue name and start/end date of an event given an ID.
        return '<h4 class="event_address">'.tribe_get_venue($event_id).'<div class="start_time">'.tribe_get_start_date($event_id).' To '.tribe_get_end_date($event_id, true, "g:i a").'</div>'.'</h4>';
    }else{
        return "<p>Event ID Not Found</p>";
    }
}
add_shortcode('event_location_date', 'event_location_shortcode');


// Number of Industry Attendees shortcode
function event_attendees_industry_shortcode($atts) {
    // Shortcode attributes, e.g., the event ID
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts, 'event_attendees_industry_total');

    if (empty($atts['id'])) {
        return 'Event ID is required.';
    }

    $event_id = $atts['id'];

    $is_event = tribe_is_event($event_id);
    if($is_event){
		//clear the cache before getting all the attendee data, this line is responsible for 
		//making the changes made in the admin screeen live on the front end. 
		Tribe__Post_Transient::instance()->delete( $event_id, Tribe__Tickets__Tickets::ATTENDEES_CACHE );
        //Get all the attendees for the event
        $all_attendees = tribe_tickets_get_attendees($event_id);

        //funcation call bellow. but this filters out all the "Not going" industry partners.
        $industry_going = array_filter($all_attendees, "filter_for_industry"); 
        // Output the result
        if ($industry_going) {
            //get the count from the ticket Array. the count represents the number on industries going. 
            return "<h3 class='industry_attendees'>"."Participating Organizations: ".count($industry_going)."</h3>";
        } else {
            return "<h3 class='industry_attendees'>"."Participating Organizations: 0 </h3>";
        }
    }
}
add_shortcode('event_attendees_industry_total', 'event_attendees_industry_shortcode');


//Number of Student Attendees shortcode
function event_attendees_student_shortcode($atts) {
    // Shortcode attributes, e.g., the event ID
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts, 'event_attendees_student_total');

    if (empty($atts['id'])) {
        return 'Event ID is required.';
    }

    $event_id = $atts['id'];

    $is_event = tribe_is_event($event_id);
    if($is_event){
        $tickets = Tribe__Tickets__Tickets::get_all_event_tickets( $event_id );

        //declared function at the bottom, need to filter out all other tickets asside from student.
        $studentTicket = findFirstTicketByName($tickets, "Student");

        // Output the result
        if ($studentTicket) {
            //get the quantity sold from the ticket object.
            return "<h3 class='student_attendees'>"."Total Students: ".$studentTicket->qty_sold."</h3>";
        } else {
            return "No 'Student' ticket found.";
        }
    }
}
add_shortcode('event_attendees_student_total', 'event_attendees_student_shortcode');


// List of Attendees shortcode
function list_of_attendees_shortcode($atts) {
// Shortcode attributes, e.g., the event ID
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts, 'list_of_attendees');

    if (empty($atts['id'])) {
        return 'Event ID is required.';
    }

    $event_id = $atts['id'];

    $is_event = tribe_is_event($event_id);

    if($is_event){
		//clear the cache before getting all the attendee data, this line is responsible for 
		//making the changes made in the admin screeen live on the front end. 
		Tribe__Post_Transient::instance()->delete( $event_id, Tribe__Tickets__Tickets::ATTENDEES_CACHE );
		//get all attendee data.
        $attendees = tribe_tickets_get_attendees($event_id);
		//var_dump($attendees);
        $organization_list = '<div class="org_container">';
        $organization_list .= '<table class="list_of_org">';
        $organization_list .= '<tbody>';
        foreach ($attendees as $attendee) {
            if (str_contains($attendee['ticket'], 'Industry') && $attendee['order_status_label'] === 'Going') {
                //'what-is-your-organization-name' contains the organization's name, if the question is altered
                //this will probably break.
                $org_name = $attendee['attendee_meta']['what-is-your-organization-name']['value'] ?? 'Unknown Organization';
                if ($org_name !== 'Unknown Organization') {
                    $filtered_org_name = preg_replace("/[^A-Za-z0-9 ]/", '', $org_name);
                    $organization_list .= '<tr>'.'<td>' . esc_html($filtered_org_name).'</td>' . '</tr>';
                }
            }
        }
        $organization_list .= '</tbody>';
        $organization_list .= '</table>';
        $organization_list .= '</div>';
        return $organization_list;

    }else{
        return "<p>No Industries Registered</p>";
    }
}
add_shortcode('list_of_attendees', 'list_of_attendees_shortcode');


// 
// Custom Functions
//
// Function to find the first ticket with the provided name
function findFirstTicketByName($tickets, $name) {
    foreach ($tickets as $ticket) {
        //if the name of the ticket contains the name provided, assume this is the ticket
        if (str_contains($ticket->name, $name)) {
            return $ticket;
        }
    }
    return null; // Return null if no match is found
}

//function to filter out industries that are "Not going". 
function filter_for_industry($var){
    //This will break of the ticket name doesnt include the word industry. 
    if(str_contains($var['ticket_name'], 'Industry') && $var['order_status_label'] === 'Going'){
        return $var;
    }
}

//function to filter out students that are "Not going". 
function filter_for_students($var){
    //This will break of the ticket name doesnt include the word student. 
    if(str_contains($var['ticket_name'], 'student') && $var['order_status_label'] === 'Going'){
        return $var;
    }
}

//
//Function for overriding the default "going" status - this happens for ONLY the industry ticket, 
//To ensure proper functionality make sure the ticket name atleast contains "industry" somewhere.
//
add_action('event_tickets_rsvp_attendee_created', 'custom_set_default_rsvp_status', 10, 4);

function custom_set_default_rsvp_status($attendee_id, $post_id, $order_id, $product_id) {
    // Load the ticket object using the product ID
    $ticket = Tribe__Tickets__Tickets::load_ticket_object($product_id);

    // Check if the ticket object is valid and if the ticket name contains "industry" (case-insensitive)
    if ($ticket && stripos($ticket->name, 'industry') !== false) {
        // Override the status to 'not going'
        update_post_meta($attendee_id, "_tribe_rsvp_status", 'no');
    }
}



//
// Add styles and scripts
//
function itjobfair_enqueue_styles() {
    // Add Bootstrap CSS and JS
    wp_enqueue_style('bootstrap_styles', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap_scripts', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array(), null, true);

    // Add theme's style.css
    wp_enqueue_style('itjobfair_theme_styles', get_stylesheet_uri());

    // Add Google Font Nunito
    wp_enqueue_style('google_fonts', 'https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap');

    // Add custom script to toggle agenda
    wp_enqueue_script('script', get_stylesheet_directory_uri() . '/toggleAgenda.js', array('jquery'), null, true);

}
add_action('wp_enqueue_scripts', 'itjobfair_enqueue_styles');


function url_and_authorization_vars(){ 
	?>
	<script type="text/javascript">
		var events_root = '<?php echo esc_url_raw(tribe_events_rest_url()); ?>';
		var tickets_root = '<?php echo esc_url_raw(tribe_tickets_rest_url()); ?>';
		var nonce = '<?php echo wp_create_nonce("wp_rest"); ?>';
		var ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';
	</script>
	<?php
}
add_action ( 'admin_head', 'url_and_authorization_vars' );

?>