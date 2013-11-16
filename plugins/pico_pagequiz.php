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
	
	public function file_meta(&$meta)
    {
        $this->type = $meta['layout'];
    }
	
	public function content_parsed(&$content)
	{
        if ($this->type == "content") {
            $this->request = substr($this->request, 1, stripos($this->request, '/', 2));
            $this->path = $this->path . '/content/' . $this->request . 'keywords.xml';
            if (!file_exists($this->path)) {
                exit;
            }
            $this->data = simplexml_load_file($this->path);
            $n = 0;
            for($i = 0; $i < count($this->data->title); $i++) {
                echo $this->data->title[$i];
                if (strpos($content, $this->data->title[$i]) !== FALSE) {
                    $this->alist[$n] = $this->data->title[$i];
                    $this->qlist[$n] = $this->data->text[$i];
                    $n++;
                }
            }
        }
	}
	
	public function get_page_data(&$data, $page_meta)
	{
		
	}
	
	public function get_pages(&$pages, &$current_page, &$prev_page, &$next_page)
	{
		
	}
	
	public function before_twig_register()
	{
		
	}
	
	public function before_render(&$twig_vars, &$twig)
	{
		
	}
	
	public function after_render(&$output)
	{
        if ($this->type == "content") {
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