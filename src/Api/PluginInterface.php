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
 * Plugin data.
 */
interface PluginInterface
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
     * Gets author name.
     *
     * @return string|null
     */
    public function getAuthor(): ?string;

    /**
     * Gets author URL.
     *
     * @return string|null
     */
    public function getAuthorUri(): ?string;

    /**
     * Gets description.
     *
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * Gets help.
     *
     * @return string|null
     */
    public function getHelp(): ?string;

    /**
     * Gets raw help.
     *
     * @return string|null
     */
    public function getHelpRaw(): ?string;

    /**
     * Gets plugin code.
     *
     * @return string|null
     */
    public function getCode(): ?string;

    /**
     * Gets plugin type.
     *
     * @return int|null
     */
    public function getType(): ?int;

    /**
     * Gets recommended loading order.
     *
     * @return int|null
     */
    public function getOrder(): ?int;

    /**
     * Gets flags.
     *
     * @return int|null
     */
    public function getFlags(): ?int;

    /**
     * Gets textpack.
     *
     * @return string|null
     */
    public function getTextpack(): ?string;

    /**
     * Whether HTML help is allowed.
     *
     * @return bool|null
     */
    public function isHtmlHelpAllowed(): ?bool;
}
