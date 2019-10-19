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

namespace Rah\Mtxpc;

use Rah\Mtxpc\Api\PackageInterface;

/**
 * Packaged plugin installer.
 *
 * @internal
 */
final class Package implements PackageInterface
{
    /**
     * Plugin data.
     *
     * @var array
     */
    private $plugin;

    /**
     * Installer.
     *
     * @var string
     */
    private $installer;

    /**
     * Constructor.
     *
     * @param array  $plugin
     * @param string $installer
     */
    public function __construct(
        array $plugin,
        string $installer
    ) {
        $this->plugin = $plugin;
        $this->installer = $installer;
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
    public function getInstaller(): string
    {
        return $this->installer;
    }
}
