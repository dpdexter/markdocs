<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/* 
* Source: https://github.com/altrano/codeigniter-twig 
*/ 

class Twig
{
	private $CI;
	private $_twig;
	private $_template_dir;
	private $_cache_dir;
	private $_debug;
	/**
	 * Constructor
	 *
	 */
	function __construct($debug = false)
	{
		$this->_debug = $debug;

	    $this->CI =& get_instance();
	    $this->CI->config->load('twig');
	    
	    ini_set('include_path',
	    ini_get('include_path') . PATH_SEPARATOR . APPPATH . 'libraries/Twig');
        
        require_once (string) "Autoloader" . EXT;
            
        log_message('debug', "Twig Autoloader Loaded");
		
        Twig_Autoloader::register();

        $this->_template_dir = $this->CI->config->item('template_dir');
        $this->_cache_dir = $this->CI->config->item('cache_dir');

        
	}

	public function render($template, $data = array()) {

		$loader = new Twig_Loader_Filesystem($this->_template_dir);
		
		$this->_twig = new Twig_Environment($loader, array(
		    'cache' => $this->_cache_dir,
		    'debug' => $this->_debug,
		));

        $template = $this->_twig->loadTemplate($template);

        return $template->render($data);
	}
	
	public function render_from_string($str, $data = array()) {
       
       	$loader = new Twig_Loader_String($str);
       	
		$this->_twig = new Twig_Environment($loader, array(
            'cache' => $this->_cache_dir,
            'debug' => $this->_debug,
        ));       	
        $twig = new Twig_Environment($loader);

        return $twig->render($str,$data);
	}

    public function display($template, $data = array()) {

	    $template = $this->_twig->loadTemplate($template);
		
	    /* elapsed_time and memory_usage */
        $data['elapsed_time'] = $this->CI->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end');
        $memory = (!function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2) . ' MB';
        $data['memory_usage'] = $memory;

        $template->display($data);
	}
}