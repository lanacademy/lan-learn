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

	public function file_meta(&$meta)
    {
        $this->type = $meta['layout'];
    }

    public function before_load_content(&$file)
    {
        if (file_exists($file)) {
            $this->yes = true;
            $this->quizcontent = file_get_contents($file);
        }
    }
	
	public function content_parsed(&$content)
	{
        if ($this->type == "content") {
            $this->request = substr($this->request, 1 + $this->offset, stripos($this->request, '/', 1 + $this->offset));
            $this->path = $this->path . '/content/' . $this->request . 'keywords.xml';
            echo $this->path;
            if (file_exists($this->path)) {
                $this->data = simplexml_load_file($this->path);
                $n = 0;
                $o = 0;
                for($i = 0; $i < count($this->data->title); $i++) {
                if (strpos($this->quizcontent, (String) $this->data->title[$i]) !== FALSE) {
                    $this->alist[$n] = $this->data->title[$i];
                    $this->qlist[$n] = $this->data->text[$i];
                    $n++;
                }
                else {
                    $this->plist[$o] = $this->data->title[$i];
                    $o++;
                }
            }
            }
        }
	}
	
	public function after_render(&$output)
	{
        if ($this->type == "content" && $this->yes) {
            $output = $output . '<script type="text/javascript">
                    $(function($){
    
    var quiz = {
        multi: [';
        for ($i = 0; $i < count($this->qlist); $i++) {
            $output = $output . '
            {
                ques: "' . $this->qlist[$i] . '",
                ans: "' . $this->alist[$i] . '"
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