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

namespace Rah\Mtxpc\Test\UnitTest;

use Rah\Mtxpc\Compiler;
use SplFileInfo;

class CompilePluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     */
    public function testCompressed(
        SplFileInfo $source,
        string $expectCompressed,
        string $expectUncompressed
    ) {
        $compiler = new Compiler();

        $compressed = $compiler
            ->compile($source->getPathname());

        $this->assertEquals($expectCompressed, $compressed);
    }

    /**
     * @dataProvider provider
     */
    public function testUncompressed(
        SplFileInfo $source,
        string $expectCompressed,
        string $expectUncompressed
    ) {
        $compiler = new Compiler();

        $uncompressed = $compiler
            ->useCompression(false)
            ->compile($source->getPathname());

        $this->assertEquals($expectUncompressed, $uncompressed);
    }

    public function provider()
    {
        $files = new \FilesystemIterator(dirname(__DIR__) . '/fixture');

        $out = [];

        foreach ($files as $file) {
            if ($file->isDir()) {
                $out[] = [
                    $file,
                    \file_get_contents($file->getPathname() . '/expect/compressed.txt'),
                    \file_get_contents($file->getPathname() . '/expect/uncompressed.txt')
                ];
            }
        }

        return $out;
    }
}
