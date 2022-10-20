<?php

/**
 * Created by PhpStorm.
 * User: Surfer
 * Date: 17/06/19
 * Time: 2:17 PM
 */
class WCUC_Collections
{
    public function __construct(){
        add_action('wp_ajax_wcuc_action_add_collection', array($this, 'ajax_add_collection'));
        add_action('wp_ajax_wcuc_action_delete_collection', array($this, 'ajax_delete_collection'));
        add_action('wp_ajax_wcuc_action_update_collection', array($this, 'ajax_update_collection'));
    }

    private function generate_unique_key($keys = null){
        $length = 6;
        $key = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $length);
        //$code = mt_rand(100000, 999999);

        if($keys){
            $found_key = array_search($key, $keys);
            if($found_key){
                $this->generate_unique_key($keys);
            }else{
                return $key;
            }
        }else{
            return $key;
        }
    }

    public function get_user_id(){

        if(is_user_logged_in()){
            $user = wp_get_current_user();
            return $user->ID;
        }else{
            return false;
        }

    }

    public function get_all_collections(){

        if(!$this->get_user_id()) return false;
        $collections = get_user_meta($this->get_user_id(), 'wcuc_user_collections', true);

        return $collections;
    }

    public function get_collection($collection_key){

        if(!$this->get_user_id()) return false;
        $collections = get_user_meta($this->get_user_id(), 'wcuc_user_collections');

        if($collections) {
            return $collections[$collection_key];
        }else{
            return false;
        }
    }

    protected function create_collection($name){

        $collections = get_user_meta($this->get_user_id(), 'wcuc_user_collections', true);
        if(!$collections){
            $collections = array();
            $collections[$this->generate_unique_key()] =  $name;

            //Save first collection
            update_user_meta($this->get_user_id(), 'wcuc_user_collections', $collections);
            return $collections = get_user_meta($this->get_user_id(), 'wcuc_user_collections', true);
        }else{
            $collections[$this->generate_unique_key(array_keys($collections))] = $name;

            //Save new collection
            update_user_meta($this->get_user_id(), 'wcuc_user_collections', $collections);
            return $collections = get_user_meta($this->get_user_id(), 'wcuc_user_collections', true);
        }
    }

    protected function update_collection($key, $value){

        $collections = get_user_meta($this->get_user_id(), 'wcuc_user_collections', true);
        $collections[$key] = $value;

        update_user_meta($this->get_user_id(), 'wcuc_user_collections', $collections);
        return $collections = get_user_meta($this->get_user_id(), 'wcuc_user_collections', true);
    }

    protected function delete_collection($key){
        $collections = get_user_meta($this->get_user_id(), 'wcuc_user_collections', true);

        if(isset($collections[$key])) {
            unset($collections[$key]);
            update_user_meta($this->get_user_id(), 'wcuc_user_collections', $collections);
            delete_user_meta($this->get_user_id(), 'wcuc_user_collection_products_'.$key);
        }
    }

    protected function check_collection_exists($key){
        $collections = get_user_meta($this->get_user_id(), 'wcuc_user_collections', true);

        if(isset($collections[$key])) {
            return true;
        }else{
            return false;
        }
    }
    protected function check_collection_by_name($name){
        $collections = get_user_meta($this->get_user_id(), 'wcuc_user_collections', true);

        if(array_search($name, $collections)) {
            return true;
        }else{
            return false;
        }
    }

    public function available_collection($prod = ''){
        global $product;
        if($product){
            $prod = $product->get_id();
        }
        $collections = $this->get_all_collections();
        $available_collections = array();
        if($collections){
            foreach ($collections as $key => $name){
                $collection_products = $this->get_collection_products($key);
                if($collection_products && array_search($prod, $collection_products)){
                    continue;
                }
                $available_collections[$key] = $name;
            }
        }
        return $available_collections;
    }

    public function get_collection_products($collection_key){
        $collection_products = get_user_meta($this->get_user_id(), 'wcuc_user_collection_products_'.$collection_key, true);
        if($collection_products) {
            return $collection_products;
        }else{
            update_user_meta($this->get_user_id(), 'wcuc_user_collection_products_'.$collection_key, array());
            return false;
        }
    }

    public function ajax_add_collection(){
        $name = $_POST['collection_name'];
        $prod = $_POST['product'];
        $flag = $this->check_collection_by_name($name);
        if(!$flag) {
            $collections = $this->create_collection($name);
            //$available_collections = $this->available_collection($prod);
            $collection_html = '';
            if($collections) {
                $collection_html .= '<option value="">'.__('Choose Collection', 'wcuc').'</option>';
                foreach ($collections as $key => $name) {
                    $collection_html .= '<option value="' . $key . '">' . $name . '</option>';
                }
            }
            echo json_encode(array('error'=>false, 'message'=>__('Collection added successfully.','wcuc'), 'collection_html'=>$collection_html));
        }else{
            echo json_encode(array('error'=>true, 'message'=>__('Collection name already exists.','wcuc')));
        }
        die();
    }

    public function ajax_delete_collection(){
        $key = $_POST['collection_key'];
        $flag = $this->check_collection_exists($key);
        if($flag) {
            $this->delete_collection($key);
            echo json_encode(array('error'=>false, 'message'=>__('Collection deleted successfully.','wcuc')));
        }else{
            echo json_encode(array('error'=>true, 'message'=>__('Error while deleting the collection.','wcuc')));
        }
        die();
    }

    public function ajax_update_collection(){
        $key = $_POST['collection_key'];
        $flag = $this->check_collection_exists($key);
        if($flag) {
            $this->update_collection($key);
            echo json_encode(array('error'=>false, 'message'=>__('Collection updated successfully.','wcuc')));
        }else{
            echo json_encode(array('error'=>true, 'message'=>__('Error while updating the collection name.','wcuc')));
        }
        die();
    }

}

new WCUC_Collections();