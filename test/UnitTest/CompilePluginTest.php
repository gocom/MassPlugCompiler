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

use PHPUnit\Framework\TestCase;
use Rah\Mtxpc\Compiler;
use SplFileInfo;

final class CompilePluginTest extends TestCase
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

        $this->assertIsString($unpacked->getName());
        $this->assertIsString($unpacked->getVersion());
        $this->assertIsString($unpacked->getAuthor());
        $this->assertIsString($unpacked->getAuthorUri());
        $this->assertIsString($unpacked->getDescription());
        $this->assertIsString($unpacked->getCode());
        $this->assertIsString($unpacked->getHelp());
        $this->assertIsString($unpacked->getHelpRaw());
        $this->assertIsInt($unpacked->getType());
        $this->assertIsInt($unpacked->getOrder());
        $this->assertIsInt($unpacked->getFlags());
        $this->assertIsString($unpacked->getTextpack());
        $this->assertIsBool($unpacked->isHtmlHelpAllowed());
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
