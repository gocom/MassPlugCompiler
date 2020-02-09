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

namespace Rah\Mtxpc\Converter;

use Rah\Mtxpc\Api\Converter\PluginDataConverterInterface;
use Rah\Mtxpc\Api\PluginInterface;

/**
 * Converts the given plugin package into a data map.
 */
final class PluginDataConverter implements PluginDataConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert(PluginInterface $plugin): array
    {
        return [
            'name' => $plugin->getName(),
            'version' => $plugin->getVersion(),
            'author' => $plugin->getAuthor(),
            'author_uri' => $plugin->getAuthorUri(),
            'description' => $plugin->getDescription(),
            'help' => $plugin->getHelp(),
            'help_raw' => $plugin->getHelpRaw(),
            'code' => $plugin->getCode(),
            'type' => $plugin->getType(),
            'order' => $plugin->getOrder(),
            'flags' => $plugin->getFlags(),
            'textpack' => $plugin->getTextpack(),
            'allow_html_help' => $plugin->isHtmlHelpAllowed(),
            'md5' => $plugin->getMd5(),
        ];
    }
}
