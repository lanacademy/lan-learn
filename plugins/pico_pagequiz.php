<?php
/**
 * On-page dynamically generated quizzes
 *
 * @author Timothy Su
 * @link http://www.timofeo.com/
 * @license http://opensource.org/licenses/MIT
 */
 
class Pico_Pagequiz {

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
                //echo($this->path);
                //var_dump($this->data);
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
            else {
                $this->yes = false;
            }
        }
        else {
            $this->yes = false;
        }
        //echo $this->yes ? 'yes' : 'no';
	}

    public function before_render(&$twig_vars, &$twig)
    {
        if ($this->yes == false) {
            $twig_vars['noquiz'] = true;
        }
        else {
            $twig_vars['noquiz'] = false;
        }
        //echo $twig_vars['noquiz'] ? 'yes' : 'no';
    }
	
	public function after_render(&$output)
	{
        if ($this->type == "content" && $this->yes) {
            $output = $output . '<script type="text/javascript">
                    $(function($){
    
    var quiz = {
        multiList: [';
        for ($i = 0; $i < count($this->qlist); $i++) {
            $output = $output . '
            {
                ques: "' . addslashes($this->qlist[$i]) . '",
                ans: "' . addslashes($this->alist[$i]) . '"
            }';
            if ($i != count($this->qlist) - 1) {
                $output = $output . ",";
            }
        }
        $output = $output . '
        ]
    },
    options = {
        intro: "Click below to begin quiz!",
        allRandom: true,
        title: "Quiz",
        multiLen: 4,
        disableDelete: true,
        numOfQuizQues: ';
        if (count($this->qlist) < 5) {
            $output = $output . (count($this->qlist));
        }
        else {
        $output = $output . '5';
    }
    $output = $output . '
};
    
    $("#quizArea").jQuizMe(quiz, options);
});
</script>';
        }
	}
	
}