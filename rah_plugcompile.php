<?php

/**
 * MassPlugCompiler/rah_plugcompiler - Compiles Textpattern's plugin installer packages from sources.
 *
 * @author Jukka Svahn
 * @copyright (c) 2011 Jukka Svahn
 * @license GNU GPLv2
 * @link https://github.com/gocom/MassPlugCompiler
 *
 * Copyright (c) 2011 Jukka Svahn <http://rahforum.biz>
 * Licensed under GNU Genral Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Requires PHP 5.2.0
 *
 * <code>
 *		rah_plugcompile::instance()
 *			->set('cache', '/path/to');
 *			->set('source', '/path/to')
 *			->package()
 *			->get();
 * </code>
 */

class rah_plugcompile {

	const GET_NORMAL = -1;
	const GET_COMPRESSED = -2;
	const INSTANCE_NEW = true;

	/**
	 * @var string Source directory.
	 */
	
	public $source;
	
	/**
	 * @var string Cache directory.
	 */
	
	public $cache;
	
	/**
	 * @var bool Compress
	 */
	
	public $compress = false;
	
	/**
	 * @var
	 */
	
	public $output = 'package';
	
	/**
	 * @var array Plugin data
	 */
	
	protected $plugin;
	
	/**
	 * @var obj Manifest file
	 */
	
	protected $manifest;
	
	/**
	 * @var string $path Details of the current file.
	 */
	
	protected $path;
	
	/**
	 * @var array $pathinfo
	 */
	
	protected $pathinfo;
	
	/**
	 * @var array Packages
	 */
	
	protected $package = array();
	
	static public $package_cache = NULL;
	static public $classTextile = NULL;
	static public $plugin_types = array();
	static public $instance;
	static public $rundir;
	
	/**
	 * Constructor
	 */
	
	public function __construct() {
		
		if(self::$classTextile === NULL) {
			
			if(!class_exists('Textile')) {
				@include_once txpath.'/lib/classTextile.php';
			}
			
			self::$classTextile = new Textile();
		}
		
		if(!self::$plugin_types) {
			self::$plugin_types = array(
				'Client side',
				'Admin/Client side',
				'Library',
				'Admin only',
			);
		}
		
		if(!self::$rundir) {
			self::$rundir = dirname(__FILE__);
		}
	}
	
	/**
	 * Get instance
	 * @param bool $new_instance
	 */
	
	static public function instance($new_instance=false) {
		
		if(!self::$instance || $new_instance == true) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		
		return self::$instance;
	}
	
	/**
	 * Set a property
	 * @param string $name
	 * @param mixed $value
	 * @return obj
	 */
	
	public function set($name, $value) {
		
		if(!property_exists($this, $name)) {
			return $this;
		}
		
		$this->$name = $value;
		return $this;
	}
	
	/**
	 * Build plugin's Textpack
	 * @return nothing
	 */
	
	private function format_textpack() {
		
		if(!is_readable($this->path))
			return;
	
		if(is_dir($this->path)) {
			foreach(glob($this->glob_escape($this->path) . '/*.textpack', GLOB_NOSORT) as $file) {
				$this->plugin['textpack'] = $this->read($file)."\n";
			}
		}
		
		elseif(is_file($this->path)) {
			$this->plugin['textpack'] = $this->read($this->path);
		}
	}
	
	/**
	 * Build plugin's meta data from manifest file
	 * @return nothing
	 */
	
	private function format_manifest() {
		$file = $this->read($this->path);
		
		if($file) {
			$this->manifest = new SimpleXMLElement($file, LIBXML_NOCDATA);
			
			foreach($this->manifest as $name => $value) {
				
				$name = (string) $name;
				
				if(!isset($this->plugin[$name]))	
					continue;
				
				$method = 'format_'.$name;
				
				if(isset($value->attributes()->file) && method_exists($this, $method)) {
					$this->path = (string) $value->attributes()->file;
					$this->pathinfo = pathinfo($this->path);
					$this->$method();
				}
				
				else $this->plugin[$name] = (string) $value;
			}
		}
	}

	/**
	 * Format source code. Removes PHP tags and so on.
	 * @return nothing
	 */
	
	private function format_code() {
		
		$this->plugin['code'] = $this->read($this->path);
	
		if(substr($this->plugin['code'], 0, 5) == '<?php')
			$this->plugin['code'] = substr_replace($this->plugin['code'], '', 0, 5);
		
		if(substr($this->plugin['code'], -2, 2) == '?>')
			$this->plugin['code'] = rtrim(substr_replace($this->plugin['code'], '', -2, 2));
	}
	
	/**
	 * Format help file
	 * @return nothing
	 */
	
