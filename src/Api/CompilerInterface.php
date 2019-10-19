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

namespace Rah\Mtxpc\Api;

/**
 * Compiler.
 */
interface CompilerInterface
{
    /**
     * Compiles the given plugin.
     *
     * @param string $path Path to the plugin source directory
     *
     * @return string
     */
    public function compile(string $path): string;

    /**
     * Whether the output file will be compressed.
     *
     * @param bool $compress
     *
     * @return $this
     */
    public function useCompression(bool $compress): CompilerInterface;

    /**
     * Whether compression is enabled.
     *
     * @return bool
     */
    public function isCompressionEnabled(): bool;

    /**
     * Sets version number.
     *
     * @param string $version
     *
     * @return $this
     */
    public function setVersion(string $version): CompilerInterface;

    /**
     * Gets version number.
     *
     * @return string
     */
    public function getVersion(): string;
}
