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
    }
    
    public function file_meta(&$meta)
    {
        $this->type = $meta['layout'];
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
        if ($this->type == 'quiz' && !isset($_GET['grade']) || $_GET['grade'] != 1) {
            $content = $this->dump_quiz();
        }
        else if ($this->type == 'quiz' && isset($_GET['grade']) && $_GET['grade'] == 1) {
            $content = $this->grade_quiz();
        }
    }
    
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
        $htmlcode = $this->dump_quizhelper();
        return $htmlcode;
    } // function dump_quiz

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
            </h2>' ;
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
        $correctpts = 0;
        for ($i = 0; $i < count($quiz['quizzes']); ++$i) {
            $q = $quiz['quizzes'][$i];
            $totalpts += $q['credit'];
            if ($q['type'] == "single") {
                for ($n = 0; $n < count($q['choices']; ++$n) {
                    if (strpos($q['choices'][$n], "*") !== false) {
                        $correct = $n;
                    }
                }
                if (isset($_POST["problem-" . $i]) && $_POST["problem-" . $i] == $correct) {
                    $correctpts += $q['credit'];
                }
            }
            else if($q['type'] == "multiple") {
                for ($n = 0; $n < count($q['choices']; ++$n) {
                    if (strpos($q['choices'][$n], "*") !== false) {
                        $correct[$n] = 1;
                    }
                    else {
                        $correct[$n] = 0;
                    }
                }
            }
            //var_dump($q['choices']);
        }
        //var_dump($_POST);
        $result = "You scored a " . $correctpts . "/" . $totalpts;
        return $result;
    }
}