	private function format_help() {
		
		$this->plugin['help'] = $this->read($this->path);
		
		if(
			$this->pathinfo['extension'] == 'textile' ||
			preg_match('/h1(\(.*\))?\./', $this->plugin['help'])
		) {
			$this->plugin['help'] = self::$classTextile->TextileThis($this->plugin['help']);
		}
	}
	
	/**
	 * Source code
	 * @param string $file
	 * @return string
	 */
	
	private function read($file) {
		
		if(strpos($file, './') === 0 || strpos($file, '../') === 0)
			$file = $this->source . '/' . $file;
	
		if(!file_exists($file) || !is_file($file) || !is_readable($file))
			return false;
		
		return file_get_contents($file);
	}
	
	/**
	 * Package code
	 * @return obj
	 */
	
	public function package() {
		
		$this->plugin = array(
			'name' => '',
			'version' => '0.1',
			'author' => '',
			'author_uri' => '',
			'description' => '',
			'help' => '',
			'code' => '',
			'type' => 0,
			'order' => 5,
			'load_order' => NULL,
			'flags' => '',
			'textpack' => '',
		);
		
		$this->collect_sources();
	
		if(!$this->plugin['code'])
			return $this;
		
		$header = $this->read(self::$rundir.'/header.txt');
		
		foreach($this->plugin as $tag => $value)
			$header = str_replace('{'.$tag.'}', $value, $header);
		
		if(!$this->plugin['name']) {
			$this->plugin['name'] = basename(dirname(dirname($this->path)));
		}
		
		if(!$this->plugin['version']) {
			$this->plugin['version'] = basename(dirname($this->path));
		}
		
		if($this->cache($this->plugin['name'], $this->plugin['version']))
			return $this;
		
		if($this->plugin['load_order'] !== NULL) {
			$this->plugin['order'] = $this->plugin['load_order'];
			unset($this->plugin['load_order']);
		}
		
		$this->plugin['md5'] = md5($this->plugin['code']);
		
		$filename = $this->plugin['name'] . '_v' . $this->plugin['version'];
		
		$this->package[$filename.'_zip.txt'] = 
			$header . chunk_split(base64_encode(gzencode(serialize($this->plugin))), 72);
		
		$this->package[$filename.'.txt'] = 
			$header . chunk_split(base64_encode(serialize($this->plugin)), 72);
			
		return $this;
	}
	
	/**
	 * Write current packages to the cache directory
	 * @return obj
	 */

	public function write() {
	
		if(!file_exists($this->cache) || !is_dir($this->cache) || !is_writable($this->cache))
			return $this;
		
		foreach($this->package as $name => $package) {
			file_put_contents(
				$this->cache . '/' . $name, $package
			);
		}
		
		return $this;
	}
	
	/**
	 * Get last compiled installer
	 * @param int $offset
	 * @return string Installer package
	 */
	
	public function get($offset=self::GET_NORMAL) {
		return implode('', array_slice($this->package, $offset, 1));
	}

	/**
	 * Collect files from a directory
	 * @return nothing
	 */
	
	private function collect_sources() {

		foreach(glob($this->glob_escape($this->source).'/*', GLOB_NOSORT) as $path) {
			
			$this->path = $path;
			$this->pathinfo = pathinfo($path);
			
			if(!isset($this->plugin[$this->pathinfo['filename']])) {
				
				if($this->pathinfo['filename'] == 'manifest') {
					$this->format_manifest();
				}
				
				if(!isset($this->pathinfo['extension']))
					continue;
			
				if($this->pathinfo['extension'] == 'php') {
					$this->format_code();
				}
				
				if($this->pathinfo['extension'] == 'textpack' && ($r = $this->read($path))) {
					$this->plugin['textpack'] .= $r . "\n";
				}
			
				continue;
			}
			
			$method = 'format_'. $this->pathinfo['filename'];
			
			if(method_exists($this, $method)) {
				$this->$method();
			}
		
			$this->plugin[$this->pathinfo['filename']] = $this->read($path);
		}
		
	}
	
	/**
	 * Check whether compiled installer file is located in the cache dir
	 * @param string $name
	 * @param string $version
	 * @return bool
	 */
	
	private function cache($name, $version) {
		
		if(!file_exists($this->cache) || !is_dir($this->cache) || !is_readable($this->cache))
			return false;
		
		if(self::$package_cache === NULL) {

			self::$package_cache = array();

			foreach(glob($this->glob_escape($this->cache) . '/*', GLOB_NOSORT) as $f) {
				$n = explode('_v', basename($f));
				self::$package_cache[$n[0]][current(explode('_', end($n)))] = true;
			}
		}
		
		return isset(self::$package_cache[$name][$version]);
	}
	
	/**
	 * Escape glob wildcard characters
	 * @param string $filename
	 * @return string
	 */

	private function glob_escape($filename) {
		return preg_replace('/(\*|\?|\[)/', '[$1]', $filename);
	}
}

?>