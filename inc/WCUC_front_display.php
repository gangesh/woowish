<?php

/**
 * Created by PhpStorm.
 * User: Surfer
 * Date: 17/06/19
 * Time: 4:43 PM
 */
class WCUC_front_display extends WCUC_Collections
{
    public function __construct()
    {
        add_action('woocommerce_single_product_summary', array($this, 'add_to_collection'), 40);
        add_shortcode('wcuc_collections', array($this, 'display_collections_shortcode'));
        add_shortcode('wcuc_collections_loop', array($this, 'display_collections_loop_shortcode'));
        add_filter('body_class', array($this, 'add_collections_body_class'), 100, 1);

        if (isset($_REQUEST['collection'])) {
            add_filter('the_title', array($this, 'remove_page_title'), 100, 2);
        }

        add_action('wp_ajax_wcuc_action_send_share_email', array($this, 'send_collection_share_email'));
        add_action('wp_ajax_wcuc_action_send_inquire_email', array($this, 'send_collection_inquire_email'));

        add_action('wp_ajax_wcuc_action_check_collection', array($this, 'check_product_in_collection'));

        //parent::__construct();

    }

    public function add_collections_body_class($classes)
    {
        $collection_options = get_option('collection_options');
        if ($collection_options) {
            if (is_page($collection_options['page_id'])) {
                return array_merge($classes, array('wcuc-collections-page'));
            }
        }
        return $classes;
    }

    public function add_to_collection()
    {
        global $product;
        $options = get_option('collection_options');
        /* $html = '<div id="wcuc-container">
        <div id="wcuc-ajax-message"></div>
        <div class="wcuc-checkbox">
        <!-- input type="checkbox" value="'.$product->get_id().'" id="wcuc-add-to-collection-check" / -->
        <label for="wcuc-add-to-collection-check">'.__('Add To Collection To Share','wcuc').'</label>
        </div>
        <input type="hidden" value="'.$product->get_id().'" id="wcuc-add-to-collection-product" />
        <div class="wcuc-collection-container">'.$this->display_collections_dropdown().'</div>
        <div class="wcuc-collection-container">
        <a href="#" class="wcuc-show-hide-add-new-collection-field">'.__('Add New Collection', 'wcuc').'</a>
        <a href="'.get_permalink($options['page_id']).'?pid='.$product->get_id(). '" class="wcuc-view-all-collections">'.__('My Collections', 'wcuc').'</a>
        <div class="wcuc-new-collection-field">
        <input type="text" id="wcuc-new-collection" value="" />
        <button class="btn button" id="wcuc-add-new-collection">'.__('Add Collection', 'wcuc').'</button>
        </div>
        </div>
        </div>'; */
        $html = '<div id="wcuc-container">
                 <div id="wcuc-ajax-message"></div>
                 <div class="wcuc-checkbox">
                     <!-- input type="checkbox" value="' . $product->get_id() . '" id="wcuc-add-to-collection-check" / -->

                 </div>
                 <input type="hidden" value="' . $product->get_id() . '" id="wcuc-add-to-collection-product" />
                 <div class="wcuc-collection-container">' . $this->display_collections_dropdown() . '</div>
                 <div class="wcuc-collection-container">


                     <div class="wcuc-new-collection-field">
                         <input type="text" id="wcuc-new-collection" value="" />
                         <button class="btn button" id="wcuc-add-new-collection">' . __('Add Collection', 'wcuc') . '</button>
                     </div>
                 </div>
              </div>';
        echo $html;
    }

