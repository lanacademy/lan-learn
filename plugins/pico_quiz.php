<?php
/**
 * A plugin that generates a quiz from certain Markdown files
 *
 * @author Timothy Su
 * @link http://timofeo.com/
 * @license http://opensource.org/licenses/MIT
 */

class Pico_Quiz
{
    
    private $theme;
    
    public function __construct()
    {
        $plugin_path       = dirname(__FILE__);
        $parent_path       = dirname(dirname(__FILE__));
        $this->log_path    = $parent_path . '/log/';
        $this->path        = $plugin_path;
        $this->small_cases = array(
            'a',
            'in',
            'the',
            'with',
            'out',
            'an',
            'on',
            'of',
            'off',
            'under',
            'above'
        );
    }
    
    public function config_loaded(&$settings)
    {
        $this->theme = $settings['theme'];
        $this->config = $settings;
    }
    
    public function file_meta(&$meta)
    {
        $this->type = $meta['layout'];
        $this->meta = $meta;
    }
    
    public function before_load_content(&$file)
    {
        if (file_exists($file)) {
            $this->quizcontent = file_get_contents($file);
            // This only works if one character = one byte?
            $this->quizcontent = substr($this->quizcontent, (-1 * (strlen($this->quizcontent) - (strpos($this->quizcontent, "*/") + 2))));
        }
    }
    
    public function content_parsed(&$content)
    {
    	// not sure the best place to do this so doing it here
    	session_start();
        if (isset($_SESSION['authed']) && $_SESSION['authed']) {
        	$this->user = $_SESSION['username'];
		}
		session_write_close();

		// Hardcoded because this isnt even relevant yet
		// Better things to do then figure this out
		$this->chapter = 'Human_Origins';


        if ($this->type == 'quiz' && !isset($_GET['grade']) && $_GET['grade'] != 1) {
            $content = $this->dump_quiz();
        }
        else if ($this->type == 'quiz' && isset($_GET['grade']) && $_GET['grade'] == 1) {
            $content = $this->grade_quiz();
        }
    }

    //private function get_chapter() {
    	
    //}
    
    private function trim_str_array($str_array)
    {
        $result = array();
        foreach ($str_array as $str) {
            $pr = trim($str);
            if (strlen($pr) > 0)
                $result[] = $pr;
        }
        return $result;
    }
    
    private function string_to_url($string)
    {
        $result = trim(strtolower($string));
        $result = preg_replace('/[ ]+/', '-', $result);
        return $result;
    }
    
    private function url_to_string($url)
    {
        $result = preg_replace('/-+/', ' ', $url);
        $result = ucwords($result);
        foreach ($this->small_cases as $word) {
            $result = preg_replace('/\b' . ucfirst($word) . '\b/', $word, $result);
        }
        return $result;
    }
    
    private function parse_problems($quizzes)
    {
        $result = array();
        foreach ($quizzes as $quiz) {
            $t = preg_split('/(\n\@)/', $quiz);
            unset($type);
            if (count($t) > 1)
                $type = 'single';
            if (!isset($type)) {
                $t = preg_split('/(\n\#)/', $quiz);
                if (count($t) > 1)
                    $type = 'multiple';
            }
            if (!isset($type)) {
                $t = preg_split('/(\n\[)/', $quiz);
                if (count($t) > 1)
                    $type = 'text';
            }
            if (!isset($type)) {
                $t = preg_split('/(\n\{)/', $quiz);
                if (count($t) > 1)
                    $type = 'code';
            }
            $obj['type']        = $type;
            $desc               = trim($t[0]);
            $credit             = substr($desc, 0, 1);
            $desc               = substr($desc, 1, strlen($desc) - 1);
            $desc               = preg_replace('/(\r\n|\r|\n)/', '<br/>', $desc);
            $desc               = '<p>' . preg_replace('/\s*\<br\/\>\s*\<br\/\>\s*/', '</p><p>', $desc) . '</p>';
            $obj['description'] = preg_replace('/\s*\<br\/\>\s*/', ' ', $desc);
            $obj['credit']      = $credit;
            unset($obj['choices']);
            if (strcmp($type, 'single') == 0 || strcmp($type, 'multiple') == 0) {
                $obj['choices'] = array();
                for ($i = 1; $i < count($t); ++$i)
                    $obj['choices'][] = $t[$i];
            }
            unset($obj['answer']);
            if (strcmp($type, 'text') == 0 || strcmp($type, 'code') == 0) {
                $obj['answer'] = trim($t[1]);
                $obj['answer'] = substr($obj['answer'], 0, strlen($obj['answer']) - 1);
                $obj['answer'] = trim($obj['answer']);
            }
            // when everything done
            $result[] = $obj;
        }
        return $result;
    }
    
