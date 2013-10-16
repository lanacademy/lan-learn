<?php
/**
 * A plugin that uses flowplayer to embed videos referenced in markdown
 *
 * @author Ben Overholts
 * @link http://www.benoverholts.com/
 * @license http://opensource.org/licenses/MIT
 */
 
class Pico_Flowplayer {

	public function __construct() {
		$plugin_path = dirname(__FILE__);
		$this->path = $plugin_path;
	}

	public function config_loaded(&$settings) {
		$this->theme = $settings['theme'];
	}

	public function file_meta(&$meta)
	{
		$this->subject = $meta['subject'];
	}

	public function before_load_content(&$file) {

		if (file_exists($file)) {
			$this->lines = explode("\n", file_get_contents($file));
		}
	}

	public function content_parsed(&$content)
	{
		$videotitle = "";
		$content = "";
		for($i = 0; $i < count($this->lines); ++$i) {
			if (preg_match("/^!!/", $this->lines[$i])) {
    			$videotitle = $this->lines[$i];
    			$this->videopath = str_replace(" ", "_", strtolower($this->subject));
				$this->videopath = "/content/" . $this->videopath . "/media/";
				$videotitle = str_replace("!!", "", $videotitle);
				$this->videopath = $this->videopath . $videotitle;


				//$content = $content . $_SERVER['DOCUMENT_ROOT'] . $this->videopath;
				$content = $content .  '<div class="flowplayer">
   											<video>
      											<source type="video/mp4" src="' . $config['base_url'] . $this->videopath . '">
   											</video>
										</div>';

			} else {
				$content = $content . $this->lines[$i] . "<br>";
			}
		}
	}
}