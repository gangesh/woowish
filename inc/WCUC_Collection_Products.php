<?php

/**
 * Created by PhpStorm.
 * User: Surfer
 * Date: 17/06/19
 * Time: 2:18 PM
 */
class WCUC_Collection_Products extends WCUC_Collections
{
    public function __construct()
    {
        //add_action('wp_ajax_get_collection_products', array($this, 'get_collection_products'));
        add_action('wp_ajax_wcuc_action_add_collection_product', array($this, 'ajax_add_product'));
        add_action('wp_ajax_wcuc_action_delete_collection_product', array($this, 'ajax_delete_product'));
        add_action('wp_ajax_wcuc_action_delete_all_collection_products', array($this, 'clear_collection_products'));

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

    public function add_product($collection_key, $pid)
    {
        if ($this->check_collection_exists($collection_key)) {
            $collection = $this->get_collection_products($collection_key);
            if (!array_search($pid, $collection)) {
                $collection[$pid] = $pid;
                update_user_meta($this->get_user_id(), 'wcuc_user_collection_products_' . $collection_key, $collection);
                return true;
            } else {
                return false;
            }
        }

    }

    public function delete_product($collection_key, $pid)
    {

        if ($this->check_collection_exists($collection_key)) {
            $collection = $this->get_collection_products($collection_key);
            $product_key = array_search($pid, $collection);

            if ($product_key) {
                unset($collection[$product_key]);
                update_user_meta($this->get_user_id(), 'wcuc_user_collection_products_' . $collection_key, $collection);
                return true;
            } else {
                return false;
            }
        }

    }

    public function ajax_add_product()
    {

        $collection_key = $_POST['key'];
        $pid = $_POST['product'];

        $flag = $this->add_product($collection_key, $pid);

        $col_class = new WCUC_front_display();
        $products = $col_class->get_collection_products($collection_key);
        if (!empty($products)) {
            $count_collection = count($products);
        } else {
            $count_collection = 0;
        }
        $product = wc_get_product($pid);
        $image_array = wp_get_attachment_image_src(get_post_thumbnail_id($pid), 'thumbnail');
        $image = $image_array[0];
        if (!$image) {
            $image = woocommerce_placeholder_img_src();
        }
        $permalink = get_permalink($pid);
        $product_name = $product->get_name();

        if ($flag) {
            echo json_encode(array('error' => false, 'product_count' => $count_collection, 'link' => $permalink, 'pro_name' => $product_name, 'pro_image' => $image, 'message' => __('Product added successfully.', 'wcuc')));
        } else {
            echo json_encode(array('error' => true, 'message' => __('Product already in Collection.', 'wcuc')));
        }
        die();
    }
    public function ajax_delete_product()
    {

        $collection_key = $_POST['key'];
        $pid = $_POST['product'];

        $flag = $this->delete_product($collection_key, $pid);

        $col_class = new WCUC_front_display();
        $products = $col_class->get_collection_products($collection_key);
        if (!empty($products)) {
            $count_collection = count($products);
        } else {
            $count_collection = 0;
        }

        if ($flag) {
            echo json_encode(array('error' => false, 'product_count' => $count_collection, 'message' => __('Product deleting successfully.', 'wcuc')));
        } else {
            echo json_encode(array('error' => true, 'message' => __('Error deleting product.', 'wcuc')));
        }
        die();
    }

    public function clear_collection_products($collection_key)
    {

        $collection_key = $_POST['key'];
        $flag = update_user_meta($this->get_user_id(), 'wcuc_user_collection_products_' . $collection_key, array());
        $count_collection = 0;

        if ($flag) {
            echo json_encode(array('error' => false, 'product_count' => $count_collection, 'message' => __('All Products deleted successfully.', 'wcuc')));
        } else {
            echo json_encode(array('error' => true, 'message' => __('Error deleting products.', 'wcuc')));
        }
        die();

    }

}

new WCUC_Collection_Products();