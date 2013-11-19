<?php
/**
 * navigation plugin which generates a better configurable navigation with endless children navigations
 *
 * @author Ahmet Topal + Timothy Su
 * @link http://ahmet-topal.com + http://timofeo.com
 * @license http://opensource.org/licenses/MIT
 */

class AT_Helper
{
public function __construct()
    {
        $base_path = dirname(dirname(__FILE__));
        $requestpath = $_SERVER['REQUEST_URI'];
        $this->path  = $base_path;
        $this->request = $requestpath;
        $this->path = $this->path . '/content/';
    }

    public function before_404_load_content(&$file)
    {
        $this->yes = yes;
    }
    
    public function config_loaded(&$settings)
    {
        $this->settings = $settings;
        $this->oldtitle = $settings['site_title'];
        if (isset($settings['base_url']) && stripos($settings['base_url'], "http") === FALSE) {
            $this->offset = strlen($settings['base_url']);
        }
        else {
            $this->offset = 0;
        }
        if ($this->yes == true) {
            $this->coursename = '';
            $settings['site_title'] = $this->oldtitle;
        }
        else {
            $this->offset2 = 0;
            if ($this->request[strlen($this->request) - 1] == '/') {
                $this->request = substr($this->request, 1 + $this->offset, stripos($this->request, '/', 1 + $this->offset) - $this->offset - 1);
            }
            else if (substr_count($this->request, '/') < 2) {
                $this->request = substr($this->request, 1 + $this->offset, strlen($this->request) - $this->offset - 1);
            }
            else {
                $this->request = substr($this->request, 1 + $this->offset, stripos($this->request, '/', 1 + $this->offset) - $this->offset - 1);
            }
            //echo $this->request;
            $this->coursename = $this->request;
            $this->request = str_replace('_', ' ', $this->request);
            $settings['site_title'] = $this->request;
            if (trim($settings['site_title']) == '') {
                $settings['site_title'] = $this->oldtitle;
                $this->coursename = '';
            }
        }
    }

    public function file_meta(&$meta) {
        $meta['coursename'] = $this->coursename;
    }
}
?>