    private function parse_quiz($quiz_specs)
    {
        $specs = $quiz_specs;
        $specs = preg_split('/(\n|^)\+/', $specs);
        $specs = $this->trim_str_array($specs);
        $meta  = $this->trim_str_array(preg_split('/\n/', $specs[0]));
        foreach ($meta as $m) {
            $t                  = explode(':', $m);
            $k                  = strtolower(trim($t[0]));
            $k                  = str_replace(' ', '_', $k);
            $struct['meta'][$k] = trim($t[1]);
        }
        $struct['quizzes'] = array();
        for ($i = 1; $i < count($specs); ++$i)
            $struct['quizzes'][] = $specs[$i];
        $struct['quizzes'] = $this->parse_problems($struct['quizzes']);
        return $struct;
    }
    
    // can delete this??
    private function dump_quiz_summary($id)
    {
        $quiz_full  = 'quizzes/' . $id;
        $specs      = $quiz_full . '/specs.md';
        $cases_dir  = $quiz_full . '/testcases';
        $submit_dir = $quiz_full . '/submissions';
        $quiz       = parse_quiz($specs);
        echo $id;
        echo $quiz['meta']['deadline'];
        echo $quiz['meta']['hard_deadline'];
    }
    
    private function dump_quiz()
    {
    	if(file_exists($this->log_path . '/tests/' . $this->user . '_' . $this->chapter . '_quiz.xml')) {
			// call a thing that loads the xml and fakes POST
    		//$this->xml_quiz_loader();
    		return '<h4>You already took this quiz</h4>';
    	}
        $htmlcode = $this->dump_quizhelper();
        return $htmlcode;
    } // function dump_quiz

    //private function xml_quiz_loader()
    //{

    //}

    private function dump_quizhelper()
    {
        $quiz_full = 'quizzes/' . $id;
        $specs = $this->quizcontent;
        $cases_dir = $quiz_full . '/testcases';
        $submit_dir = $quiz_full . '/submissions';
        $quiz = $this->parse_quiz($specs);
        global $title;
        $title_split = explode(' ', $title);
        $quiz_order = $title_split[0];
        $quiz_title = substr($title, strlen($quiz_order));
        $result =
        '<form class="quiz-form" id="quiz-form" method="post" action="' . $_SERVER[REQUEST_URI] . '?grade=1' . '">
            <script>var editor = null;</script>';
        for ($i = 0; $i < count($quiz['quizzes']); ++$i) {
            $q = $quiz['quizzes'][$i];
            $number = $i + 1;
            $result = $result . 
            '<h2>
                Problem '. $number .'
                <span class="credit"> - '. $q['credit'] .' Point(s)</span>
            </h2>
            <p>' . $q['description'] . '</p>' ;
            if ($q['type'] == 'single'):
                $result = $result . '<ul>';
                for ($j = 0; $j < count($q['choices']); ++$j):
                    $choice = $q['choices'][$j];
                    $result = $result .
                    '<li>
                        <label for="problem-'. $i .'-choice-'. $j .'">
                        <input
                        type="radio"
                        value="'. $j .'"
                        name="problem-'. $i .'"
                        id="problem-'. $i .'-choice-'. $j .'"/> '
                        . trim(substr($choice, 1, strlen($choice) - 1)) . '</label>
                    </li>';
                endfor;
                $result = $result . '</ul>';
            elseif ($q['type'] == 'multiple'):
                $result = $result . '<ul>';
                for ($j = 0; $j < count($q['choices']); ++$j):
                    $choice = $q['choices'][$j];
                    $result = $result .
                    '<li>
                        <label for="problem-'. $i .'-choice-'. $j .'">
                        <input
                        type="checkbox"
                        value="true"
                        id="problem-'. $i .'-choice-'. $j .'"
                        name="problem-'. $i .'-choice-'. $j .'"/> '. trim(substr($choice, 1, strlen($choice) - 1)) . '</label>
                    </li>';
                endfor;
                $result = $result . '</ul>';
            elseif ($q['type'] == 'text'):
                $result = $result . '<textarea
                class="text-editor"
                id="problem-'. $i .'"
                name="problem-'. $i .'"></textarea><br><br>';
            elseif ($q['type'] == 'code'):
                $result = $result . '<div
                class="code-editor"
                id="problem-'. $i .'"></div>
                <script>
                editor = ace.edit("problem-'. $i .'");
                editor.setHighlightActiveLine(false);
                editor.getSession().setMode("ace/mode/c_cpp");
                </script>';
            endif;
        }
        $result = $result . '
        <div class="form-tail">
            <input type="hidden" name="action" value="submit" />
            <input type="hidden" name="quiz-id" value="'. $id .'" />
            <input type="submit" class="button submit" value="Submit" />
            <input type="button" class="button" onclick="window.history.back()" value="Cancel" />
        </div>
    </form>';
	return $result;
	}

