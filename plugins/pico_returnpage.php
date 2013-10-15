<?php
/**
 * Adds twig variable 'current_url'
 *
 * @author Timothy Su
 * @link http://www.timofeo.com/
 * @license http://opensource.org/licenses/MIT
 */
 
class Pico_ReturnPage {
	public function before_render(&$twig_vars, &$twig)
	{
		$twig_vars['current_url'] = $_SERVER["REQUEST_URI"];
	}
}
?>