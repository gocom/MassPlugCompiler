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

namespace Rah\Mtxpc\Test\Integration;

use PHPUnit\Framework\TestCase;

final class CommandLineTest extends TestCase
{
    public function testHelp(): void
    {
        $this->assertStringContainsString(
            'Options',
            `bin/mtxpc -h`
        );
    }

    public function testCompressed(): void
    {
        $result = `bin/mtxpc -c test/fixture/abc_plugin`;

        $expect = \file_get_contents('test/fixture/abc_plugin/expect/compressed.txt');

        $this->assertEquals($expect, $result);
    }

    public function testUncompressed(): void
    {
        $result = `bin/mtxpc test/fixture/abc_plugin`;

        $expect = \file_get_contents('test/fixture/abc_plugin/expect/uncompressed.txt');

        $this->assertEquals($expect, $result);
    }

    public function testCompressedOutputDirectory(): void
    {
        $result = `bin/mtxpc -c --outdir=.test-result/ test/fixture/abc_plugin`;

        $this->assertStringContainsString(
            'abc_plugin_v0.1.0_zip.txt',
            $result
        );

        $this->assertFileExists('.test-result/abc_plugin_v0.1.0_zip.txt');

        $this->assertFileEquals(
            'test/fixture/abc_plugin/expect/compressed.txt',
            '.test-result/abc_plugin_v0.1.0_zip.txt'
        );

        \unlink('.test-result/abc_plugin_v0.1.0_zip.txt');
    }

    public function testUncompressedOutputDirectory(): void
    {
        $result = `bin/mtxpc --outdir=.test-result/ test/fixture/abc_plugin`;

        $this->assertStringContainsString(
            'abc_plugin_v0.1.0.txt',
            $result
        );

        $this->assertFileExists('.test-result/abc_plugin_v0.1.0.txt');

        $this->assertFileEquals(
            'test/fixture/abc_plugin/expect/uncompressed.txt',
            '.test-result/abc_plugin_v0.1.0.txt'
        );

        \unlink('.test-result/abc_plugin_v0.1.0.txt');
    }
}
