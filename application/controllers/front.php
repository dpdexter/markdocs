<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include(APPPATH.'libraries/geshi.php');

class Front extends CI_Controller {
	
	public $directory = array();

	public $vars = array();
	
	// we are going to file cache a bunch of stuff 
	// but should we use it or not?
		public $use_cache 	= FALSE;

	// clients cache path
		public $cache_path 	= '';
		public $download 	= '';
		public $dl_arr	 	= array();
	
	// Some goodies for counting tree levels
		public $tree_cnt 	= 0;
	
	// Get the client info
		public $info = array();
		public $path = array();
				
	public function index()
	{
		
		$this->load->library('spyc');
		$this->load->library('twig');
		$this->load->helper('file');
		$this->load->helper('markdocs');
		$this->load->helper('markdown');
		
		// Load the output profiler?		
			if($this->config->item('output_enabled') === true){
				$this->output->enable_profiler(TRUE);
				$sections = array(
				    'config'  => TRUE,
				    'queries' => FALSE
				    );
				$this->output->set_profiler_sections($sections);
			}
			
		// Get the file client details
			$this->vars["info"] = array(
										'theme' 		=> $this->config->item('theme'),
										'content' 		=> $this->config->item('content'),
										'company' 		=> $this->config->item('company'),
										'meta_title' 	=> $this->config->item('meta_title'),
										'domain' 		=> $this->config->item('base_url'),
										'first_link' 	=> $this->config->item('first_link'),
										'version' 		=> $this->config->item('version')
									);		
			
			
			
		// Is it valid?
			if(!file_exists('../content/'.$this->vars["info"]["content"])){
				show_404();
			}

		// Check if the directory has changed 
			$this->cache_path 	= realpath(dirname(__FILE__).'/../cache').'/'.$this->vars["info"]["content"];
			$this->download 	= realpath(dirname(__FILE__).'/../builds').'/'.$this->vars["info"]["content"];
			
			if(!file_exists($this->cache_path)){
				mkdir($this->cache_path);
				write_file($this->cache_path.'/md5.md','');
				$md5 = '';
			}else{
				$md5 = read_file($this->cache_path.'/md5.md');
			}
		
		$current_md5 = md5_dir('../content/'.$this->vars["info"]["content"]);
		if($current_md5 == $md5){
			$this->use_cache = TRUE;
		}else{
			if(file_exists($this->download.'/'.$this->vars["info"]["content"].'.zip')){
				unlink($this->download.'/'.$this->vars["info"]["content"].'.zip');
			}
			
			// Lets write the current md5 to the file
			write_file($this->cache_path.'/md5.md',$current_md5);
		}
		
		$uri = $this->uri->segment_array();
		
		// Build Nav Tree 
			$this->vars["nav"] = $this->_build_tree($this->vars["info"]["content"]);
			
		// Here we'll handle the file structure

			if(!isset($uri[1]) || $uri[1] == 'index.html'){
				$str = read_file('../content/'.$this->vars["info"]["content"].'/index.md');
			}else{
				if($uri[1] == 'download'){
					$this->load->library('zip');
					$this->load->helper('file');
					$this->load->helper('download');
					if(file_exists($this->download.'/'.$this->vars["info"]["content"].'.zip')){
						$data = file_get_contents($this->download.'/'.$this->vars["info"]["content"].'.zip');
						$name = $this->vars["info"]["content"].'.zip';
						force_download($name, $data);
					}else{
						if(file_exists($this->download.'/guide')){
							$this->_rrmdir($this->download.'/guide');
						}
						if(!file_exists($this->download)){
							mkdir($this->download);
						}

						mkdir($this->download.'/guide');
						
						// Add standard file
							mkdir($this->download.'/guide/css/');
							mkdir($this->download.'/guide/css/'.$this->vars["info"]["theme"].'/');
							mkdir($this->download.'/guide/css/'.$this->vars["info"]["theme"].'/images');
							mkdir($this->download.'/guide/js');

							
							$doc_root = rtrim($_SERVER["DOCUMENT_ROOT"],"/");
							copy($doc_root.'/js/jquery.js',$this->download.'/guide/js/jquery.js');
							copy($doc_root.'/css/'.$this->vars["info"]["theme"].'/style.css',$this->download.'/guide/css/'.$this->vars["info"]["theme"].'/style.css');
						
						// Get the images
							mkdir($this->download.'/guide/img');
							$img = get_filenames($doc_root.'/img');
							foreach($img as $i){
								copy($doc_root.'/img/'.$i,$this->download.'/guide/img/'.$i);
							}

						// Get the images
							mkdir($this->download.'/guide/assets');
							$img = get_filenames($doc_root.'/assets');
							foreach($img as $i){
								copy($doc_root.'/assets/'.$i,$this->download.'/guide/assets/'.$i);
							}
						
						$file = file_get_contents('http://'.$_SERVER["HTTP_HOST"]);
						$file = str_replace('<!-- BASE !-->','<base href="./" />',$file);
						$file = str_replace('href="/','href="',$file);
						$file = str_replace('src="/','src="',$file);
						
						// Strip out any ignores.
						
						$file = preg_replace("#<!--ignore([^!-->]*)!-->(((?!<!--/?ignore(?:[^!-->]*)!-->).)*)<!--/ignore!-->#si","",$file);
						
						write_file($this->download.'/guide/index.html',$file);
						
						foreach($this->dl_arr as $fl){
							$file = file_get_contents('http://'.$_SERVER["HTTP_HOST"].$fl);
							$file = str_replace('href="/','href="',$file);
							$file = str_replace('src="/','src="',$file);
							
							// Do the same thing as above. 
								$file = preg_replace("#<!--ignore([^!-->]*)!-->(((?!<!--/?ignore(?:[^!-->]*)!-->).)*)<!--/ignore!-->#si","",$file);
						
							$path = $this->download.'/guide'.$fl;
							// calculate and build the directories
								$a = explode('/',ltrim($fl,'/'));
								$p = '';
								$depth = '';
								foreach($a as $b){
									if(substr($b,-5,5) != '.html'){
										$p .= '/'.$b;
										if(!file_exists($this->download.'/guide'.$p)){
											mkdir($this->download.'/guide'.$p);	
										}
										$depth .= '../';
									}
								}
							$file = str_replace('<!-- BASE !-->','<base href="'.$depth.'" />',$file);
							if(substr($path,-5,5) != '.html'){
								$path .= '/index.html';
							}
							write_file($path,$file);
						}
						$this->zip->read_dir($this->download.'/guide/',FALSE);
						$this->zip->archive($this->download.'/'.$this->vars["info"]["content"].'.zip');
						header('location: /download');
						exit;
					}
					
				}else{
				
					$this->load->helper('url');
					$url = "/".uri_string();
					
					if(!isset($this->path[$url])){
						$this->vars["info"]["meta_title"] = '404 file not found';
						$this->vars["content"] 	= $this->twig->render('error.html',$this->vars);
						$this->vars["breadcrumb"]	= '	<div id="breadcrumb">
													404 Error File Not Found
												</div>';
						
						$this->load->library('twig');
						$output = $this->twig->render('front.html', $this->vars);
						header("HTTP/1.0 404 Not Found");
						echo $output;
						return;
					}else{
						$path = $this->path[$url];
					}
					$fl = rtrim($path,'/');
					if(substr($fl,-11,11) == '/index.html'){
						$fl = str_replace('/index.html','/index.md',$fl);
					}
										
					$str = read_file($fl);
					// Build Nav Tree 
						$this->vars["nav_js"] = '<script type="text/javascript">
													$(function(){';
							foreach($uri as $u){
								if(substr($u,-5,5) != ''){
									$this->vars["nav_js"] .= "$('#".str_replace("%26","",$u)."').show();\n";	
								}
							}
						$this->vars["nav_js"] .= '	});
												</script>';
				}
			}
		
		// Set the meta title
			$this->vars["info"]["meta_title"] = $this->vars["info"]["meta_title"];
		
		// Load the custom variables 
			$this->vars["custom"] = array();
			if(file_exists('../custom_variables.php')){
				require_once('../custom_variables.php');
				$this->vars["custom"] = array_merge($this->vars["custom"],$custom);
			}
			
        // Get the YAML information 
            preg_match('/^-{3}\s*$(.*?)^(?:-{3}|\.{3})\s*$/ms', $str, $matches);
            if(!empty($matches))
            {
                // Remove the YAML from the str variable
        			$str = str_replace($matches[0],'',$str);
                
                // Parse the YAML
        			$yaml = $this->spyc->YAMLLoad($matches[0]);

                // Allow for info overrides
                    if(isset($yaml["info"])){
                        $this->vars["info"] = array_merge($this->vars["info"],$yaml["info"]);
                    }
                // Allow for custom variable overrides and setting
                    if(isset($yaml["custom"])){
                        $this->vars["custom"] = array_merge($this->vars["custom"],$yaml["custom"]);
                    }
            }
            
        // Parse with Twig            
            $str = $this->twig->render_from_string($str, $this->vars);

		// Parse the markdown 
			$str = Markdown($str);
			preg_match_all('/<p>CODE::([\w]+)(.*?)(<pre><code>)(.*?)(<\/code><\/pre>)/ism',$str,$matches);
			$i = 0;
			foreach($matches[1] as $m){
				//
					$process = str_replace("&lt;","<",$matches[4][$i]);
					$process = str_replace("&gt;",">",$process);
					$geshi = new geshi(trim($process),$m);
					if(strtoupper($m) == 'EE'){
						$m = 'ExpressionEngine';
					}
					#$geshi->set_header_content('Syntax Highlighting: '.$m);
					$geshi->set_code_style('');
					$geshi->set_overall_class('codeblock');
					#$geshi->enable_line_numbers(1);
					$tmp = $geshi->parse_code();
					$str = str_replace($matches[0][$i],$tmp,$str);
				$i++;
			}
			$this->vars["markdocs"]["ignore"] = "<!--ignore!-->";
			$this->vars["markdocs"]["endignore"] = "<!--/ignore!-->";

			$this->vars["content"] = $str;
			$this->vars["breadcrumb"] = $this->_build_breadcrumb();
			$this->vars["http_host"] = $_SERVER["HTTP_HOST"];

		// View it 
			
			$output = $this->twig->render('front.html', $this->vars);
			
			$this->output->set_output($output);
	}

/**
	HELPER METHODS
**/

	public function _build_tree($subdomain){
		if($this->use_cache){
			$str = read_file($this->cache_path.'/tree.md');
			$tree = unserialize($str);
		}else{
			$tree = $this->_get_files('../content/'.$subdomain);
			write_file($this->cache_path.'/tree.md',serialize($tree));
		}	
		return $this->_array2ul($tree);
	}
	
	public function _get_files($dir) {
		
		$this->load->helper('directory');
		
		if(!isset($files)){
			$files = array();
		}
		if ($handle = opendir($dir)) {
	    	while (false !== ($file = readdir($handle))) {
				$ignore = array(".","..","img");

	        	if (!in_array($file,$ignore) && substr($file,0,1) != '.'){
	        	
	        		if(is_dir($dir.'/'.$file)) {
	            		// Call a name 
		            		$nm = urlencode(strtolower(str_replace(" ","-",trim($file))));
		                	$this->directory[$nm] = $file;
		                	$dir2 = $dir.'/'.$file;
		                	$files[$file.'|~|'.$dir] = $this->_get_files($dir2);
	            	}else {
	            		// have add a double space to the key value so we can 
	            		// key sort them properly
	              			if(	substr($file,0,1) != '.'
	              				&& 
	              				strtolower(substr($file,-3,3)) == '.md'){
	              				$files[str_replace(".md","  .md",$file)] = $dir.'/'.$file;
	            			}
	            	}
	        	}
	    	}
	    	closedir($handle);
	    }
	    ksort($files);
	  	return $files;
	}
	
	public function _array2ul($files,$id='menu'){
		
		$tree = '';
		
		if(count($files) > 1){
			$tree = '<ul id="'.str_replace("&","",$id).'" class="" data-count="'.count($files).'">';
		}
		
		// Put the overview in on the 
		// first go round
			if($this->tree_cnt == 0 && trim($this->vars["info"]["first_link"]) != ''){ 
				$tree .= '<li id="menu_first"><a href="/index.html">'.$this->vars["info"]["first_link"].'</a> </li>';
			}
			$this->tree_cnt++;
			
		// Lop through the array recursively to pick up sub-directories
			foreach($files as $key => $val){
				if(is_array($val)){
					$a = explode("|~|",$key);
					$b = strtolower(str_replace(' ','-',$a[0]));
					$c = trim($a[1],'/').'/'.$a[0].'/index.html';					
					
					$this->dl_arr[] = $this->_format_path($c);
					$tree .= "<li class=\"tree_dir\">
									<a href=\"".$this->_format_path($c)."\">".$this->_format_filenm($a[0])."</a>\n";
					$tree .= $this->_array2ul($val,$b)."\n";
					$tree .= "</li>\n";
				}else{
					if(basename($val) != 'index.md'){
						$this->dl_arr[] = $this->_format_path($val);
						$tree .= '<li><a href="'.$this->_format_path($val).'" class="tree_file">'.$this->_format_filenm($val).'</a></li>';
					}
				}
			}
		
		if(count($files) > 1){
			$tree .= '</ul>';
		}
		return $tree;
	}

	public function _format_filenm($val){
		$str = basename($val);
		$str = str_replace(".md","",$str);
		preg_match("/(\d+(\s*)-)(\s*)\w/",$str,$matches);
		if($matches){
			$str = ltrim($str,$matches[1]); 
		}
		$str = ucwords(str_replace("-"," ",$str));
		return $str; 
	}
	
	public function _format_path($val){	
		$str = str_replace(".md",".html",$val);
		$str = str_replace('../content/'.$this->vars["info"]["content"],"",$str);
		$str = strtolower(str_replace(" ","-",$str));
		$str = str_replace("&","%26",$str); # Ampersand Safe
		
		$this->path[$str] = $val;
		return $str; 
	}	
	
	function _build_breadcrumb()
	{
		$this->load->helper('url');
		$url = uri_string();
		$a = explode("/",$url);

		if(count($a) == 1){ return ''; }
		$base = '/';
		$new[] = '<a href="'.$base.'index.html">Home</a>';
		$crumb = '<div class="breadcrumb">';
		$total = count($a);
		$i = 1;
		foreach($a as $b){
			$base .= $b.'/';
			
			// Remove any ordering numbers from the breadcrumbs
				preg_match("/(\d+(\s*)-)(\s*)\w/",$b,$matches);
				if($matches){
					$b = ltrim($b,$matches[1]); 
				}

			if($i == $total)
			{
				$selected = str_replace("-"," ",str_replace('.html','',$b));
				if($selected == 'index'){
					#$new[] = $this->vars["info"]["first_link"];
				}else{
					$new[] = ucwords(urldecode($selected));
				}
			}elseif($i == ($total - 1)){
				$new[] = ucwords(urldecode(str_replace("-"," ",$b)));
			}else{
				$str = 	'<a href="'.$base.'index.html">'.ucwords(urldecode(str_replace("-"," ",$b))).'</a>';
				$new[] = $str;
			}
			$i++;
		}

		$crumb .= join($new,'<span class="divider">/</span>'); 
		$crumb .= '	</div>';
		return $crumb;
	}
	
	function _rrmdir($dir) { 
		if (is_dir($dir)) { 
			$objects = scandir($dir); 
			foreach ($objects as $object) { 
				if ($object != "." && $object != "..") { 
					if (filetype($dir."/".$object) == "dir") $this->_rrmdir($dir."/".$object); else unlink($dir."/".$object); 
				} 
			} 
			reset($objects); 
			rmdir($dir); 
		} 
	} 
	
}