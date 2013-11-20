<?php
/**
 * An attempt to implement common theming.
 *
 * @author Paarth Chadha
 * @link http://www.timofeo.com/
 * @license http://opensource.org/licenses/MIT
 */

class Pico_CommonThemeHeader {

	public function before_render(&$twig_vars, &$twig)
	{
		$twig->addExtension(new Twig_Extension_StringLoader());
		$twig_vars['common_theme_header'] = file_get_contents("themes/common.html");
	}
}

?>