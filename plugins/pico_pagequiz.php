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
	
	public function file_meta(&$meta)
    {
        $this->type = $meta['layout'];
    }
	
	public function content_parsed(&$content)
	{
        if ($this->type == "content") {
            $this->request = substr($this->request, 1, stripos($this->request, '/', 2));
            $this->path = $this->path . '/content/' . $this->request . 'keywords.xml';
            if (file_exists($this->path)) {
                $this->data = simplexml_load_file($this->path);
                $n = 0;
                for($i = 0; $i < count($this->data->title); $i++) {
                //echo $content;
                if (strpos($content, $this->data->title[$i]) !== FALSE) {
                    $this->alist[$n] = $this->data->title[$i];
                    $this->qlist[$n] = $this->data->text[$i];
                    $n++;
                }
            }
            }
        }
	}
	
	public function after_render(&$output)
	{
        if ($this->type == "content" && file_exists($this->path)) {
            echo '<script type="text/javascript">
                    $(function($){
    
    var quiz = {
        multi: [';
        for ($i = 0; $i < count($this->qlist); $i++) {
            echo '
            {
                ques: "' . $this->qlist[$i] . '",
                ans: "' . $this->alist[$i] . '"
            },
            ';
        }
        echo '
        ]
    },
    options = {
        intro: "Click below to begin quiz!",
        allRandom: true,
        title: "Quiz",
        disableDelete = true,
        numOfQuizQues = 5;
    };
    
    $("#quizArea").jQuizMe(quiz, options);
});
</script>';
        }
	}
	
}