<?php
/**
 * Populates 'my_keywords' twig variable to contain all the keywords
 * found on the current page, seperated by delimiter ','
 *
 * @author Timothy Su
 * @link http://www.timofeo.com/
 * @license http://opensource.org/licenses/MIT
 */
 
class Pico_MetaKeywords {

    public function __construct()
    {
        $base_path = dirname(dirname(__FILE__));
        $requestpath = $_SERVER['REQUEST_URI'];
        $this->path  = $base_path;
        $this->request = $requestpath;
    }
	
    public function config_loaded(&$settings)
    {
        $this->settings = $settings;
        if (isset($settings['base_url']) && stripos($settings['base_url'], "http") === FALSE) {
            $this->offset = strlen($settings['base_url']);
        }
        else {
            $this->offset = 0;
        }
    }

    public function before_load_content(&$file)
    {
        if (file_exists($file)) {
            $this->quizcontent = file_get_contents($file);
        }
    }

	public function file_meta(&$meta)
    {
        $this->type = $meta['layout'];
    }
	
	public function content_parsed(&$content)
	{
        if ($this->type == "content") {
            $this->request = substr($this->request, 1 + $this->offset, stripos($this->request, '/', 1 + $this->offset) - $this->offset);
            $this->path = $this->path . '/content/' . $this->request . 'keywords.xml';
            if (file_exists($this->path)) {
                $this->data = simplexml_load_file($this->path);
                $n = 0;
                $o = 0;
                for($i = 0; $i < count($this->data->title); $i++) {
                if (stripos($this->quizcontent, (String) $this->data->title[$i]) !== FALSE) {
                    $this->alist[$n] = $this->data->title[$i];
                    $this->qlist[$n] = $this->data->text[$i];
                    $n++;
                }
                else {
                    $this->plist[$o] = $this->data->title[$i];
                    $o++;
                }
                }
                if (count($this->alist) == 0) {
                    $this->yes = false;
                }
                else {
                    $this->yes = true;
                }
            }
        }
        else {
            $this->yes = false;
        }
	}

    public function before_render(&$twig_vars, &$twig)
    {
        $twig_vars['my_keywords'] = '';
        if ($this->yes) {
            for($i = 0; $i < count($this->alist); $i++) {
                $twig_vars['my_keywords'] = $twig_vars['my_keywords'] . $this->alist[$i];
                if ($i != count($this->alist) - 1) {
                    $twig_vars['my_keywords'] = $twig_vars['my_keywords'] . ",";
                }
            }
        }
    }
}