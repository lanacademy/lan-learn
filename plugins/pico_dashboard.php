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

    	$wiki_baseurl = "http://en.wikipedia.org/wiki/";

    	$Fcoursename = str_replace(" ", "_", $this->coursename);

    	// only bother doing this function for logged in users
        if (isset($_SESSION['authed']) && $_SESSION['authed']) {

        	// NOTE: theoretically, all of the data collection routines in this function
        	// could be implemented in one iteration over the log file.  This might streamline
        	// the process a bit but its still linear time either way.
        	
        	// get the last page visited and page clicks per chapter
        	// TODO: This can be made more efficient by reading backwards from the end of the file
        	$plugin_path = dirname(dirname(__FILE__));
        	$user = $_SESSION['username'];
        	$last = NULL;
        	$wiki_count = array(); // indexed by chapter
        	if(($handle = fopen($plugin_path . '/log/' . $user . '.log', "r")) !== FALSE) {
        		while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        			if(strcmp($data[0], '[HIT]') == 0 && preg_match('/^\/' . $Fcoursename .'\/(.*)$/', $data[2]) === 1) {
        				$last = $data[2];
        			}
        			if(strcmp($data[0], '[WIK]') == 0) {
        				if($last != NULL) {
        					preg_match('/\/' . $Fcoursename . '\/(\d+)\.(\w*)\/.*/', $last, $matches);
        					$last_chapter = $matches[2];
        					if(isset($wiki_count[$matches[2]])) {
        						$wiki_count[$matches[2]] += 1;
        					} else {
        						$wiki_count[$matches[2]] = 1;
        					}
        				}
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
        			preg_match('/.*\.(\w*)\/\z/', str_replace("-", "_", $last), $matches);
        			$chapter = $matches[1];
        			$chapter = '<a href=' . $this->pathheader . $last . '>' . $chapter . '</a>';
        			$last_page = 'You were last at the index of ' . $chapter;
        		} else {
        			preg_match('/.*\.(\w*)\/\d+\.(\w*)\z/', str_replace("-", "_", $last), $matches);
        			$page = str_replace("_", " ", $matches[2]);
        			$chapter = str_replace("_", " ", $matches[1]);
        			$page = '<a href=' . $this->pathheader . $last . '>' . $page . '</a>';
        			$last_page = 'You were last at ' . $page . ' in ' . $chapter;
        		}
        	}

        	// build quizzes taken / average score graphs
        	$quiz_avg_m = array(); // indexed by months
        	$quiz_avg_c = array(); // indexed by chapter
        	$quiz_count_m = array(); // indexed by months
        	$quiz_count_c = array(); // indexed by chapter
        	if(($handle = fopen($plugin_path . '/log/' . $user . '.log', "r")) !== FALSE) {
        		while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        			if(strcmp($data[0], '[SQZ]') == 0) {
        				$month = date_parse_from_format('Y/m/d H:i:s', $data[1]);
        				$month = $month['month'];
        				preg_match('/(\d+)\/(\d+)/', $data[5], $matches);
        				preg_match('/\w*/', $data[4], $match);
        				$chapter = $match[0];
        				// need to make sure aborted quizzes dont cause division by 0
        				if($matches[2] != 0) { // this might be unsafe, not sure what happens if the regex doesnt match (should never happen though)
	        				if(isset($quiz_count_m[$month])) {
	        					$quiz_count_m[$month] += 1;
	        				} else {
	        					$quiz_count_m[$month] = 1;
	        				}
	        				if(isset($quiz_avg_m[$month])) {
	        					$quiz_avg_m[$month] += $matches[1]/$matches[2];
	        				} else {
	        					$quiz_avg_m[$month] = $matches[1]/$matches[2];
	        				}

		        			if(isset($quiz_count_c[$chapter])) {
		        				$quiz_count_c[$chapter] += 1;
		        			} else {
		        				$quiz_count_c[$chapter] = 1;
		        			}
		        			if(isset($quiz_avg_c[$chapter])) {
		        				$quiz_avg_c[$chapter] += $matches[1]/$matches[2];
		        			} else {
	        					$quiz_avg_c[$chapter] = $matches[1]/$matches[2];
	        				}

		        		}
        			}
        		}
        	}

        	// build time spent by course graph
        	// ! SQZ tagged lines are not read by this
        	// ! Time for last page wont be included if user goes straight from page to course dashboard (needs to refresh)
        	//		(not sure if this can be fixed in pico, may need to wait for phile implementation)

        	// this can probably be cleaned up logically

        	$chapter_last_timestamp = NULL; // indexed by chapter, needed for cumulative summation.
        	$prev_chapter = NULL;
        	$make_set = FALSE;
        	$chapter_time = array(); // indexed by chapter, number of seconds spent on each chapter.
        	if(($handle = fopen($plugin_path . '/log/' . $user . '.log', "r")) !== FALSE) {
        		while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        			if(strcmp($data[0], '[HIT]') == 0) {
        				if(preg_match('/\A\/(\w*)\/\d+\.(\w*).*/', $data[2], $matches) === 1 && strcmp($matches[1], $Fcoursename) == 0) {
        					if($prev_chapter != NULL) {
	        					if($make_set && (strtotime($data[1]) - $chapter_last_timestamp) < 900) {
	        						if(isset($chapter_time[$prev_chapter])) {
	        							//echo 'Added ' . (strtotime($data[1]) - $chapter_last_timestamp) . ' seconds to ' . $prev_chapter . "\n";
										$chapter_time[$prev_chapter] = $chapter_time[$prev_chapter] + (strtotime($data[1]) - $chapter_last_timestamp);
	        						} else {
	        							//echo 'Added ' . (strtotime($data[1]) - $chapter_last_timestamp) . ' seconds to ' . $prev_chapter . " (init)\n";
	        							$chapter_time[$prev_chapter] = (strtotime($data[1]) - $chapter_last_timestamp);
	        						}
	        					} elseif($make_set && (strtotime($data[1]) - $chapter_last_timestamp) > 900) {
	        						if(isset($chapter_time[$prev_chapter])) {
	        							//echo 'Added ' . 60 . ' seconds to ' . $prev_chapter . "\n";
										$chapter_time[$prev_chapter] = $chapter_time[$prev_chapter] + 60;
	        						} else {
	        							//echo 'Added ' . 60 . ' seconds to ' . $prev_chapter . " (init)\n";
	        							$chapter_time[$prev_chapter] = 60;
	        						}
	        					}
	        					$prev_chapter = $matches[2];
	        					$chapter_last_timestamp = strtotime($data[1]);
	        					$make_set = TRUE;
	        				} else {
	        					$prev_chapter = $matches[2];
	        					$chapter_last_timestamp = strtotime($data[1]);
	        					$make_set = TRUE;
	        				}
        				} else {
        					if($prev_chapter != NULL) {
	        					if($make_set && (strtotime($data[1]) - $chapter_last_timestamp) < 900) {
	        						if(isset($chapter_time[$prev_chapter])) {
	        							//echo 'Added ' . (strtotime($data[1]) - $chapter_last_timestamp) . ' seconds to ' . $prev_chapter . "\n";
										$chapter_time[$prev_chapter] = $chapter_time[$prev_chapter] + (strtotime($data[1]) - $chapter_last_timestamp);
	        						} else {
	        							//echo 'Added ' . (strtotime($data[1]) - $chapter_last_timestamp) . ' seconds to ' . $prev_chapter . " (init)\n";
	        							$chapter_time[$prev_chapter] = (strtotime($data[1]) - $chapter_last_timestamp);
	        						}
	        					} elseif($make_set && (strtotime($data[1]) - $chapter_last_timestamp) > 900) {
	        						if(isset($chapter_time[$prev_chapter])) {
	        							//echo 'Added ' . 60 . ' seconds to ' . $prev_chapter . "\n";
										$chapter_time[$prev_chapter] = $chapter_time[$prev_chapter] + 60;
	        						} else {
	        							//echo 'Added ' . 60 . ' seconds to ' . $prev_chapter . " (init)\n";
	        							$chapter_time[$prev_chapter] = 60;
	        						}
	        					}
	        					$make_set = FALSE;
	        				}  else {
	        					$prev_chapter = $matches[2];
	        					$chapter_last_timestamp = strtotime($data[1]);
	        					$make_set = TRUE;
	        				}
        				}
        			}
        		}
        	}

        	// build the last 10 wiki hits
        	$file = file($plugin_path . '/log/' . $user . '.log');
        	$file = array_reverse($file);
        	$wiki_words = array();
        	$count = 0;
        	foreach($file as $line) {
        		$line = explode(",", $line);
        		if(strcmp($line[0], '[WIK]') == 0 && $count < 10) {
        			$wiki_words[$count] = $line[2];
        			$count += 1;
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
						<h4>Hours spent per chapter [graph1] & Number of quizzes taken/Average quiz score by month [graph2] & by chapter [graph3] & wiki clicks by chapter [graph4]</h4>';
						if(count($chapter_time) > 0) {
							$dashCode = $dashCode . '<canvas id="myChart" width="300" height="400"></canvas>';
						} else {
							$dashCode = $dashCode . '<h5>Start working to see statistics here [graph1]</h5>';
						}
						if(count($quiz_avg_m) > 0) {
							$dashCode = $dashCode . '<canvas id="myChart2" width="300" height="400"></canvas>';
						} else {
							$dashCode = $dashCode . '<h5>Start working to see statistics here [graph2]</h5>';
						}
						if(count($quiz_avg_c) > 0) {
							$dashCode = $dashCode . '<canvas id="myChart3" width="300" height="400"></canvas>';
						} else {
							$dashCode = $dashCode . '<h5>Start working to see statistics here [graph3]</h5>';
						}
						if(count($wiki_count) > 0) {
							$dashCode = $dashCode . '<canvas id="myChart4" width="300" height="400"></canvas>';
						} else {
							$dashCode = $dashCode . '<h5>Start working to see statistics here [graph4]</h5>';
						}

						$dashCode = $dashCode . '<h4>Last 10 Keywords Clicked</h4>';
						for($i = 0; $i < 10; $i ++) {
							$dashCode = $dashCode . '<h5><a href=' . $wiki_baseurl . $wiki_words[$i] . ' target="_blank">' . $wiki_words[$i] . '</a></h5>';
						}
					$dashCode = $dashCode . '</div>
				</div>
			</div>
			</div>
			<script type="text/javascript">
			var time_by_chapter = {
			    labels : [';

			    // insert chapter names for time by chapter graph
			    foreach($chapter_time as $key => $value) {
			    	$dashCode = $dashCode . '"' . $key . '", ';
			    }
			    $dashCode = rtrim($dashCode, ", ");


			    $dashCode = $dashCode . '],
			    datasets : [
			        {
			            fillColor : "rgba(151,187,205,0.5)",
			            strokeColor : "rgba(151,187,205,1)",
			            data : [';

			            foreach($chapter_time as $key => $value) {
			            	$dashCode = $dashCode . ($value/360) . ', ';
			            }
			            $dashCode = rtrim($dashCode, ', ');

			            $dashCode = $dashCode . ']
			        }
			    ]
			}

			var wiki_by_chapter = {
			    labels : [';

			    // insert chapter names for time by chapter graph
			    foreach($wiki_count as $key => $value) {
			    	$dashCode = $dashCode . '"' . $key . '", ';
			    }
			    $dashCode = rtrim($dashCode, ", ");


			    $dashCode = $dashCode . '],
			    datasets : [
			        {
			            fillColor : "rgba(151,187,205,0.5)",
			            strokeColor : "rgba(151,187,205,1)",
			            data : [';

			            foreach($wiki_count as $key => $value) {
			            	$dashCode = $dashCode . $value . ', ';
			            }
			            $dashCode = rtrim($dashCode, ', ');

			            $dashCode = $dashCode . ']
			        }
			    ]
			}

			var quiz_by_month = {
			    labels : [';

			   	// insert months for quiz grades graph
			    foreach($quiz_avg_m as $key => $value) {
			    	$dashCode = $dashCode . '"' . date("F", mktime(0, 0, 0, $key, 10)) . '", ';
			    }
			    $dashCode = rtrim($dashCode, ", ");

				$dashCode = $dashCode . '],
			    datasets : [
			        {
			            fillColor : "rgba(220,220,220,0.5)",
			            strokeColor : "rgba(220,220,220,1)",
			            data : [';
			        
			    		// insert data for number of quizzes taken by month
			            foreach($quiz_count_m as $value) {
			            	$dashCode = $dashCode . $value . ', ';
			            }
			            $dashCode = rtrim($dashCode, ", ");

			    $dashCode = $dashCode . ']
			        },
			        {
			            fillColor : "rgba(151,187,205,0.5)",
			            strokeColor : "rgba(151,187,205,1)",
			            data : [';

			            // insert data for averages quiz grades by month
			            foreach($quiz_avg_m as $key => $value) {
			            	$dashCode = $dashCode . (($value / $quiz_count_m[$key])*100) . ', ';
			            }
			            $dashCode = rtrim($dashCode, ", ");

			    $dashCode = $dashCode . ']
			        }
			    ]
			}

			var quiz_by_chapter = {
				labels : [';

			   	// insert months for quiz grades graph
			    foreach($quiz_avg_c as $key => $value) {
			    	$dashCode = $dashCode . '"' . $key . '", ';
			    }
			    $dashCode = rtrim($dashCode, ", ");

				$dashCode = $dashCode . '],
			    datasets : [
			        {
			            fillColor : "rgba(220,220,220,0.5)",
			            strokeColor : "rgba(220,220,220,1)",
			            data : [';
			        
			    		// insert data for number of quizzes taken by month
			            foreach($quiz_count_c as $value) {
			            	$dashCode = $dashCode . $value . ', ';
			            }
			            $dashCode = rtrim($dashCode, ", ");

			    $dashCode = $dashCode . ']
			        },
			        {
			            fillColor : "rgba(151,187,205,0.5)",
			            strokeColor : "rgba(151,187,205,1)",
			            data : [';

			            // insert data for averages quiz grades by month
			            foreach($quiz_avg_c as $key => $value) {
			            	$dashCode = $dashCode . (($value / $quiz_count_c[$key])*100) . ', ';
			            }
			            $dashCode = rtrim($dashCode, ", ");

			    $dashCode = $dashCode . ']
			        }
			    ]
			}

			var ctx = document.getElementById("myChart").getContext("2d");
			var myNewChart = new Chart(ctx).Bar(time_by_chapter);
			var ctx2 = document.getElementById("myChart2").getContext("2d");
			var myNewChart2 = new Chart(ctx2).';
			if(count($quiz_avg_m) > 1) {
				$dashCode = $dashCode . 'Line';
			} else {
				$dashCode = $dashCode . 'Bar';
			}

			$dashCode = $dashCode . '(quiz_by_month);
			var ctx3 = document.getElementById("myChart3").getContext("2d");
			var myNewChart3 = new Chart(ctx3).Bar(quiz_by_chapter);
			var ctx4 = document.getElementById("myChart4").getContext("2d");
			var myNewChart4 = new Chart(ctx4).';
			if(count($wiki_count) > 1) {
				$dashCode = $dashCode . 'Line(wiki_by_chapter)';
			} else {
				$dashCode = $dashCode . 'Bar(wiki_by_chapter,{scaleOverride: true, scaleStepWidth: 1, scaleSteps: ';
				$dashCode = $dashCode . (int)(count($wiki_count) + 4) . '}';
			}

			$dashCode = $dashCode . ');</script>';

        }
        else {
            $dashCode = '<div class="col-md-12"><div class="well"><h2>Please login to view course dashboard!</h2></div></div>';
        }

        session_write_close();

        return $dashCode;
        
    }
}

?>