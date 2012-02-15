<?php

/**
 * Implementation example. Searches plugin directories from parent directory
 * and tries to write compiled installer packages to "../../packages" directory.
 *
 * @package rah_plugcompiler
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
 */
 
 	include dirname(dirname(__FILE__)) . '/rah_plugcompile.php';
 
 	rah_plugcompile::instance()
 		->set('cache', dirname(dirname(__FILE__)) . '/packages');

	foreach(
		array_merge(
			glob(dirname(dirname(dirname(__FILE__))).'/*'),
			array()
		) as $path
	) {
		rah_plugcompile::instance()
			->set('source', $path)
			->package()
			->write();
	}

?>