    private function grade_quiz() {
        $specs = $this->quizcontent;
        $quiz = $this->parse_quiz($specs);
        $correctpts = 0.0;
        $totalpts = 0;

        // load the completed quiz into xml
       	$xml = new SimpleXMLElement('<quiz></quiz>');

        // processing each question
        for ($i = 0; $i < count($quiz['quizzes']); ++$i) {
        	$xml->addChild('question', "");
        	$xml->question[$i]->addAttribute('number', $i);
            $q = $quiz['quizzes'][$i];
            $totalpts += $q['credit'];
            if ($q['type'] == "single") {
            	$xml->question[$i]->addAttribute('type', 'single');
            	$xml->question[$i] = $_POST["problem-" . $i];
                for ($n = 0; $n < count($q['choices']); ++$n) {
                    if (strpos($q['choices'][$n], "*") !== false) {
                        $correct = $n;
                    }
                }
                if (isset($_POST["problem-" . $i]) && $_POST["problem-" . $i] == $correct) {
                    $correctpts += $q['credit'];
                }
            }
            else if($q['type'] == "multiple") {
            	$xml->question[$i]->addAttribute('type', 'multiple');
                for ($n = 0; $n < count($q['choices']); ++$n) {

                	$xml->question[$i]->addChild('choice', $_POST["problem-" . $i . "-choice-" . $n]);

                    if (strpos($q['choices'][$n], "*") !== false) {
                        $correctarray[$n] = 1;
                    }
                    else {
                        $correctarray[$n] = 0;
                    }
                }
                $iscorrect = true;
                for ($n = 0; $n < count($q['choices']); ++$n) {
                    if (isset($_POST["problem-" . $i . "-choice-" . $n])) {
                        if ($correctarray[$n] != 1) {
                            $iscorrect = false;
                        }
                    }
                    else {
                        if ($correctarray[$n] != 0) {
                            $iscorrect = false;
                        }
                    }
                }
                if ($iscorrect) {
                    $correctpts += $q['credit'];
                }
            }
            else if($q['type'] == "text") {
            	$xml->question[$i]->addAttribute('type', 'text');
            	$xml->question[$i] = $_POST["problem-" . $i];
                $wordarray = explode(',', $q['answer']);
                $nummatches = 0;
                for ($n = 0; $n < count($wordarray); ++$n) {
                    if (isset($_POST["problem-" . $i]) && preg_match('/\b' . $wordarray[$n] . '\b/', $_POST["problem-" . $i])) {
                        $nummatches++;
                    }
                }
                $pointsearned = ($nummatches / count($wordarray)) / $q['credit'];
                $correctpts += $pointsearned;
            }
            //var_dump($q['choices']);
            //var_dump($q['answer']);
            //var_dump($_POST);
        }
        //var_dump($_POST);
        $result = "<h2><i>You scored a " . $correctpts . "/" . $totalpts . "</i></h2><br />";
        $result = $result . $this->dump_gradedquiz();

        $xml->asXML($this->log_path . '/tests/' . $this->user . '_' . $this->chapter . '_quiz.xml');

        session_write_close();

        $this->log_to_tracker($correctpts, $totalpts);

        //var_dump($result);
        return $result;
    }

    private function log_to_tracker($correctpts, $totalpts) {
    	$coursename = $this->config['site_title'];
    	$data = '[CHT],';
    	$data = $data . date('Y/m/d H:i:s');
    	$data = $data . ',' . $coursename . ',' . $this->chapter;
    	$data = $data . ',' . $correctpts . '/' . $totalpts . "\n";
    	if (file_exists($this->log_path . $this->user . '.log')) {
            $data = file_get_contents($this->log_path . $this->user . '.log') . $data;
        }
        file_put_contents($this->log_path . $this->user . '.log', $data);
    }