    public function check_product_in_collection($key = null, $prod = null)
    {
        global $product;
        $flag = false;

        if (!$key) {
            $key = $_POST['collection_key'];
        }

        if (!$prod) {
            if (isset($_POST['product'])) {
                $prod = $_POST['product'];
            } else {
                $prod = $product->get_id();
            }
        }

        $collection_products = get_user_meta($this->get_user_id(), 'wcuc_user_collection_products_' . $key, true);
        if ($collection_products) {
            $pid = array_search($prod, $collection_products);
            if ($pid) {
                $flag = true;
            }
        }

        if (isset($_POST['collection_key'])) {
            if ($flag) {
                $collection_html = '<label for="wcuc-add-to-collection-check">' . __('<p class="">Product already in this collection.</p>', 'wcuc') . '</label>';
                echo json_encode(array('error' => false, 'collection_html' => __($collection_html, 'wcuc')));
            } else {
                //$collection_html = '<input type="checkbox" value="'.$prod.'" id="wcuc-add-to-collection-check" />
                $collection_html = '<label for="wcuc-add-to-collection-check">' . __('Add To Collection', 'wcuc') . '</label>';
                echo json_encode(array('error' => true, 'collection_html' => __($collection_html, 'wcuc')));
            }
            die();
        }

        return $flag;
    }

    public function available_collection($prod = '')
    {
        global $product;
        $collections = $this->get_all_collections();
        $available_collections = array();
        if ($collections) {
            foreach ($collections as $key => $name) {
                $collection_products = $this->get_collection_products($key);
                if ($collection_products && array_search($product->get_id(), $collection_products)) {
                    continue;
                }
                $available_collections[$key] = $name;
            }
        }
        return $available_collections;
    }

    public function display_collections_dropdown()
    {
        global $product;
        //$available_collections = $this->available_collection();
        $collections = $this->get_all_collections();
        //var_dump($collections);
        $html = '<div class="wcuc-collection-dropdown-container">';

        //  $html .= '<option value="">'.__('Choose Collection', 'wcuc').'</option>';
        if ($collections) {
            foreach ($collections as $key => $name) {
                //  $html .= '<option value="' . $key . '">' . $name . '</option>';
                $html .= ' <input type="hidden" value="' . $key . '" data-col-name="' . $name . '" id="wcuc-add-to-collection-value" />';
            }
        }

        $html .= '<button class="button" id="wcuc-add-to-collection">' . __('Add to Collection', 'wcuc') . '</button>';

        $html .= '</div>';
        return $html;
    }

    public function display_collections_shortcode()
    {
        $options = get_option('collection_options');
        $collections = $this->get_all_collections();
        $html = '<div class="wcuc-collections-page-container">';
        $html = '<div class="wcuc-collections-page-container">';

        if (isset($_GET["pid"])) {
            $bpid = $_GET["pid"];
            $html .= '<a class="back-to-product" href="' . get_permalink($bpid) . '"><< ' . __('Back to Product', 'wcuc') . '</a>';
        }

        $html .= '<div class="accordion" id="accordionExample274" aria-multiselectable="true">';

        foreach ($collections as $key => $name) {

            $html .= '<div class="card z-depth-0"> <div class="card-header" id="headingOne' . $key . '"><div class="wcuc-collection-block"><a href="#collapseOne' . $key . '" class="collapsed" data-toggle="collapse"
          aria-expanded="false" aria-controls="collapseOne' . $key . '">' . $name . '</a><a href="#" class="wcuc-delete-collection" data-delete-collection-id="' . $key . '">X</a></div> </div>';

            $products = $this->get_collection_products($key);

            $html .= ' <div id="collapseOne' . $key . '" class="collapse" aria-labelledby="headingOne' . $key . '"
      data-parent="#accordionExample274">
      <div class="card-body">';
            if ($products) {
                $html .= '<div class="wcuc-collection-title-share-block"><h1 class="main_title">' . $collections[$key] . '</h1> <div class="wcus-share-inquire"><a href="#" data-toggle="modal" data-target="#wcuc-share' . $key . '" class="btn btn-outline-info waves-effect waves-light">Share</a><a href="#" data-toggle="modal" data-target="#wcuc-inquire' . $key . '" class="btn btn-outline-success waves-effect">Inquire</a></div></div>';
                foreach ($products as $pid) {
                    $product = wc_get_product($pid);
                    $image_array = wp_get_attachment_image_src(get_post_thumbnail_id($pid), 'thumbnail');
                    $image = $image_array[0];
                    if (!$image) {
                        $image = woocommerce_placeholder_img_src();
                    }
                    $html .= '<div class="wcuc-collection-single-block">'; //wc_price($product->get_price());
                    $html .= '<div class="wcuc-collection-product-thumb"><a href="#" class="wcuc-delete-product" data-delete-id="' . $pid . '" data-delete-collection-id="' . $key . '">X</a><a href="' . get_permalink($pid) . '"><img src="' . $image . '" alt="' . $product->get_name() . '"></a></div>';
                    $html .= '<div class="wcuc-collection-product-name"><a href="' . get_permalink($pid) . '"><h3>' . $product->get_name() . '</h3></a></div>';
                    $html .= '</div>';
                }
                $html .= '</div>';

            } else {

                $html .= '<div class="wcuc-collection-title-share-block"><h2>' . $collections[$key] . '</h2> </div>';
                $html .= '<p>' . __('No products found in this collection.', 'wcuc') . '</p>';
            }
            $html .= $this->display_share_form($key, $collections[$key]);
            $html .= $this->display_inquire_form($key, $collections[$key]);

            $html .= '
    </div>
  </div>';
        }

        $html .= '</div>';
        //$html .= $this->collection_email_template();
        wp_dequeue_script("divi-custom-script");
        wp_enqueue_style('wcuc-bs-css');
        wp_enqueue_style('wcuc-mdb-css');
        wp_enqueue_script('wcuc-bs-js');
        wp_enqueue_script('wcuc-mdb-js');
        return $html;
    }

