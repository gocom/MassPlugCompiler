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

namespace Rah\Mtxpc;

use JsonSerializable;
use Rah\Mtxpc\Api\PluginInterface;

/**
 * Plugin data.
 *
 * @internal
 */
final class Plugin implements PluginInterface, JsonSerializable
{
    /**
     * Plugin data.
     *
     * @var array
     */
    private $plugin = [];

    /**
     * Constructor.
     *
     * @param array $plugin Plugin data
     */
    public function __construct(array $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->plugin['name'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): ?string
    {
        return $this->plugin['version'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthor(): ?string
    {
        return $this->plugin['author'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorUri(): ?string
    {
        return $this->plugin['author_uri'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): ?string
    {
        return $this->plugin['description'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getHelp(): ?string
    {
        return $this->plugin['help'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getHelpRaw(): ?string
    {
        return $this->plugin['help_raw'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): ?string
    {
        return $this->plugin['code'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): ?int
    {
        return $this->plugin['type'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder(): ?int
    {
        return $this->plugin['order'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFlags(): ?int
    {
        return $this->plugin['flags'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTextpack(): ?string
    {
        return $this->plugin['textpack'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function isHtmlHelpAllowed(): ?bool
    {
        return $this->plugin['allow_html_help'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->plugin;
    }
}
