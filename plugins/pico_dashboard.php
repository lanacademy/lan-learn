<?php
/**
 * Populates 'my_keywords' twig variable to contain all the keywords
 * found on the current page, seperated by delimiter ','
 *
 * @author Timothy Su
 * @link http://www.timofeo.com/
 * @license http://opensource.org/licenses/MIT
 */

class Pico_Dashboard {

	public function plugins_loaded()
	{
		
	}
	
	public function request_url(&$url)
	{
		
	}
	
	public function before_load_content(&$file)
	{
		
	}
	
	public function after_load_content(&$file, &$content)
	{
		
	}
	
	public function before_404_load_content(&$file)
	{
		
	}
	
	public function after_404_load_content(&$file, &$content)
	{
		
	}
	
	public function config_loaded(&$settings)
	{
		
	}
	
	public function before_read_file_meta(&$headers)
	{
		
	}
	
	
	public function content_parsed(&$content)
	{
		
	}

    public function file_meta(&$meta)
    {
        $this->coursename = $meta['title'];
        $this->layout = $meta['layout'];
    }
	
	public function get_pages(&$pages, &$current_page, &$prev_page, &$next_page)
	{
		
	}
	
	public function before_twig_register()
	{
		
	}
	
	public function before_render(&$twig_vars, &$twig)
	{
        if ($this->layout = 'course') {
            $twig_vars['current_page']['title'] = $this->coursename;
            $this->authed = $twig_vars['authed'];
		    $twig_vars['dashboard'] = $this->buildDash();
        }
	}
	
	public function after_render(&$output)
	{
		
	}
	
    private function buildDash() {
        if ($this->authed) {
            $dashCode = '<h1>' . $this->coursename . '</h1>';
        }
        else {
            $dashCode = '<div class="col-md-12"><div class="well"><h2>Please login to view course dashboard!</h2></div></div>';
        }

        return $dashCode;
    }
}

?>