<?php
/*
Plugin Name: Custom Gift Card Plugin
Description: A plugin to create and manage gift cards with a custom form and PayPal payment integration.
Version: 1.6
Author: Jay Dev
*/

function enqueue_gift_card_scripts() {
    wp_enqueue_script('jquery'); // Make sure jQuery is loaded
    wp_enqueue_script('gift-card-scripts', plugin_dir_url(__FILE__) . 'script.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_gift_card_scripts');


add_action('wp_enqueue_scripts', 'enqueue_paypal_sdk');

function enqueue_paypal_sdk() {
    wp_enqueue_script('paypal-sdk', 'https://www.paypal.com/sdk/js?client-id=' . get_option('paypal_client_id'), array(), null, true);
}



// Enqueue the CSS file
function enqueue_gift_card_styles() {
    wp_enqueue_style('gift-card-styles', plugin_dir_url(__FILE__) . 'style.css', array(), '1.0', 'all');
}
add_action('wp_enqueue_scripts', 'enqueue_gift_card_styles');

// Create Custom Post Type
function create_gift_cards_post_type() {
    register_post_type('gift_cards',
        array(
            'labels' => array(
                'name' => __('Gift Cards'),
                'singular_name' => __('Gift Card')
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor', 'thumbnail'),
            'show_in_rest' => true,
        )
    );
}
add_action('init', 'create_gift_cards_post_type');

// Create Custom Taxonomy
function create_gift_card_taxonomy() {
    register_taxonomy(
        'gift_card_category',
        'gift_cards',
        array(
            'label' => __('Gift Card Categories'),
            'rewrite' => array('slug' => 'gift-card-category'),
            'hierarchical' => true,
        )
    );
}
add_action('init', 'create_gift_card_taxonomy');

// Create Custom Form Shortcode
function gift_card_form_shortcode() {
    $paypal_client_id = get_option('paypal_client_id', ''); // Get the PayPal client ID from settings

   

    ob_start();
    ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <div class="plugin-css">
    <div class="form-css">
    <form id="gift-card-form">
        
        <div>
            <label for="gift-card-category">Gift Card Category:</label>
            <select id="gift-card-category" name="gift_card_category">
                <option value="">Select a category</option>
                <?php
                $categories = get_terms(array(
                    'taxonomy' => 'gift_card_category',
                    'hide_empty' => false,
                ));
                foreach ($categories as $category) {
                    echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
                }
                ?>
            </select>
        </div>
        <div>
            <label for="gift-card-post">Gift Card:</label>
            <select id="gift-card-post" name="gift_card_post">
                <option value="">Select a gift card</option>
            </select>
        </div>
       <div class="formfields">
       <div>
            <label for="your-name">Your Name:</label>
            <input type="text" id="your-name" name="your_name" required>
        </div>
        <div>
            <label for="recipient-name">Recipient Name:</label>
            <input type="text" id="recipient-name" name="recipient_name" required>
        </div>
        <div>
            <label for="voucher-value">Voucher Value (Min $50):</label>
            <input type="number" id="voucher-value" name="voucher_value" min="50" max="2000" required>
        </div>
        <div>
            <label for="personal-message">Personal Message (Optional, Max 250 Characters):</label>
            <textarea id="personal-message" name="personal_message" maxlength="250"></textarea>
        </div>
        <div>
            <label for="recipient-email">Recipient Email:</label>
            <input type="email" id="recipient-email" name="recipient_email" required>
        </div>
        <div>
            <label for="your-email">Your Email (for the receipt):</label>
            <input type="email" id="your-email" name="your_email" required>
        </div>
        <div>
            <label for="payment-method">Payment Method:</label>
            <div id="paypal-button-container"></div>
        </div>
        <div id="order-summary">
    <h3>Order Summary</h3>
    <table>
        <tr>
            <td>Gift Card from:</td>
            <td id="order-gift-name"></td>
        </tr>
        <tr>
            <td>Item:</td>
            <td id="order-gift-item"></td>
        </tr>
        <tr>
            <td>Total:</td>
            <td id="order-total-price"></td>
        </tr>
    </table>
</div>

       </div>
       
    </form>
    </div>
    <div class="form-preview">
    <div id="gift-card-content">
    <h5>Please Select Any Gift Card to Preview</h5>
    </div>
    <div class="live-preview">
    <form>
    <label for="your-name">Your Name:</label>
    <input type="text" id="your-name" name="your-name" disabled>
    
    <label for="recipient-name">Recipient Name:</label>
    <input type="text" id="recipient-name" name="recipient-name" disabled>
    
    <label for="voucher-value">Voucher Value($):</label>
    <input type="text" id="voucher-value" name="voucher-value" disabled>
    
    <label for="personal-message">Personal Message:</label>
    <span type="text" id="personal-message"></span>
    
    <label for="recipient-email">Recipient Email:</label>
    <input type="text" id="recipient-email" name="recipient-email" disabled>
    
    <label for="your-email">Your Email(for the receipt):</label>
    <input type="text" id="your-email" name="your-email" disabled>
    
    <label for="expiry-date">Expiry Date:</label>
    <input type="text" id="expiry-date" name="expiry-date" disabled>
</form>
    </div>
    
    </div>
    </div>
    
 <script src="https://www.paypal.com/sdk/js?client-id=<?php echo esc_attr($paypal_client_id); ?>&currency=USD"></script>
        <script>

function getExpiryDate() {
        let today = new Date();
        let nextYear = new Date(today.setFullYear(today.getFullYear() + 1));
        let dd = String(nextYear.getDate()).padStart(2, '0');
        let mm = String(nextYear.getMonth() + 1).padStart(2, '0'); // January is 0!
        let yyyy = nextYear.getFullYear();

        return mm + '/' + dd + '/' + yyyy;
    }

    // Set the expiry date in the form field
    document.getElementById('expiry-date').value = getExpiryDate();


    
document.addEventListener('DOMContentLoaded', function () {

    if (typeof paypal === 'undefined') {
        alert('PayPal SDK not loaded');
        return;
    }
    paypal.Buttons({
        createOrder: function (data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: document.getElementById('voucher-value').value
                    }
                }]
            });
        },
        onApprove: function (data, actions) {
            return actions.order.capture().then(function (details) {
                // alert('Transaction completed by ' + details.payer.name.given_name);
                // console.log(details);
                // Add transaction ID to form data
                let formData = new FormData(document.getElementById('gift-card-form'));
                formData.append('transaction_id', details.id);
                formData.append('img_src', document.getElementById('gift-card-content').querySelector('img').src);
                // Submit the form automatically
                fetch('<?php echo admin_url('admin-ajax.php?action=submit_gift_card_form'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                       
                        Swal.fire({
            
                            title: "Order Completed",
                            text: "Gift Card Sent Successfully",
                            text: `Transaction completed by ${details.payer.name.given_name}`,
                            icon: "success"
                        });


                    } else {
                        alert('There was an error submitting your order. Please try again.');
                        console.error('Error: ', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error: ', error);
                });
            });
        }
    }).render('#paypal-button-container');
    
    document.getElementById('gift-card-category').addEventListener('change', function () {
        let categoryId = this.value;
        if (categoryId) {
            fetch(`<?php echo admin_url('admin-ajax.php?action=get_gift_cards_by_category&category_id='); ?>${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    let giftCardPostSelect = document.getElementById('gift-card-post');
                    giftCardPostSelect.innerHTML = '<option value="">Select a gift card</option>';
                    data.forEach(post => {
                        let option = document.createElement('option');
                        option.value = post.id;
                        option.textContent = post.title;
                        option.dataset.featuredImage = post.featured_image; // Store the featured image URL in a data attribute
                        giftCardPostSelect.appendChild(option);
                    });
                });
        }
    });

    document.getElementById('gift-card-post').addEventListener('change', function () {
    let postId = this.value;
    let giftCardContent = document.getElementById('gift-card-content');
    
    if (postId) {
        fetch(`<?php echo admin_url('admin-ajax.php?action=get_gift_card_content&post_id='); ?>${postId}`)
            .then(response => response.json())
            .then(data => {
                let selectedOption = this.options[this.selectedIndex];
                let featuredImage = selectedOption.dataset.featuredImage;
                let contentHtml = '';
                
                if (featuredImage) {
                    contentHtml += `<img src="${featuredImage}" alt="${data.title}">`;
                }
                
                contentHtml += `<h4>${data.title}</h4><div>${data.content}</div>`;
                
                if (featuredImage || data.content) {
                    giftCardContent.innerHTML = contentHtml;
                    giftCardContent.style.display = 'block';
                } else {
                    giftCardContent.style.display = 'none';
                }
                
                document.getElementById('order-gift-item').textContent = data.title;
            });
    } else {
        giftCardContent.style.display = 'none';
    }
});

});

    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('gift_card_form', 'gift_card_form_shortcode');

function create_gift_card_users_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gift_card_users';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        your_name varchar(255) NOT NULL,
        recipient_name varchar(255) NOT NULL,
        voucher_value float NOT NULL,
        personal_message text NOT NULL,
        recipient_email varchar(255) NOT NULL,
        your_email varchar(255) NOT NULL,
        transaction_id varchar(255) NOT NULL,
        coupon_code varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_gift_card_users_table');



// Fetch Gift Cards by Category
function get_gift_cards_by_category() {
    $category_id = intval($_GET['category_id']);
    $query = new WP_Query(array(
        'post_type' => 'gift_cards',
        'tax_query' => array(
            array(
                'taxonomy' => 'gift_card_category',
                'field'    => 'term_id',
                'terms'    => $category_id,
            ),
        ),
    ));

    $posts = array();
    while ($query->have_posts()) {
        $query->the_post();
        $posts[] = array(
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'featured_image' => get_the_post_thumbnail_url(get_the_ID(), 'full')
        );
    }

    wp_send_json($posts);
}

add_action('wp_ajax_get_gift_cards_by_category', 'get_gift_cards_by_category');
add_action('wp_ajax_nopriv_get_gift_cards_by_category', 'get_gift_cards_by_category');

// Fetch Gift Card Content by Post ID
function get_gift_card_content() {
    $post_id = intval($_GET['post_id']);
    $post = get_post($post_id);

    $data = array(
        'title' => $post->post_title,
        'content' => apply_filters('the_content', $post->post_content),
    );

    wp_send_json($data);
}
add_action('wp_ajax_get_gift_card_content', 'get_gift_card_content');
add_action('wp_ajax_nopriv_get_gift_card_content', 'get_gift_card_content');




function send_gift_card_emails($recipient_email, $your_name, $recipient_name, $voucher_value, $personal_message, $coupon_code, $expiry_date, $your_email, $img_src) {
    // Get the email body templates from the settings
    $recipient_email_body = get_option('recipient_email_body', '');
    $buyer_email_body = get_option('buyer_email_body', '');

    // Replace placeholders with actual values for recipient email
    $recipient_message = str_replace(
        ['{your_name}', '{recipient_name}', '{voucher_value}', '{personal_message}', '{coupon_code}', '{expiry_date}', '{recipient_email}', '{img_src}'],
        [$your_name, $recipient_name, $voucher_value, $personal_message, $coupon_code, $expiry_date, $recipient_email, $img_src],
        $recipient_email_body
    );

    // Send email to recipient
    $recipient_subject = "You've received a gift card from $your_name";
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($recipient_email, $recipient_subject, $recipient_message, $headers);

    // Replace placeholders with actual values for buyer email
    $buyer_message = str_replace(
        ['{your_name}', '{recipient_name}', '{voucher_value}', '{personal_message}', '{coupon_code}', '{expiry_date}', '{recipient_email}', '{img_src}'],
        [$your_name, $recipient_name, $voucher_value, $personal_message, $coupon_code, $expiry_date, $recipient_email, $img_src],
        $buyer_email_body
    );

    // Send email to buyer
    $buyer_subject = "Your gift card order receipt";
    wp_mail($your_email, $buyer_subject, nl2br($buyer_message), $headers);
}




function submit_gift_card_form() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gift_card_users';

    $your_name = sanitize_text_field($_POST['your_name']);
    $recipient_name = sanitize_text_field($_POST['recipient_name']);
    $voucher_value = floatval($_POST['voucher_value']);
    $personal_message = sanitize_textarea_field($_POST['personal_message']);
    $recipient_email = sanitize_email($_POST['recipient_email']);
    $your_email = sanitize_email($_POST['your_email']);
    $transaction_id = sanitize_text_field($_POST['transaction_id']);
    $img_src = sanitize_text_field($_POST['img_src']);

    // Generate WooCommerce Coupon
    $coupon_code = 'gc-' . wp_generate_password(8, false); // Example: Generate a unique coupon code
    $discount_type = 'fixed_cart';
    $amount = $voucher_value;
    $expiry_date = date('Y-m-d', strtotime('+1 year'));

    $coupon = array(
        'post_title' => $coupon_code,
        'post_content' => '',
        'post_status' => 'publish',
        'post_author' => 1,
        'post_type' => 'shop_coupon'
    );

    $new_coupon_id = wp_insert_post($coupon);

    if ($new_coupon_id) {
        update_post_meta($new_coupon_id, 'discount_type', $discount_type);
        update_post_meta($new_coupon_id, 'coupon_amount', $amount);
        update_post_meta($new_coupon_id, 'expiry_date', $expiry_date);
        update_post_meta($new_coupon_id, 'individual_use', 'yes');
        update_post_meta($new_coupon_id, 'product_ids', '');
        update_post_meta($new_coupon_id, 'exclude_product_ids', '');
        update_post_meta($new_coupon_id, 'usage_limit', '');
        update_post_meta($new_coupon_id, 'usage_limit_per_user', '');
        update_post_meta($new_coupon_id, 'limit_usage_to_x_items', '');
        update_post_meta($new_coupon_id, 'free_shipping', 'no');
        update_post_meta($new_coupon_id, 'exclude_sale_items', 'yes');
        update_post_meta($new_coupon_id, 'minimum_amount', '');
        update_post_meta($new_coupon_id, 'maximum_amount', '');
        update_post_meta($new_coupon_id, 'customer_email', array($recipient_email));
    }

    // Insert data into the gift-card-users table
    $result = $wpdb->insert(
        $table_name,
        array(
            'your_name' => $your_name,
            'recipient_name' => $recipient_name,
            'voucher_value' => $voucher_value,
            'personal_message' => $personal_message,
            'recipient_email' => $recipient_email,
            'your_email' => $your_email,
            'transaction_id' => $transaction_id,
            'coupon_code' => $coupon_code,
        )
    );

    if ($result === false) {
        wp_send_json(array('success' => false, 'message' => 'Failed to insert data into table'));
    } else {
        // Send the emails
        send_gift_card_emails($recipient_email, $your_name, $recipient_name, $voucher_value, $personal_message, $coupon_code, $expiry_date, $your_email, $img_src);
        wp_send_json(array('success' => true));
    }
}
add_action('wp_ajax_submit_gift_card_form', 'submit_gift_card_form');
add_action('wp_ajax_nopriv_submit_gift_card_form', 'submit_gift_card_form');




add_action('woocommerce_thankyou', 'reuse_partial_coupon', 10, 1);

function reuse_partial_coupon($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;

    $used_coupons = $order->get_used_coupons();
    if (empty($used_coupons)) return;

    foreach ($used_coupons as $coupon_code) {
        $coupon = new WC_Coupon($coupon_code);

        // Check if the coupon is a fixed amount discount
        if ($coupon->get_discount_type() === 'fixed_cart') {
            $coupon_amount = $coupon->get_amount();
            $order_total = $order->get_subtotal();

            // Calculate remaining coupon credit
			if($coupon_amount < $order_total){
				$remaining_amount = 0;
		
			}
			else{
				$remaining_amount = $coupon_amount - $order_total;
			}
            // if ($remaining_amount > 0) {
                // Update coupon amount
                $coupon->set_amount($remaining_amount);
                $coupon->save();
            // } else {
            //     // Delete the coupon if no credit remains
            //     $coupon->delete(true);
            // }
        }
    }
}

add_action('admin_menu', 'add_gift_card_orders_submenu');

function add_gift_card_orders_submenu() {
    add_submenu_page(
        'edit.php?post_type=gift_cards',
        __('Gift Card Orders', 'textdomain'),
        __('Orders', 'textdomain'),
        'manage_options',
        'gift-card-orders',
        'display_gift_card_orders_page'
    );
}


function display_gift_card_orders_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gift_card_users';

    // Get the data from the database table
    $results = $wpdb->get_results("SELECT * FROM $table_name");

    echo '<div class="wrap">';
    echo '<h1>' . __('Gift Card Orders', 'textdomain') . '</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . __('ID', 'textdomain') . '</th>';
    echo '<th>' . __('Name', 'textdomain') . '</th>';
    echo '<th>' . __('Recipient Name', 'textdomain') . '</th>';
    echo '<th>' . __('Voucher Value', 'textdomain') . '</th>';
    echo '<th>' . __('Personal Message', 'textdomain') . '</th>';
    echo '<th>' . __('Recipient Email', 'textdomain') . '</th>';
    echo '<th>' . __('Email', 'textdomain') . '</th>';
    echo '<th>' . __('Transaction ID', 'textdomain') . '</th>';
    echo '<th>' . __('Coupon Code', 'textdomain') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Loop through the results and display each row
    foreach ($results as $row) {
        echo '<tr>';
        echo '<td>' . esc_html($row->id) . '</td>';
        echo '<td>' . esc_html($row->your_name) . '</td>';
        echo '<td>' . esc_html($row->recipient_name) . '</td>';
        echo '<td> $ ' . esc_html($row->voucher_value) . '</td>';
        echo '<td>' . esc_html($row->personal_message) . '</td>';
        echo '<td>' . esc_html($row->recipient_email) . '</td>';
        echo '<td>' . esc_html($row->your_email) . '</td>';
        echo '<td>' . esc_html($row->transaction_id) . '</td>';
        echo '<td>' . esc_html($row->coupon_code) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}


add_action('admin_menu', 'add_gift_card_settings_submenu');

function add_gift_card_settings_submenu() {
    add_submenu_page(
        'edit.php?post_type=gift_cards',
        __('Gift Card Settings', 'textdomain'),
        __('Settings', 'textdomain'),
        'manage_options',
        'gift-card-settings',
        'display_gift_card_settings_page'
    );
}


function display_gift_card_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Gift Card Settings', 'textdomain'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('gift_card_settings_group');
            do_settings_sections('gift-card-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'register_gift_card_settings');

function register_gift_card_settings() {
    // Register settings
    register_setting('gift_card_settings_group', 'gift_card_custom_css');
    register_setting('gift_card_settings_group', 'paypal_client_id'); // Register the PayPal client ID setting
    register_setting('gift_card_settings_group', 'recipient_email_body'); // Register the recipient email body setting
    register_setting('gift_card_settings_group', 'buyer_email_body'); // Register the buyer email body setting

    // Add settings section
    add_settings_section(
        'gift_card_settings_section',
        __('Custom Settings', 'textdomain'),
        'gift_card_settings_section_callback',
        'gift-card-settings'
    );

    // Add settings fields
    add_settings_field(
        'gift_card_custom_css',
        __('Custom CSS', 'textdomain'),
        'gift_card_custom_css_callback',
        'gift-card-settings',
        'gift_card_settings_section'
    );

    add_settings_field(
        'paypal_client_id',
        __('PayPal Client ID', 'textdomain'),
        'paypal_client_id_callback',
        'gift-card-settings',
        'gift_card_settings_section'
    );

    add_settings_field(
        'recipient_email_body',
        __('Recipient Email Body', 'textdomain'),
        'recipient_email_body_callback',
        'gift-card-settings',
        'gift_card_settings_section'
    );

    add_settings_field(
        'buyer_email_body',
        __('Buyer Email Body', 'textdomain'),
        'buyer_email_body_callback',
        'gift-card-settings',
        'gift_card_settings_section'
    );
}

function gift_card_settings_section_callback() {
    echo '<p>Add this Short Code to display Gift Card Form <b>[gift_card_form]</b></p>';
    echo '<p>' . __('Add your custom settings for the Gift Card plugin here.', 'textdomain') . '</p>';
}

function gift_card_custom_css_callback() {
    $css = get_option('gift_card_custom_css', '');
    echo '<textarea id="gift_card_custom_css" name="gift_card_custom_css" rows="10" cols="50" class="large-text code">' . esc_textarea($css) . '</textarea>';
}

function paypal_client_id_callback() {
    $paypal_client_id = get_option('paypal_client_id', '');
    echo '<input type="text" id="paypal_client_id" name="paypal_client_id" value="' . esc_attr($paypal_client_id) . '" class="regular-text">';
}

function recipient_email_body_callback() {
    $recipient_email_body = get_option('recipient_email_body', '');
    echo '<h4><b>Use these dyncamic short codes is email: </b>{your_name}, {recipient_name}, {voucher_value}, {personal_message}, {coupon_code}, {expiry_date}, {recipient_email},{img_src}</h4>';
    echo '<textarea id="recipient_email_body" name="recipient_email_body" rows="10" cols="50" class="large-text code">' . esc_textarea($recipient_email_body) . '</textarea>';
}

function buyer_email_body_callback() {
    $buyer_email_body = get_option('buyer_email_body', '');
    echo '<h4><b>Use these dyncamic short codes is email: </b>{your_name}, {recipient_name}, {voucher_value}, {personal_message}, {coupon_code}, {expiry_date}, {recipient_email}</h4>';
    echo '<textarea id="buyer_email_body" name="buyer_email_body" rows="10" cols="50" class="large-text code">' . esc_textarea($buyer_email_body) . '</textarea>';
}

add_action('wp_enqueue_scripts', 'enqueue_custom_gift_card_css');

function enqueue_custom_gift_card_css() {
    $custom_css = get_option('gift_card_custom_css', '');
    if (!empty($custom_css)) {
        wp_add_inline_style('gift-card-styles', $custom_css);
    }
}