    public function display_collections_loop_shortcode()
    {
        // $options = get_option('collection_options');
        // $collections = $this->get_all_collections();
        if(is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $collections = get_user_meta($current_user->ID, 'wcuc_user_collections', true);
        
       
        $html = '<div class="wcuc-collections-page-container">';

        // if (isset($_GET["pid"])) {
        //     $bpid = $_GET["pid"];
        //     $html .= '<a class="back-to-product" href="' . get_permalink($bpid) . '"><< ' . __('Back to Product', 'wcuc') . '</a>';
        // }

        foreach ($collections as $key => $name) {

            $html .= '<div id="mySidenav" class="sidenav"><div class="card z-depth-0">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a> ';

            $products = $this->get_collection_products($key);

            $html .= ' <div id="' . $key . '" class="" aria-labelledby="headingOne' . $key . '"
      data-parent="#accordionExample274">
      <div class="card-body">';

            $html .= '<div class="wcuc-collection-title-share-block">
      <h3 class="main_title">My Collection <span id="wcuc_clear_all_pro" data-collection-key="' . $key . '">Clear All</span></h3>
       <div class="wcus-share-inquire"><a href="#" data-toggle="modal" data-target="#wcuc-share' . $key . '" class="btn btn-outline-info waves-effect waves-light">Share</a>
       <a href="#" data-toggle="modal" data-target="#wcuc-inquire' . $key . '" class="btn btn-outline-success waves-effect">Inquire</a>
       </div></div>';

            if ($products) {

                foreach ($products as $pid) {
                    $product = wc_get_product($pid);
                    $image_array = wp_get_attachment_image_src(get_post_thumbnail_id($pid), 'thumbnail');
                    $image = $image_array[0];
                    if (!$image) {
                        $image = woocommerce_placeholder_img_src();
                    }
                    $html .= '<div class="wcuc-collection-single-block">'; //wc_price($product->get_price());
                    $html .= '<div class="wcuc-collection-product-thumb"><a href="#" class="wcuc-delete-product" data-delete-id="' . $pid . '" data-delete-collection-id="' . $key . '">X</a><a href="' . get_permalink($pid) . '"><img src="' . $image . '" alt="' . $product->get_name() . '"></a></div>';
                    $html .= '<div class="wcuc-collection-product-name"><a href="' . get_permalink($pid) . '"><h3>' . $product->get_name() . '</h3></a></div>';
                    $html .= '</div>';
                }
                $html .= '</div>';
              
                $html .= '<p class="no-product-class" style="display:none">' . __('No products found in this collection.', 'wcuc') . '</p>';
            } else {

                // $html .= '<div class="wcuc-collection-title-share-block"><h2>' . $collections[$key] . '</h2> </div>';
                $html .= '<p class="no-product-class">' . __('No products found in this collection.', 'wcuc') . '</p>';
            }
			
  				$html .= $this->display_share_form($key, $collections[$key]);
                $html .= $this->display_inquire_form($key, $collections[$key]);
            	$html .= '
    </div>
    </div>
    </div>';
        }

        //$html .= $this->collection_email_template();
        wp_dequeue_script("divi-custom-script");
        wp_enqueue_style('wcuc-bs-css');
        wp_enqueue_style('wcuc-mdb-css');
        wp_enqueue_script('wcuc-bs-js');
        wp_enqueue_script('wcuc-mdb-js');
        return $html;
    }
    }

