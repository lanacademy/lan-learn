<?php
/**
 * Tracks all page hits for logged in users
 *
 * @author Timothy Su
 * @link http://www.timofeo.com/
 * @license http://opensource.org/licenses/MIT
 */
 
class Pico_Tracking {
    
    public function __construct()
    {
        $plugin_path = dirname(dirname(__FILE__));
        $this->path  = $plugin_path;
    }
    
    public function config_loaded(&$settings)
    {
        $this->settings = $settings;
        if (isset($settings['tracking'])) {
            $this->tracking = $settings['tracking'];
        }
        else {
            $this->tracking = false;
        }
    }
    
	public function before_render(&$twig_vars, &$twig)
	{
		if ($twig_vars['authed'] && $this->tracking) {
            $page = $_SERVER['REQUEST_URI'];
            if (strpos($page, ".map") !== false) {
                exit;
            }
            if (strpos($page, "/plugins/") !== false) {
                exit;
            }
            if (strpos($page, "/media/") !== false) {
                exit;
            }
            if (strpos($page, "/themes/") !== false) {
                exit;
            }
            if (strpos($page, ".png") !== false) {
                exit;
            }
            if (strpos($page, ".js") !== false) {
                exit;
            }
            if (strpos($page, ".gif") !== false) {
                exit;
            }
            if (strpos($page, ".css") !== false) {
                exit;
            }
            if (strpos($page, ".jpg") !== false) {
                exit;
            }
            if (strpos($page, ".jpeg") !== false) {
                exit;
            }
            $user = $twig_vars['username'];
            $data = "[HIT],";
            $data = $data . date('Y/m/d H:i:s');
            $data = $data . "," . $page . "\n";
            if (file_exists($this->path . '/log/' . $user . '.log')) {
                $data = file_get_contents($this->path . '/log/' . $user . '.log') . $data;
            }
            file_put_contents($this->path . '/log/' . $user . '.log', $data);
        }
	}
}