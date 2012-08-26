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
	 * @var string Path to source directory.
	 */
	
	public $source;
	
	/**
	 * @var string Path to cache directory.
	 */
	
	public $cache;
	
	/**
	 * @var array Plugin data
	 */
	
	protected $plugin;
	
	/**
	 * @var string Current file's location.
	 */
	
	protected $path;
	
	/**
	 * @var array Current filepath's information.
	 */
	
	protected $pathinfo;
	
	/**
	 * @var array Compiled packages
	 */
	
	protected $package = array();
	
	/**
	 * @var array File writing queue
	 */
	
	protected $write_queue = array();
	
	/**
	 * @var string Plugin header meta
	 */
	
	public $header = NULL;
	
	static public $package_cache = NULL;
	static public $classTextile = NULL;
	static public $instance;
	static public $rundir;

	/**
	 * Constructor
	 */
	
	public function __construct() {
	
		if(!self::$rundir) {
			self::$rundir = dirname(__FILE__);
		}
		
		if(self::$classTextile === NULL) {
			
			self::$classTextile = false;
			
			if(!class_exists('Textile') && file_exists(self::$rundir.'/classTextile.php')) {
				@include_once self::$rundir.'/classTextile.php';
			}
			
			if(!class_exists('Textile') && defined('txpath')) {
				@include_once txpath.'/lib/classTextile.php';
			}
			
			if(class_exists('Textile')) {
				self::$classTextile = new Textile();
			}
		}
		
		if($this->header === NULL) {
			$this->header = implode("\n", array(
				'# Name: {name} v{version}',
				'# {description}',
				'# Author: {author}',
				'# URL: {author_uri}',
				'# Recommended load order: {order}',
				'# .....................................................................',
				'# This is a plugin for Textpattern - http://textpattern.com/',
				'# To install: textpattern > admin > plugins',
				'# Paste the following text into the "Install plugin" box:',
				'# .....................................................................',
				'',
				'',
			));
		}
	}
	
	/**
	 * Gets an instance
	 * @param bool $new_instance
	 * @return obj
	 */
	
	static public function instance($new_instance=false) {
		
		if(!self::$instance || $new_instance == true) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		
		return self::$instance;
	}
	
	/**
	 * Sets a property
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
	 * Builds plugin's Textpack
	 * @return nothing
	 */
	
	protected function format_textpack() {
		
		if(!is_readable($this->path))
			return;
		
		if(is_file($this->path)) {
			$this->plugin['textpack'][] = $this->read($this->path);
			return;
		}
	
		if(is_dir($this->path)) {
			foreach($this->read((array) glob($this->glob_escape($this->path) . '/*.textpack', GLOB_NOSORT)) as $file) {
				
				if(strpos($file, '#@language') === false) {
					array_unshift($this->plugin['textpack'], $file);
					continue;
				}
					
				$this->plugin['textpack'][] =  $file;
			}
		}
	}
	
	/**
	 * Builds plugin's meta data from a manifest file
	 * @return nothing
	 */
	
	protected function format_manifest() {
		$file = $this->read($this->path);
		
		if($file) {
			
			try {
				@$manifest = new SimpleXMLElement($file, LIBXML_NOCDATA);
			}
			catch(Exception $exception) {
				return;
			}
			
			foreach($manifest as $name => $value) {
				
				$name = (string) $name;
				
				if(!isset($this->plugin[$name])) {
					continue;
				}
				
				$method = 'format_'.$name;
				
				if(isset($value->attributes()->file) && method_exists($this, $method)) {
					foreach(explode(',', (string) $value->attributes()->file) as $path) {
						$this->path = $this->path(trim($path));
						$this->pathinfo = pathinfo($this->path);
						$this->$method();
					}
				}
				
				else {
					$this->plugin[$name] = (string) $value;
				}
			}
		}
	}

	/**
	 * Formats source code. Removes PHP tags and so on.
	 * @return nothing
	 */
	
	protected function format_code() {
		
		$code = $this->read($this->path);
	
		if(substr(ltrim($code), 0, 5) == '<?php') {
			$code = substr_replace(ltrim($code), '', 0, 5);
		}
		
		if(substr(rtrim($code), -2, 2) == '?>') {
			$code = substr_replace(rtrim($code), '', -2, 2);
		}
		
		$this->plugin['code'][basename($this->path).':'.md5($code)] = $code;
	}
	
	/**
	 * Formats help file
	 * @return nothing
	 */
	
	protected function format_help() {
		
		$this->plugin['help'] = $this->read($this->path);
		
		if(
			$this->pathinfo['extension'] == 'textile' ||
			preg_match('/h1(\(.*\))?\./', $this->plugin['help'])
		) {
		
			if(self::$classTextile) {
				$this->plugin['help'] = self::$classTextile->TextileThis($this->plugin['help']);
			}
			
			else {
				$this->plugin['help_raw'] = $this->plugin['help'];
				$this->plugin['allow_html_help'] = 0;
				$this->plugin['help'] = '';
			}
		}
	}
	
	/**
	 * Forms absolute file path
	 * @param string $path
	 * @return string
	 */
	
	public function path($path) {
		
		if(strpos($path, './') === 0 || strpos($path, '../') === 0) {
			$path = $this->source . '/' . $path;
		}
		
		return $path;
	}
	
	/**
	 * Reads contents of file(s)
	 * @param string|array $file
	 * @return mixed
	 */
	
	public function read($file) {
	
		if(is_array($file)) {
			return array_filter(array_map(array($this, 'read'), $file), 'is_string');
		}
		
		$file = $this->path($file);
	
		if(!$file || !file_exists($file) || !is_file($file) || !is_readable($file)) {
			return false;
		}
		
		return file_get_contents($file);
	}
	
	/**
	 * Packages the plugin
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
			'code' => array(),
			'type' => 0,
			'order' => 5,
			'flags' => '',
			'textpack' => array(),
			'allow_html_help' => 1,
		);
		
		$this->collect_sources();
	
		if(!$this->plugin['code']) {
			return $this;
		}
		
		if(!$this->plugin['version']) {
			$this->plugin['version'] = basename(dirname($this->path));
		}
		
		$this->plugin['code'] = implode("\n", $this->plugin['code']);
		$this->plugin['textpack'] = implode("\n", $this->plugin['textpack']);
		$this->plugin['md5'] = md5($this->plugin['code']);
		
		$header = $this->header;
		
		foreach($this->plugin as $tag => $value) {
			if(strpos($header, '{'.$tag.'}') !== false) {
				$header = str_replace('{'.$tag.'}', (string) $value, $header);
			}
		}
		
		$filename = $this->plugin['name'] . '_v' . $this->plugin['version'];
		$packed = serialize($this->plugin);
		
		$this->package[$filename.'_zip.txt'] = 
			$header . chunk_split(base64_encode(gzencode($packed)), 72);
		
		$this->package[$filename.'.txt'] = 
			$header . chunk_split(base64_encode($packed), 72);
		
		if(!$this->cache($this->plugin['name'], $this->plugin['version'])) {
			$this->write_queue[] = $filename.'.txt';
			$this->write_queue[] = $filename.'_zip.txt';
		}
		
		return $this;
	}
	
	/**
	 * Writes current packages to the cache directory
	 * @return obj
	 */

	public function write() {
	
		if(!file_exists($this->cache) || !is_dir($this->cache) || !is_writable($this->cache))
			return $this;
		
		foreach($this->write_queue as $name) {
			file_put_contents(
				$this->cache . '/' . $name, $this->package[$name]
			);
		}
		
		return $this;
	}
	
	/**
	 * Gets last compiled installer
	 * @param int $offset
	 * @return string Installer package
	 */
	
	public function get($offset=self::GET_NORMAL) {
		return implode('', array_slice($this->package, $offset, 1));
	}

	/**
	 * Collects files from a directory
	 * @return nothing
	 */
	
	protected function collect_sources() {

		foreach((array) glob($this->glob_escape($this->source).'/*', GLOB_NOSORT) as $path) {
			
			$this->path = $path;
			$this->pathinfo = pathinfo($path);
			
			if(!isset($this->plugin[$this->pathinfo['filename']])) {
				
				if($this->pathinfo['filename'] == 'manifest') {
					$this->format_manifest();
				}
				
				if($this->pathinfo['filename'] == 'textpacks') {
					$this->format_textpack();
				}
				
				if(!isset($this->pathinfo['extension'])) {
					continue;
				}
				
				if($this->pathinfo['extension'] == 'php') {
					$this->format_code();
				}
			
				continue;
			}
			
			$method = 'format_'. $this->pathinfo['filename'];
			
			if(method_exists($this, $method)) {
				$this->$method();
			}
			
			else {
				$this->plugin[$this->pathinfo['filename']] = $this->read($path);
			}
		}
		
	}
	
	/**
	 * Checks whether compiled installer file is located in the cache dir
	 * @param string $name
	 * @param string $version
	 * @return bool|obj
	 */
	
	public function cache($name=NULL, $version=NULL) {
		
		if(!file_exists($this->cache) || !is_dir($this->cache) || !is_readable($this->cache))
			return false;
		
		if(self::$package_cache === NULL) {

			self::$package_cache = array();

			foreach((array) glob($this->glob_escape($this->cache) . '/*.txt', GLOB_NOSORT) as $f) {
				if($f && is_file($f)) {
					
					$n = explode('_', basename($f, '.txt'));
					
					if(end($n) == 'zip') {
						unset($n[count($n)-1]);
					}
					
					self::$package_cache[implode('_', array_slice($n, 0, -1))][ltrim(end($n), 'v')] = true;
				}
			}
		}
		
		if($name !== NULL && $version !== NULL) {
			return isset(self::$package_cache[$name][$version]);
		}
		
		return $this;
	}
	
	/**
	 * Escapes glob wildcard characters
	 * @param string $filename
	 * @return string
	 */

	public function glob_escape($filename) {
		return preg_replace('/(\*|\?|\[)/', '[$1]', $filename);
	}
}

?>