    public function get_collection_products($collection_key)
    {
        $collection_products = get_user_meta($this->get_user_id(), 'wcuc_user_collection_products_' . $collection_key, true);
        if ($collection_products) {
            return $collection_products;
        } else {
            update_user_meta($this->get_user_id(), 'wcuc_user_collection_products_' . $collection_key, array());
            return false;
        }
    }

    protected function display_share_form($key, $name)
    {
        $html = '<div class="modal fade" id="wcuc-share' . $key . '" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="false">
        <div class="modal-dialog modal-notify modal-info" role="document">
          <div class="modal-content">
            <!--Header-->
            <div class="modal-header">
              <p class="heading lead">' . __('Share Your ' . $name, 'wcuc') . '</p>

              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true" class="white-text">×</span>
              </button>
            </div>

            <!--Body-->
           <form id="wcuc-share-form" class="wcuc-needs-validation" novalidate>
            <div class="modal-body">
            ' . do_action('wcuc_before_share_form') . '
              <div class="md-form">
                <input type="email" id="wcuc-share-email" name="wcuc-share-email" class="md-text form-control" value="" required>
                <label for="wcuc-share-email">' . __('Recipient\'s Email', 'wcuc') . '</label>
              </div>
              <div class="md-form">
                <input type="text" id="wcuc-share-name-self" name="wcuc-share-name-self" class="md-text form-control" value="" required>
                <label for="wcuc-share-name-self">' . __('Your Name', 'wcuc') . '</label>
              </div>
              <div class="md-form">
                <input type="email" id="wcuc-share-self-email" name="wcuc-share-self-email" class="md-text form-control" value="" required>
                <label for="wcuc-share-self-email">' . __('Your Email', 'wcuc') . '</label>
              </div>
              <div class="md-form">
                <input type="text" id="wcuc-share-subject" name="wcuc-share-subject" class="md-text form-control" value="" required>
                <label for="wcuc-share-subject">' . __('Subject', 'wcuc') . '</label>
              </div>
              <div class="md-form">
                <textarea type="text" id="wcuc-share-message" name="wcuc-share-message" class="md-textarea form-control" rows="3"></textarea>
                <label for="wcuc-share-message">' . __('Your message', 'wcuc') . '</label>
              </div>
              ' . do_action('wcuc_after_share_form') . '
            </div>
			<div class="container">
              <div class="row">
                <div class="wcuc-email-response col-12"></div>
              </div>
            </div>
            <!--Footer-->
            <div class="modal-footer justify-content-center">
              <input type="hidden" id="wcuc-share-collection" name="wcuc-share-collection" class="md-text form-control" value="' . $key . '">
              <button type="submit" id="submit-share-form" name="submit-share-form" class="btn btn-info waves-effect waves-light">' . __('Send', 'wcuc') . '
                <i class="fa fa-paper-plane ml-1"></i>
              </button>
            </div>
            </form>
          </div>
        </div>
      </div>';
        return $html;
    }

    protected function display_inquire_form($key, $name)
    {
        $html = '<div class="modal fade" id="wcuc-inquire' . $key . '" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="false">
        <div class="modal-dialog modal-notify modal-success" role="document">
          <div class="modal-content">
            <!--Header-->
            <div class="modal-header">
              <p class="heading lead">' . __('Inquire About ' . $name, 'wcuc') . '</p>

              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true" class="white-text">×</span>
              </button>
            </div>

            <!--Body-->
            <form id="wcuc-inquire-form" class="wcuc-needs-validation" novalidate>
            <div class="modal-body">
            ' . do_action('wcuc_before_inquire_form') . '
              <div class="md-form">
                <input type="text" id="wcuc-inquire-full-name" name="wcuc-inquire-full-name" class="md-text form-control" value="" required>
                <label for="wcuc-inquire-full-name">' . __('Full Name', 'wcuc') . '</label>
              </div>
              <div class="md-form">
                <input type="email" id="wcuc-inquire-email" name="wcuc-inquire-email" class="md-text form-control" value="" required>
                <label for="wcuc-inquire-email">' . __('Email Address', 'wcuc') . '</label>
              </div>
              <div class="md-form">
                <input type="text" id="wcuc-inquire-phone" name="wcuc-inquire-phone" class="md-text form-control" value="" required>
                <label for="wcuc-inquire-phone">' . __('Contact Number', 'wcuc') . '</label>
              </div>
              <div class="md-form">
                <textarea type="text" id="wcuc-inquire-message" name="wcuc-inquire-message" class="md-textarea form-control" rows="3" required></textarea>
                <label for="wcuc-inquire-message">' . __('Enquiry', 'wcuc') . '</label>
              </div>
              ' . do_action('wcuc_after_inquire_form') . '
            </div>
			<div class="container">
              <div class="row">
                <div class="wcuc-email-response col-12"></div>
              </div>
            </div>
            <!--Footer-->
            <div class="modal-footer justify-content-center">
              <input type="hidden" id="wcuc-inquire-collection" name="wcuc-inquire-collection" class="md-text form-control" value="' . $key . '">
              <button type="submit" id="submit-inquire-form" name="submit-inquire-form" class="btn btn-success waves-effect waves-light">' . __('Send', 'wcuc') . '
              <i class="fa fa-paper-plane ml-1"></i>
              </button>
            </div>

            </form>
          </div>
        </div>
      </div>';
        return $html;
    }

    public function collection_email_template($body, $collection)
    {
        $collections = $this->get_all_collections();
        $html = null;
        if (isset($collections[$collection])) {
            $uploads = wp_upload_dir();
            $html .= '<table width="100%" border-collapse="collapse">
                        <tbody>
                        <tr>
                            <td style="padding: 30px 5px;">' . wpautop(trim($body)) . '</td>
                        </tr>
                        </tbody>
                  </table>';
            //$collection = $_REQUEST['collection'];
            $products = $this->get_collection_products($collection);
            if ($products) {
                $html .= '<table width="100%" border-collapse="collapse">

                    <tbody>';
                foreach ($products as $pid) {
                    $product = wc_get_product($pid);
                    $image_array = wp_get_attachment_image_src(get_post_thumbnail_id($pid), 'large');
                    $image = $image_array[0];
                    if (!$image) {
                        $image = woocommerce_placeholder_img_src();
                    }

                    $attachment_ids = $product->get_gallery_attachment_ids();
                    $gal_image = "";
                    if ($attachment_ids != "") {
                        foreach ($attachment_ids as $attachment_id) {
                            // Display the image URL
                            $gal_image = $Original_image_url = wp_get_attachment_url($attachment_id);
                            break;
                        }
                    }

                    //$file_path = str_replace( $uploads['baseurl'], $uploads['basedir'], $image );

                    $html .= '<tr style="display:block">
                      <td style="border-width:0px; padding-right:40px"><img src="' . $image . '" width="300" />';

                    if ($gal_image != "") {
                        $html .= '<br/><img src="' . $gal_image . '" width="300" />';
                    }

                    $html .= '</td> <td style="border-width:0px;padding-bottom: 20px; vertical-align: bottom;"><div>PRODUCT DETAILS</div>

                      <div>SKU: ' . $product->get_sku() . '</div>
                      <div>Title: ' . $product->get_name() . '</div>
                      <div>Description: ' . $product->get_description() . '</div>
                      </td>
<br/><br/><br/>
                    </tr><tr><td><hr/></td></tr>';
                }
                $html .= '</tbody>
                  </table>';
            }
        }
        return $html;
    }

    public function send_collection_share_email()
    {

        $share_email = $_POST['wcuc-share-email'];
        $share_self_name = $_POST['wcuc-share-name-self'];
        $share_self_email = $_POST['wcuc-share-self-email'];
        $share_subject = $_POST['wcuc-share-subject'];
        $share_message = $_POST['wcuc-share-message'];
        $share_collection = $_POST['wcuc-share-collection'];

        $body = $this->collection_email_template($share_message, $share_collection);

        //$body = '<table><tr><td></td></tr></table>';

        $multiple_recipients = explode(',', $share_email);
        $subj = $share_subject;

        //$body = '<div>'.trim($share_message).'</div>';

        $headers = array();
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . $share_self_name . ' <' . $share_self_email . '>';
        $headers[] = 'Reply-To: ' . $share_self_name . ' <' . $share_self_email . '>';

        $flag = wp_mail($multiple_recipients, $subj, $body, $headers);
        //var_dump($body);

        $email_success_html = '<div class="alert alert-success" role="alert">
                              <h4 class="alert-heading">' . __('Collection Sent!', 'wcuc') . '</h4>
                              <p>' . __('Your collection  sent successfully to your friend.', 'wcuc') . '</p>
                            </div>';

        $email_error_html = '<div class="alert alert-danger" role="alert">
                              <h4 class="alert-heading">' . __('Sorry!! Error Occurred.', 'wcuc') . '</h4>
                              <p>' . __('Error occurred sending your Collection. Please try resending.', 'wcuc') . '</p>
                            </div>';

        if ($flag) {
            echo json_encode(array('error' => false, 'message' => $email_success_html));
        } else {
            echo json_encode(array('error' => true, 'message' => $email_error_html));
        }

        die();
    }

    public function send_collection_inquire_email()
    {
        $inquire_name = $_POST['wcuc-inquire-full-name'];
        $inquire_email = $_POST['wcuc-inquire-email'];
        $inquire_phone = $_POST['wcuc-inquire-phone'];
        $inquire_message = $_POST['wcuc-inquire-message'];

        $inquire_collection = $_POST['wcuc-inquire-collection'];

        $body = $this->collection_email_template($inquire_message, $inquire_collection);

        //$body = '<table><tr><td></td></tr></table>';
        $options = get_option('collection_options');
        if (isset($options['inquire_admin_email']) && !empty($options['inquire_admin_email'])) {
            $inquire_admin_email = $options['inquire_admin_email'];
        } else {
            $inquire_admin_email = get_option('admin_email');
        }
        $multiple_recipients = $inquire_admin_email;
        $subj = 'Inquiry Form';

        //$body = '<div>'.$share_message.'</div>';

        $headers = array();
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . $inquire_name . ' <' . $inquire_email . '>';
        $headers[] = 'Reply-To: ' . $inquire_name . ' <' . $inquire_email . '>';

        $flag = wp_mail($multiple_recipients, $subj, $body, $headers);

        $email_success_html = '<div class="alert alert-success" role="alert">
                              <h4 class="alert-heading">' . __('Inquiry Sent!', 'wcuc') . '</h4>
                              <p>' . __('Your enquiry sent successfully. We will get back to you soon.', 'wcuc') . '</p>
                             </div>';

        $email_error_html = '<div class="alert alert-danger" role="alert">
                              <h4 class="alert-heading">' . __('Sorry!! Error Occurred.', 'wcuc') . '</h4>
                              <p>' . __('Error occurred sending your Inquiry. Please try resending.', 'wcuc') . '</p>
                              </div>';

        if ($flag) {
            echo json_encode(array('error' => false, 'message' => $email_success_html));
        } else {
            echo json_encode(array('error' => true, 'message' => $email_error_html));
        }
        die();
    }

    public function remove_page_title($title, $id = null)
    {
        return '';
    }

}

new WCUC_front_display();