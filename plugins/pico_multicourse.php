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
            $settings['site_title'] = $this->oldtitle;
        }
        else {
            $this->offset2 = 0;
            if ($this->request[strlen($this->request) - 1] == '/') {
                $this->request = substr($this->request, 1 + $this->offset, stripos($this->request, '/', 1 + $this->offset) - $this->offset - 1);
            }
            else {
                $this->request = substr($this->request, 1 + $this->offset, strlen($this->request) - $this->offset - 1);
            }
            echo $this->request;
            $this->request = str_replace('_', ' ', $this->request);
            $settings['site_title'] = $this->request;
            if (trim($settings['site_title']) == '') {
                $settings['site_title'] = $this->oldtitle;
            }
        }
    }

    public function before_render(&$twig_vars, &$twig)
    {
        $twig_vars['course_navigation'] = $this->build_course_navigation($this->listcourses());
    }
	
    private function listcourses() {
        $results = scandir($this->path);
        $n = 0;

        foreach ($results as $result) {
            if ($result === '.' or $result === '..') continue;

            if (is_dir($this->path . '/' . $result)) {
                $courselisting[$n] = $result;
                $n++;
            }
        }

        /*for($i = 0; $i < count($courselisting); $i++) {
            $courselisting[i] = str_replace('_', ' ', $courselisting[i]);
        }*/

        return $courselisting;
    }

    private function build_course_navigation($navigation) {
        $list = '<div id="noquiz">Course Listing:</div><ul id="at_navigation" class="nav"><ul id="nav2">';
        for ($i = 0; $i < count($navigation); $i++) {
            $withspaces = str_replace('_', ' ', $navigation[$i]);
            $list = $list . '<li class=""><a href="' . $this->settings['base_url'] . '/' . $navigation[$i] . '" class="" title="' . $withspaces . '">' . $withspaces . '</a></li>';
        }
        $list = $list . '</ul></ul>';
        
        return $list;
    }
}