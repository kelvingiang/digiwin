<?php

class Metabox_Main
{

    private $_controller_name = 'main_controller_options';
    private $_controller_options = array();

    public function __construct()
    {
        $defaultOption = array(
            'metabox_source' => TRUE,
            'metabox_view' => TRUE,
            'metabox_web' => TRUE,
            'metabox_download' => TRUE,
            'metabox_language' => TRUE,
            'metabox_industries' => FALSE,
            'metabox_service' => FALSE,
            'metabox_solution' => FALSE,
            'metabox_active' => FALSE,
            'metabox_seo' => FALSE,
            'metabox_order' => TRUE,
            'metabox_home' => TRUE,
            'metabox_link' => TRUE,
            'metabox_sidebar' => TRUE,
        );

        $this->_controller_options = get_option($this->_controller_name, $defaultOption);
        $this->metabox_source();
        $this->metabox_view();
        $this->metabox_web();
        $this->metabox_download();
        $this->metabox_language();
        $this->metabox_industries();
        $this->metabox_service();
        $this->metabox_solution();
        $this->metabox_active();
        $this->metabox_home();
        $this->metabox_order();
        $this->metabox_seo();
        $this->metabox_link();
        $this->metabox_sidebar();
        add_action('admin_init', array($this, 'do_output_buffer'));
    }


     public function metabox_source()
    {
        if ($this->_controller_options['metabox_source']) {
            require_once(DIR_METABOX . 'metabox-source.php');
            new Metabox_source();
        }
    }


    public function metabox_view()
    {
        if ($this->_controller_options['metabox_view']) {
            require_once(DIR_METABOX . 'metabox-view.php');
            new Metabox_View();
        }
    }


    public function metabox_link()
    {
        if ($this->_controller_options['metabox_link']) {
            require_once(DIR_METABOX . 'metabox-link.php');
            new Metabox_Link();
        }
    }

    public function metabox_web()
    {
        if ($this->_controller_options['metabox_web']) {
            require_once(DIR_METABOX . 'metabox-web.php');
            new Metabox_Web_FreeBook();
        }
    }

    public function metabox_language()
    {
        if ($this->_controller_options['metabox_language']) {
            require_once(DIR_METABOX . 'metabox-language.php');
            new metabox_language();
        }
    }

    public function metabox_download()
    {
        if ($this->_controller_options['metabox_download']) {
            require_once(DIR_METABOX . 'metabox-downloads.php');
            new Metabox_Download();
        }
    }

    public function metabox_industries()
    {
        if ($this->_controller_options['metabox_industries']) {
            require_once(DIR_METABOX . 'metabox-industries.php');
            new Metabox_Industries();
        }
    }

    public function metabox_service()
    {
        if ($this->_controller_options['metabox_service']) {
            require_once(DIR_METABOX . 'metabox-service.php');
            new Metabox_Service();
        }
    }

    public function metabox_solution()
    {
        if ($this->_controller_options['metabox_solution']) {
            require_once(DIR_METABOX . 'metabox-solution.php');
            new Metabox_Solution();
        }
    }

    public function metabox_active()
    {
        if ($this->_controller_options['metabox_active'] == true) {
            require_once(DIR_METABOX . 'metabox-active.php');
            new Metabox_Active();
        }
    }


    public function metabox_home()
    {
        if ($this->_controller_options['metabox_home'] == true) {
            require_once(DIR_METABOX . 'metabox-home.php');
            new Metabox_Home();
        }
    }

    public function metabox_seo()
    {
        if ($this->_controller_options['metabox_seo'] == true) {
            require_once(DIR_METABOX . 'metabox-seo.php');
            new Metabox_Seo();
        }
    }

    public function metabox_order()
    {
        if ($this->_controller_options['metabox_order'] == true) {
            require_once(DIR_METABOX . 'metabox-order.php');
            new Metabox_Order();
        }
    }


    public function metabox_sidebar()
    {
        if ($this->_controller_options['metabox_sidebar'] == true) {
            require_once(DIR_METABOX . 'metabox-sidebar.php');
            new Metabox_SideBar();
        }
    }

    //=== FUNCTION NAY GIAI QUYET CHUYEN TRANG BI LOI 
    public function do_output_buffer()
    {
        ob_start();
    }
}
