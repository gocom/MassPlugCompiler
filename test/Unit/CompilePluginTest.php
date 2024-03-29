<?php

declare(strict_types=1);

/*
 * mtxpc - Plugin compiler for Textpattern CMS
 * https://github.com/gocom/MassPlugCompiler
 *
 * Copyright (C) 2022 Jukka Svahn
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

namespace Rah\Mtxpc\Test\Unit;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use Rah\Mtxpc\Compiler;
use Rah\Mtxpc\Converter\PluginDataConverter;
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
    ): void {
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
    ): void {
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
    ): void {
        $compiler = new Compiler();
        $converter = new PluginDataConverter();

        $plugin = $compiler
            ->setVersion('0.1.0')
            ->useCompression(false)
            ->compile($source->getPathname());

        $unpacked = $plugin->getUnpacked();
        $map = $converter->convert($unpacked);

        $this->assertJsonStringEqualsJsonString(
            $expectUnpacked,
            (string) \json_encode($map)
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

    /**
     * @return array<int, array<int, SplFileInfo|string|false>>
     */
    public function provider(): array
    {
        $files = new FilesystemIterator(dirname(__DIR__) . '/fixture');

        $out = [];

        /** @var SplFileInfo $file */
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
