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
 * mtxpc is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, version 2.
 *
 * mtxpc is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MassPlugCompiler. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Rah\Mtxpc;

final class SourceCollector
{
    /**
     * Gets matching files from the given path.
     *
     * @param string $path
     *
     * @return array
     */
    public function getFiles(string $path, $ns = '', $append = false): array
    {
        $path = \realpath(\rtrim($path, '\\/'));
        $directory = new \RecursiveDirectoryIterator($path);
        $files = new \RecursiveIteratorIterator($directory);
        $collection = [];
        $offset = \strlen($path) + 1;
        $ns = \trim($ns, '\\');
        $ns = $ns ? $ns . '\\' : '';

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $path = \substr($file->getPathname(), $offset, -4);
                $path = \str_replace('/', '\\', $path);
                $code = \file_get_contents($file->getPathname());

                if (\mb_substr($code, 0, 5) === '<?php') {
                    $code = \mb_substr($code, 5);
                }

                if (\mb_substr($code, -2, 2) === '?>') {
                    $code = \mb_substr($code, 0, -2);
                }

                $collection[$ns . $path] = $code;
            }
        }

        return $collection;
    }
}