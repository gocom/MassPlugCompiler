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
        string $expectUncompressed,
        string $expectUnpacked
    ) {
        $compiler = new Compiler();

        $plugin = $compiler
            ->setVersion('0.1.0')
            ->compile($source->getPathname());

        $this->assertEquals($expectCompressed, $plugin->getInstaller());

        $this->assertEquals($source->getBasename(), $plugin->getName());

        $this->assertEquals('0.1.0', $plugin->getVersion());
    }

    /**
     * @dataProvider provider
     */
    public function testUncompressed(
        SplFileInfo $source,
        string $expectCompressed,
        string $expectUncompressed,
        string $expectUnpacked
    ) {
        $compiler = new Compiler();

        $plugin = $compiler
            ->setVersion('0.1.0')
            ->useCompression(false)
            ->compile($source->getPathname());

        $this->assertEquals($expectUncompressed, $plugin->getInstaller());

        $this->assertEquals($source->getBasename(), $plugin->getName());

        $this->assertEquals('0.1.0', $plugin->getVersion());
    }

    /**
     * @dataProvider provider
     */
    public function testUnpacked(
        SplFileInfo $source,
        string $expectCompressed,
        string $expectUncompressed,
        string $expectUnpacked
    ) {
        $compiler = new Compiler();

        $plugin = $compiler
            ->setVersion('0.1.0')
            ->useCompression(false)
            ->compile($source->getPathname());

        $unpacked = $plugin->getUnpacked();

        $this->assertJsonStringEqualsJsonString(
            $expectUnpacked,
            \json_encode($unpacked)
        );

        $this->assertTrue(\is_string($unpacked->getName()));
        $this->assertTrue(\is_string($unpacked->getVersion()));
        $this->assertTrue(\is_string($unpacked->getAuthor()));
        $this->assertTrue(\is_string($unpacked->getAuthorUri()));
        $this->assertTrue(\is_string($unpacked->getDescription()));
        $this->assertTrue(\is_string($unpacked->getCode()));
        $this->assertTrue(\is_string($unpacked->getHelp()));
        $this->assertTrue(\is_string($unpacked->getHelpRaw()));
        $this->assertTrue(\is_int($unpacked->getType()));
        $this->assertTrue(\is_int($unpacked->getOrder()));
        $this->assertTrue(\is_int($unpacked->getFlags()));
        $this->assertTrue(\is_string($unpacked->getTextpack()));
        $this->assertTrue(\is_bool($unpacked->isHtmlHelpAllowed()));
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
                    \file_get_contents($file->getPathname() . '/expect/uncompressed.txt'),
                    \file_get_contents($file->getPathname() . '/expect/unpacked.json'),
                ];
            }
        }

        return $out;
    }
}