    private function dump_gradedquiz()
    {
        $quiz_full = 'quizzes/' . $id;
        $specs = $this->quizcontent;
        $cases_dir = $quiz_full . '/testcases';
        $submit_dir = $quiz_full . '/submissions';
        $quiz = $this->parse_quiz($specs);
        global $title;
        $title_split = explode(' ', $title);
        $quiz_order = $title_split[0];
        $quiz_title = substr($title, strlen($quiz_order));
        for ($i = 0; $i < count($quiz['quizzes']); ++$i) {
            $q = $quiz['quizzes'][$i];
            $number = $i + 1;
            $result = $result . 
            '<h2>
                Problem '. $number .'
                <span class="credit"> - '. $q['credit'] .' Point(s)</span>
            </h2>
            <p>' . $q['description'] . '</p>' ;
            if ($q['type'] == 'single'):
                $result = $result . '<ul>';
                for ($j = 0; $j < count($q['choices']); ++$j):
                    $choice = $q['choices'][$j];
                $correct = -1;
                if (strpos($q['choices'][$j], "*") !== false) {
                    $correct = $j;
                }
                if($j == $correct && isset($_POST["problem-" . $i]) && $_POST["problem-" . $i] == $j) {
                        $result = $result .
                    '<li>
                        <label for="problem-'. $i .'-choice-'. $j .'">' . trim(substr($choice, 1, strlen($choice) - 1)) . '</label> <i>- Correct answer & Your answer</i>
                    </li>';
                }
                else if($j == $correct) {
                    $result = $result .
                    '<li>
                        <label for="problem-'. $i .'-choice-'. $j .'">' . trim(substr($choice, 1, strlen($choice) - 1)) . '</label> <i>- Correct answer</i>
                    </li>';
                }
                else if(isset($_POST["problem-" . $i]) && $_POST["problem-" . $i] == $j)
                {
                    $result = $result .
                    '<li>
                        <label for="problem-'. $i .'-choice-'. $j .'">' . trim(substr($choice, 1, strlen($choice) - 1)) . '</label> <i>- Your answer</i>
                    </li>';
                }
                else {
                    $result = $result .
                    '<li>
                        <label for="problem-'. $i .'-choice-'. $j .'">' . trim(substr($choice, 1, strlen($choice) - 1)) . '</label>
                    </li>';
                }
                endfor;
                $result = $result . '</ul>';
            elseif ($q['type'] == 'multiple'):
                $result = $result . '<ul>';
                for ($j = 0; $j < count($q['choices']); ++$j):
                    $choice = $q['choices'][$j];
                $correct = -1;
                if (strpos($q['choices'][$j], "*") !== false) {
                    $correct = 1;
                }
                if($correct == 1 && isset($_POST["problem-" . $i . "-choice-" . $j])) {
                        $result = $result .
                    '<li>
                        <label for="problem-'. $i .'-choice-'. $j .'">' . trim(substr($choice, 1, strlen($choice) - 1)) . '</label> <i>- Correct answer & Your answer</i>
                    </li>';
                }
                else if($correct == 1) {
                    $result = $result .
                    '<li>
                        <label for="problem-'. $i .'-choice-'. $j .'">' . trim(substr($choice, 1, strlen($choice) - 1)) . '</label> <i>- Correct answer</i>
                    </li>';
                }
                else if(isset($_POST["problem-" . $i . "-choice-" . $j]))
                {
                    $result = $result .
                    '<li>
                        <label for="problem-'. $i .'-choice-'. $j .'">' . trim(substr($choice, 1, strlen($choice) - 1)) . '</label> <i>- Your answer</i>
                    </li>';
                }
                else {
                    $result = $result .
                    '<li>
                        <label for="problem-'. $i .'-choice-'. $j .'">' . trim(substr($choice, 1, strlen($choice) - 1)) . '</label>
                    </li>';
                }
                endfor;
                $result = $result . '</ul>';
            elseif ($q['type'] == 'text'):
                $result = $result . '<textarea readonly="true"
                class="text-editor"
                id="problem-'. $i .'"
                name="problem-'. $i .'">' . $_POST["problem-" . $i] .'</textarea><p><b>We were looking for the following word(s): ' . str_replace(',', ', ', $q["answer"]) . '</b></p><br /><br />';
            elseif ($q['type'] == 'code'):
                $result = $result . '<div
                class="code-editor"
                id="problem-'. $i .'"></div>
                <script>
                editor = ace.edit("problem-'. $i .'");
                editor.setHighlightActiveLine(false);
                editor.getSession().setMode("ace/mode/c_cpp");
                </script>';
            endif;
        }

        return $result;
    }
}