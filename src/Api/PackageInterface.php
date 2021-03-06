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
 * Packaged installer.
 */
interface PackageInterface
{
    /**
     * Gets the name of the plugin.
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Gets the version number of the plugin.
     *
     * @return string|null
     */
    public function getVersion(): ?string;

    /**
     * Gets the package installer code.
     *
     * @return string
     */
    public function getInstaller(): string;

    /**
     * Gets unpacked plugin contents.
     *
     * @return PluginInterface
     */
    public function getUnpacked(): PluginInterface;
}
