<?php
/**
 * Builds the course dashboard and populates it with cool
 * graphs and data and stuff.
 *
 * @author Ben Overholts
 * @link http://www.benoverholts.com/
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
        if ($this->layout == 'course') {
            $twig_vars['current_page']['title'] = $this->coursename;
		    $twig_vars['dashboard'] = $this->buildDash();
        }
	}
	
	public function after_render(&$output)
	{
		
	}
	
    private function buildDash() {
    	session_start();

    	// only bother doing this function for logged in users
        if (isset($_SESSION['authed']) && $_SESSION['authed']) {

        	// get the last page visited
        	// TODO: This can be made more efficient by reading backwards from the end of the file
        	$plugin_path = dirname(dirname(__FILE__));
        	$user = $_SESSION['username'];
        	$last = NULL;
        	if(($handle = fopen($plugin_path . '/log/' . $user . '.log', "r")) !== FALSE) {
        		while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        			if(strcmp($data[0], '[HIT]') == 0 && preg_match('/^\/Example_Course\/(.*)$/', $data[2]) === 1) {
        				$last = $data[2];
        			}
        		}
        	}
        	
        	if (isset($settings['base_url'])) {
            	$this->pathheader = $settings['base_url'];
        	}

        	if($last == NULL){
        		$last_page = "Visit a page in this course to see your bookmark here";
        	} else {
        		if(strcmp(substr($last, -1),"/") == 0) {
        			preg_match('/.*\.(\w*)\/\z/', $last, $matches);
        			$chapter = $matches[1];
        			$chapter = '<a href=' . $this->pathheader . $last . '>' . $chapter . '</a>';
        			$last_page = 'You were last at the index of ' . $chapter;
        		} else {
        			preg_match('/.*\.(\w*)\/\d+\.(\w*)\z/', $last, $matches);
        			$page = str_replace("_", " ", $matches[2]);
        			$chapter = str_replace("_", " ", $matches[1]);
        			$page = '<a href=' . $this->pathheader . $last . '>' . $page . '</a>';
        			$last_page = 'You were last at ' . $page . ' in ' . $chapter;
        		}
        	}

        	// build dashboard html
        	$dashCode = '<div class="row">
				<div class="col-md-12">' .
					'<h3>Welcome to ' . $this->coursename . ', ' . $_SESSION['username'] . '</h3>' .
				'</div>
				<div class="col-md-12">
					<div class="well">
						<h4>' . $last_page . '</h4>
						<h4>Hours spent per course & Number of tests taken/Average test score</h4>
						<canvas id="myChart" width="300" height="400"></canvas><canvas id="myChart2" width="300" height="400"></canvas>
					</div>
				</div>
			</div>
			</div>
			<script type="text/javascript">
			var data = {
			    labels : ["Biology", "Business", "Finance", "Physics", "Writing"],
			    datasets : [
			        {
			            fillColor : "rgba(151,187,205,0.5)",
			            strokeColor : "rgba(151,187,205,1)",
			            pointColor : "rgba(151,187,205,1)",
			            pointStrokeColor : "#fff",
			            data : [35,55,120,34,88]
			        }
			    ]
			}

			var data2 = {
			    labels : ["September", "October", "November"],
			    datasets : [
			        {
			            fillColor : "rgba(220,220,220,0.5)",
			            strokeColor : "rgba(220,220,220,1)",
			            data : [15, 33, 8]
			        },
			        {
			            fillColor : "rgba(151,187,205,0.5)",
			            strokeColor : "rgba(151,187,205,1)",
			            data : [80, 100, 60]
			        }
			    ]
			}

			var ctx = document.getElementById("myChart").getContext("2d");
			var myNewChart = new Chart(ctx).Line(data);
			var ctx2 = document.getElementById("myChart2").getContext("2d");
			var myNewChart2 = new Chart(ctx2).Bar(data2);</script>';


        }
        else {
            $dashCode = '<div class="col-md-12"><div class="well"><h2>Please login to view course dashboard!</h2></div></div>';
        }

        session_write_close();

        return $dashCode;
        
    }
}

?>