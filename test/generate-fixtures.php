<?php

declare(strict_types=1);

/*
 * mtxpc - Plugin compiler for Textpattern CMS
 * https://github.com/gocom/MassPlugCompiler
 *
 * Copyright (C) 2019 Jukka Svahn
 *
 * This file is part of mtxpc.
 *
 * txpmpc is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, version 2.
 *
 * mtxpc is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with mtxpc. If not, see <http://www.gnu.org/licenses/>.
 */

require \dirname(__DIR__) . '/vendor/autoload.php';

$files = new \FilesystemIterator(__DIR__ . '/fixture/');

/** @var \SplFileInfo $file */
foreach ($files as $file) {
    if (!$file->isDir()) {
        continue;
    }

    $expect = $file->getPathname() . '/expect';

    if (!\is_dir($expect)) {
        \mkdir($expect, 0775, true);
    }

    $plugin = (new \Rah\Mtxpc\Compiler())
        ->setVersion('0.1.0')
        ->useCompression(true)
        ->compile($file->getPathname());

    \file_put_contents($expect . '/compressed.txt', $plugin->getInstaller());

    \file_put_contents($expect . '/unpacked.json', \json_encode($plugin->getUnpacked(), \JSON_PRETTY_PRINT));

    $plugin = (new \Rah\Mtxpc\Compiler())
        ->setVersion('0.1.0')
        ->useCompression(false)
        ->compile($file->getPathname());

    \file_put_contents($expect . '/uncompressed.txt', $plugin->getInstaller());
}
