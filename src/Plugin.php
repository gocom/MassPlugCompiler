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
use Rah\Mtxpc\Converter\PluginDataConverter;

/**
 * Plugin data.
 *
 * @internal
 */
final class Plugin implements PluginInterface, JsonSerializable
{
    /**
     * Name.
     *
     * @var string|null
     */
    private $name;

    /**
     * Version.
     *
     * @var string|null
     */
    private $version;

    /**
     * Author.
     *
     * @var string|null
     */
    private $author;

    /**
     * Author URI.
     *
     * @var string|null
     */
    private $authorUri;

    /**
     * Description.
     *
     * @var string|null
     */
    private $description;

    /**
     * Help contents.
     *
     * @var string|null
     */
    private $help;

    /**
     * Raw Help content.
     *
     * @var string|null
     */
    private $helpRaw;

    /**
     * Code.
     *
     * @var string|null
     */
    private $code;

    /**
     * Checksum.
     *
     * @var string|null
     */
    private $md5;

    /**
     * Type.
     *
     * @var int|null
     */
    private $type;

    /**
     * Order.
     *
     * @var int|null
     */
    private $order;

    /**
     * Flags.
     *
     * @var int|null
     */
    private $flags;

    /**
     * Textpack.
     *
     * @var string|null
     */
    private $textpack;

    /**
     * Whether HTML help is allowed.
     *
     * @var bool|null
     */
    private $isHtmlHelpAllowed;

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(?string $name): PluginInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function setVersion(?string $version): PluginInterface
    {
        $this->version = $version;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthor(?string $author): PluginInterface
    {
        $this->author = $author;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorUri(): ?string
    {
        return $this->authorUri;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthorUri(?string $uri): PluginInterface
    {
        $this->authorUri = $uri;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription(?string $description): PluginInterface
    {
        $this->description = $description;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHelp(): ?string
    {
        return $this->help;
    }

    /**
     * {@inheritdoc}
     */
    public function setHelp(?string $help): PluginInterface
    {
        $this->help = $help;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHelpRaw(): ?string
    {
        return $this->helpRaw;
    }

    /**
     * {@inheritdoc}
     */
    public function setHelpRaw(?string $help): PluginInterface
    {
        $this->helpRaw = $help;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode(?string $code): PluginInterface
    {
        $this->code = $code;

        return $this;
    }

    public function getMd5(): ?string
    {
        return $this->md5;
    }

    public function setMd5(?string $checksum): PluginInterface
    {
        $this->md5 = $checksum;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setType(?int $type): PluginInterface
    {
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder(): ?int
    {
        return $this->order;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrder(?int $order): PluginInterface
    {
        $this->order = $order;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFlags(): ?int
    {
        return $this->flags;
    }

    /**
     * {@inheritdoc}
     */
    public function setFlags(?int $flags): PluginInterface
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTextpack(): ?string
    {
        return $this->textpack;
    }

    /**
     * {@inheritdoc}
     */
    public function setTextpack(?string $contents): PluginInterface
    {
        $this->textpack = $contents;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isHtmlHelpAllowed(): ?bool
    {
        return $this->isHtmlHelpAllowed;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsHtmlHelpAllowed(?bool $isHtmlHelpAllowed): PluginInterface
    {
        $this->isHtmlHelpAllowed = $isHtmlHelpAllowed;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return (new PluginDataConverter())->convert($this);
    }
}
