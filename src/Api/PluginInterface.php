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
     * Sets the name of the plugin.
     *
     * @param string|null $name
     *
     * @return $this
     */
    public function setName(?string $name): self;

    /**
     * Gets the version number of the plugin.
     *
     * @return string|null
     */
    public function getVersion(): ?string;

    /**
     * Sets the version number of the plugin.
     *
     * @param string|null $version
     *
     * @return $this
     */
    public function setVersion(?string $version): self;

    /**
     * Gets author name.
     *
     * @return string|null
     */
    public function getAuthor(): ?string;

    /**
     * Sets author name.
     *
     * @param string|null $author
     *
     * @return $this
     */
    public function setAuthor(?string $author): self;

    /**
     * Gets author URL.
     *
     * @return string|null
     */
    public function getAuthorUri(): ?string;

    /**
     * Sets author URL.
     *
     * @param string|null $uri
     *
     * @return $this
     */
    public function setAuthorUri(?string $uri): self;

    /**
     * Gets description.
     *
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * Sets description.
     *
     * @param string|null $description
     *
     * @return $this
     */
    public function setDescription(?string $description): self;

    /**
     * Gets help contents.
     *
     * @return string|null
     */
    public function getHelp(): ?string;

    /**
     * Sets help contents.
     *
     * @param string|null $help
     *
     * @return $this
     */
    public function setHelp(?string $help): self;

    /**
     * Gets raw help contents.
     *
     * @return string|null
     */
    public function getHelpRaw(): ?string;

    /**
     * Sets raw help contents.
     *
     * @param string|null $help
     *
     * @return $this
     */
    public function setHelpRaw(?string $help): self;

    /**
     * Gets plugin code.
     *
     * @return string|null
     */
    public function getCode(): ?string;

    /**
     * Sets plugin code.
     *
     * @param string|null $code
     *
     * @return $this
     */
    public function setCode(?string $code): self;

    /**
     * Gets checksum.
     *
     * @return string|null
     */
    public function getMd5(): ?string;

    /**
     * Sets checksum.
     *
     * @param string|null $checksum
     *
     * @return $this
     */
    public function setMd5(?string $checksum): self;

    /**
     * Gets plugin type.
     *
     * @return int|null
     */
    public function getType(): ?int;

    /**
     * Sets plugin type.
     *
     * @param int|null $type
     *
     * @return $this
     */
    public function setType(?int $type): self;

    /**
     * Gets recommended loading order.
     *
     * @return int|null
     */
    public function getOrder(): ?int;

    /**
     * Sets recommended loading order.
     *
     * @param int|null $order
     *
     * @return $this
     */
    public function setOrder(?int $order): self;

    /**
     * Gets flags.
     *
     * @return int|null
     */
    public function getFlags(): ?int;

    /**
     * Sets flags.
     *
     * @param int|null $flags
     *
     * @return $this
     */
    public function setFlags(?int $flags): self;

    /**
     * Gets textpack translations.
     *
     * @return string|null
     */
    public function getTextpack(): ?string;

    /**
     * Sets textpack translations.
     *
     * @param string|null $contents
     *
     * @return $this
     */
    public function setTextpack(?string $contents): self;

    /**
     * Whether HTML help is allowed.
     *
     * @return bool|null
     */
    public function isHtmlHelpAllowed(): ?bool;

    /**
     * Sets whether HTML help is allowed.
     *
     * @param bool|null $status
     *
     * @return $this
     */
    public function setIsHtmlHelpAllowed(?bool $status): self;
}
