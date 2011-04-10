<?php

	rah_plugcompile();

	if(@txpinterface == 'admin')
		rah_plugcompile_installer();

/**
	Does installing and sets language strings
*/

	function rah_plugcompile_installer() {
		
		global $event, $prefs, $textarray;
		
		if($event != 'prefs')
			return;
		
		foreach(
			array(
				'rah_plugcompile_active' => 'Use automatic plugin package compiler?',
				'rah_plugcompile_releases' => 'Path to rah_plugin_dev\'s releases directory',
				'rah_plugcompile_packages' => 'Path to packages destination'
			) as $string => $translation
		)
			if(!isset($textarray[$string]))
				$textarray[$string] = $translation;
		
		if(!isset($prefs['rah_plugcompile_active']))
			safe_insert(
				'txp_prefs',
				"prefs_id=1,
				name='rah_plugcompile_active',
				val=0,
				type=1,
				event='admin',
				html='yesnoradio',
				position=102"
			);
		
		foreach(
			array(
				'rah_plugcompile_releases',
				'rah_plugcompile_packages'
			) as $name
		)
			if(!isset($prefs[$name]))
				safe_insert(
					'txp_prefs',
					"prefs_id=1,
					name='{$name}',
					val='',
					type=1,
					event='admin',
					html='text_input',
					position=102"
				);
	}

/**
	The automatic compiler. Goes thru projects directory
	and compiles every release
*/
	
	function rah_plugcompile() {
		
		global $prefs;
		
		/*
			If not active, end here
		*/
		
		if(!isset($prefs['rah_plugcompile_active']) || !$prefs['rah_plugcompile_active'])
			return;
		
		/*
			Clean up the paths, or if not set
			use defaults
		*/
		
		$project =
			$prefs['rah_plugcompile_releases'] ? 
				$prefs['rah_plugcompile_releases']
			: 
				'../plugins/releases'
			;
		
		$cache =
			$prefs['rah_plugcompile_packages'] ? 
				$prefs['rah_plugcompile_packages']
			: 
				'../plugins/packages'
			;
		
		$project = rtrim($project, '\\/') . '/';
		$cache = rtrim($cache, '\\/') . '/';
		
		/*
			Check that the directories are configured
			properly
		*/
		
		foreach(
			array(
				'file_exists',
				'is_dir',
				'is_readable',
				'is_writeable'
			) as $func
		)
			if(($func != 'is_writeable' && !$func($project)) || !$func($cache))
				return;
		
		/*
			Include Textile library
		*/
		
		@include_once txpath.'/lib/classTextile.php';
		
		/*
			File to include in the plugin template
		*/
		
		$files = 
			array(
				'name.txt',
				'author.txt',
				'author_uri.txt',
				'description.txt',
				'help.txt',
				'code.php',
				'type.txt',
				'load_order.txt',
				'flags.txt',
				'textpack.txt'
			);
		
		/*
			Plugin types
		*/
		
		$types = 
			array(
				'Client side',
				'Admin/Client side',
				'Library',
				'Admin only'
			);
		
		/*
			List through the directories
				/releases/
					rah_pluginname/
						0.1
						0.2
						0.3
					rah_pluginname/
						0.5
						0.6
		*/
		
		foreach(
			glob($project.'*_*/'.'*', GLOB_ONLYDIR) as $dir
		) {
			
			/*
				If not readable, end here
			*/

			if(!is_readable($dir))
				continue;
				
			/*
				Get the plugin's name and version from
				the path
			*/
			
			$name = basename(dirname($dir));
			$version = basename($dir);
			
			/*
				Check that the plugin has valid prefix,
				if not skip
			*/
			
			if(!isset($name[3]) || $name[3] !== '_') {
				continue;
			}
			
			/*
				Check if the plugin is already in packages
			*/
			
			$pack['normal'] = $cache . '/' . $name . '_v' . $version . '.txt';
			$pack['zip'] = $cache . '/' . $name . '_v' . $version . '_zip.txt';
			
			if(file_exists($pack['normal']))
				$pack['normal'] = false;
			
			if(!function_exists('gzencode') || file_exists($pack['zip']))
				$pack['zip'] = false;
			
			if(!$pack['zip'] && !$pack['normal'])
				continue;
				
			/*
				Fetch the files
			*/
			
			$plugin = array();
			
			foreach($files as $file) {

				if(!is_readable($dir.'/'.$file) || is_dir($dir.'/'.$file))
					continue;
			
				$data = trim(file_get_contents($dir.'/'.$file));
				
				/*
					Plugin can not be codeless.
					Would just cause fatal errors.
				*/
				
				if($file == 'code.php' && !$data)
					break;
				
				/*
					If source code, do clean up
				*/
				
				if($file == 'code.php') {
					if(substr($data,0,5) == '<?php')
						$data = substr_replace($data,'',0,5);
					
					if(substr($data,-2,2) == '?>')
						$data = rtrim(substr_replace($data,'',-2,2));
				}
				
				/*
					Plugin template expects order instead of
					load_order
				*/
				
				elseif($file == 'load_order.txt') {
					$file = 'order.txt';
				}
				
				/*
					Textile the help file if the help starts with h1.
				*/
				
				elseif($file == 'help.txt') {
					if(!empty($data) && preg_match('/h1(\(.*\))?\./',$data)) {
						$textile = new Textile();
						$data = $textile->TextileThis($data);
					}
				}
				
				/*
					Create the plugin data array
					for the template
				*/
				
				$plugin[substr($file,0,-4)] = $data;
			}
			
			/*
				We didn't get code, end here
			*/
			
			if(!isset($plugin['code']))
				continue;
				
			/*
				Generate md5 checksum, set name and version
				to the data array
			*/
			
			$plugin['md5'] = md5($plugin['code']);
			$plugin['name'] = $name;
			$plugin['version'] = $version;
			
			/*
				Generate the template and save the files
			*/
			
			foreach($pack as $type => $path) {
				
				if(!$path)
					continue;
				
				$package =
					'# Name: '.$plugin['name'].' v'.$plugin['version'].($type == 'zip' ? ' (compressed)' : '').n.
					'# Type: '.(isset($types[$plugin['type']]) ? $types[$plugin['type']] : 'Unknown').' plugin'.n.
					'# '.$plugin['description'].n.
					'# Author: '.$plugin['author'].n.
					'# URL: '.$plugin['author_uri'].n.
					'# Recommended load order: '.$plugin['order'].n.n.
					'# .....................................................................'.n.
					'# This is a plugin for Textpattern - http://textpattern.com/'.n.
					'# To install: textpattern > admin > plugins'.n.
					'# Paste the following text into the \'Install plugin\' box:'.n.
					'# .....................................................................'.n.n.	
					($type == 'zip' ? 
						chunk_split(base64_encode(gzencode(serialize($plugin))), 72)
					:
						chunk_split(base64_encode(serialize($plugin)), 72))
				;
				
				file_put_contents($path, $package);
			}
		}
	}
?>