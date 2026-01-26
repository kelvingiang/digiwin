<?php

class Controller_Main
{

    private $_controller_name = 'main_controller_options';
    private $_controller_options = array();

    public function __construct()
    {

        $defaultOption = array(

            'controller_setting' => false,
            'controller_case_study' => true,
            'controller_resources' => true,
            'controller_active' => true,
            'controller_industries' => true,
            'controller_solutions' => true,
            'controller_services' => true,
            'controller_information' => true,
            'controller_slider' => true,
            'controller_join_us' => true,
            'controller_logo' => true,
            'controller_popup' => true,
        );

        $this->_controller_options = get_option($this->_controller_name, $defaultOption);

        $this->page_popup();
        $this->page_information();
        $this->page_setting();
        $this->page_logo();

        $this->post_slider();
        $this->post_solutions();
        $this->post_services();
        $this->post_industries();
        $this->post_active();
        $this->post_resources();
        $this->post_cases_studues();
        $this->post_join_us();

        add_action('admin_init', array($this, 'do_output_buffer'));
    }

    public function page_popup()
    {
        if ($this->_controller_options['controller_popup']) {
            require_once(DIR_CONTROLLER . 'controller-popup.php');
            new Controller_Popup();
        }
    }

    public function page_logo()
    {
        if ($this->_controller_options['controller_logo']) {
            require_once(DIR_CONTROLLER . 'controller-logo.php');
            new Controller_logo();
        }
    }


    public function page_setting()
    {
        if ($this->_controller_options['controller_setting']) {
            require_once(DIR_CONTROLLER . 'controller-setting.php');
            new Controller_Web_Setting();
        }
    }

    public function page_information()
    {
        if ($this->_controller_options['controller_information']) {
            require_once(DIR_CONTROLLER . 'controller-information.php');
            new Controller_Company_Information();
        }
    }


    public function post_join_us()
    {
        if ($this->_controller_options['controller_join_us'] == true) {
            require_once(DIR_CONTROLLER . 'controller-join-us.php');
            new Controller_Join_Us();
        }
    }



    public function post_cases_studues()
    {
        if ($this->_controller_options['controller_case_study'] == true) {
            require_once(DIR_CONTROLLER . 'controller-case-studies.php');
            new Controller_Case_Studies();
        }
    }


    public function post_resources()
    {
        if ($this->_controller_options['controller_resources'] == true) {
            require_once(DIR_CONTROLLER . 'controller-resources.php');
            new Controller_Resources();
        }
    }

    public function post_active()
    {
        if ($this->_controller_options['controller_active'] == true) {
            require_once(DIR_CONTROLLER . 'controller-active.php');
            new Controller_Active();
        }
    }

    public function post_industries()
    {
        if ($this->_controller_options['controller_industries'] == true) {
            require_once(DIR_CONTROLLER . 'controller-industries.php');
            new Controller_Industries();
        }
    }

    public function post_solutions()
    {
        if ($this->_controller_options['controller_solutions'] == true) {
            require_once(DIR_CONTROLLER . 'controller-solutions.php');
            new Controller_Solutions();
        }
    }

    public function post_services()
    {
        if ($this->_controller_options['controller_services'] == true) {
            require_once(DIR_CONTROLLER . 'controller-services.php');
            new Controller_Services();
        }
    }

    public function post_slider()
    {
        if ($this->_controller_options['controller_slider'] == true) {
            require_once(DIR_CONTROLLER . 'controller-slider.php');
            new Controller_Slider();
        }
    }

    //=== FUNCTION NAY GIAI QUYET CHUYEN TRANG BI LOI 
    public function do_output_buffer()
    {
        ob_start();
    }
}
