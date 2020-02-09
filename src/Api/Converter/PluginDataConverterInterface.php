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

namespace Rah\Mtxpc\Api\Converter;

use Rah\Mtxpc\Api\PluginInterface;

/**
 * Converts the given plugin package into a data map.
 */
interface PluginDataConverterInterface
{
    /**
     * Converts the given plugin into a data map.
     *
     * @param PluginInterface $plugin
     *
     * @return array<string, mixed>
     */
    public function convert(PluginInterface $plugin): array;
}
