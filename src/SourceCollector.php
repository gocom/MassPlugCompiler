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

/**
 * Collects source files.
 *
 * @internal
 */
final class SourceCollector
{
    /**
     * Gets matching files from the given path.
     *
     * @param string $path
     * @param string $ns
     *
     * @return array<string, string>
     */
    public function getFiles(string $path, string $ns = ''): array
    {
        $path = (string) \realpath(\rtrim($path, '\\/'));
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
                $code = (string) \file_get_contents($file->getPathname());
                $code = \preg_replace('/^\s*<\?php|\?>\s*$/', '', $code);

                $collection[$ns . $path] = (string) $code;
            }
        }

        return $collection;
    }
}
