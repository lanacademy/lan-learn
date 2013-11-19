<?php
/**
 * Enables multiple courses in Pico/LAN-LMS
 *
 * @author Timothy Su
 * @link http://www.timofeo.com/
 * @license http://opensource.org/licenses/MIT
 */
 
class Pico_MultiCourse {

    public function __construct()
    {
        $requestpath = $_SERVER['REQUEST_URI'];
        $this->request = $requestpath;
    }

    public function before_404_load_content(&$file)
    {
        $this->yes = yes;
    }
	
    public function config_loaded(&$settings)
    {
        $this->oldtitle = $settings['site_title'];
        if (isset($settings['base_url']) && stripos($settings['base_url'], "http") === FALSE) {
            $this->offset = strlen($settings['base_url']);
        }
        else {
            $this->offset = 0;
        }
        if ($this->yes == true) {
            $settings['site_title'] = $this->oldtitle;
        }
        else {
            $this->request = substr($this->request, 1 + $this->offset, stripos($this->request, '/', 1 + $this->offset) - $this->offset - 1);
            $this->request = str_replace('_', ' ', $this->request);
            $settings['site_title'] = $this->request;
            if ($settings['site_title'] == ' ') {
                $settings['site_title'] = $this->oldtitle;
            }
        }
    }
	
}