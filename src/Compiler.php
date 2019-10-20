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

use Rah\Mtxpc\Api\CompilerInterface;
use Rah\Mtxpc\Api\PackageInterface;

/**
 * Plugin compiler.
 *
 * ```
 * use Rah\Mtxpc\Compiler;
 *
 * $compiler = new Compiler();
 *
 * $plugin = $compiler->compile('/path/to/the/plugin/source');
 *
 * echo $plugin->getInstaller();
 * ```
 */
final class Compiler implements CompilerInterface
{
    /**
     * Path to source directory.
     *
     * @var string
     */
    private $source;

    /**
     * Plugin data.
     *
     * @var array
     */
    private $plugin;

    /**
     * Current file.
     *
     * @var \SplFileInfo
     */
    private $currentFile;

    /**
     * Version number.
     *
     * @var string
     */
    private $version;

    /**
     * Whether the output file will be compressed.
     *
     * @var bool
     */
    private $compress = true;

    /**
     * {@inheritdoc}
     */
    public function useCompression(bool $compress): CompilerInterface
    {
        $this->compress = $compress;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isCompressionEnabled(): bool
    {
        return $this->compress;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $path): PackageInterface
    {
        $this->setSourcePath($path);
        $this->plugin = $this->getPluginTemplate();
        $files = new \FilesystemIterator($this->getSourcePath());

        /** @var DirectoryIterator $file */
        foreach ($files as $file) {
            $this->setCurrentFile($file);

            if ($this->getCurrentFile()->getExtension() === 'php') {
                $this->process('code');
                continue;
            }

            $this->process();
        }

        $this->plugin['help'] = \implode("\n\n", $this->plugin['help']);
        $this->plugin['help_raw'] = \implode("\n\n", $this->plugin['help_raw']);
        $this->plugin['code'] = \implode("\n", $this->plugin['code']);
        $this->plugin['textpack'] = \implode("\n", $this->plugin['textpack']);
        $this->plugin['md5'] = \md5($this->plugin['code']);

        $header = $this->getTemplate('header');
        $variables = [];

        foreach ($this->plugin as $name => $value) {
            if (\is_scalar($value)) {
                $variables['{' . $name . '}'] = (string)$value;
            }
        }

        $header = \strtr($header, $variables) . "\n";

        $packed = \serialize($this->plugin);

        if ($this->isCompressionEnabled()) {
            $packed = \gzencode($packed);
        }

        return new Package(
            $this->plugin,
            $header . \chunk_split(\base64_encode($packed), 72, "\n")
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setVersion(string $version): CompilerInterface
    {
        $this->version = $version;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): string
    {
        return $this->version ?? '0.0.0';
    }

    /**
     * Process the current file as the given field.
     *
     * @param string|null $field
     */
    private function process(?string $field = null): void
    {
        if ($field === null) {
            $field = $this->getCurrentFile()->getBasename('.' . $this->getCurrentFile()->getExtension());
        }

        $method = 'add' . \ucfirst(\mb_strtolower($field));

        if (\method_exists($this, $method)) {
            $this->$method();

            return;
        }

        if (\substr($method, -1) === 's') {
            $method = \substr($method, 0, -1);
        }

        if (\method_exists($this, $method)) {
            $this->$method();
        }
    }

    /**
     * Set the current file.
     *
     * @param string $path
     */
    private function setCurrentFile(\SplFileInfo $file): void
    {
        $this->currentFile = $file;
    }

    /**
     * Gets the current file.
     *
     * @return \SplFileInfo|null
     */
    private function getCurrentFile(): ?\SplFileInfo
    {
        return $this->currentFile;
    }

    /**
     * Gets the current file contents.
     *
     * @return string|null
     */
    private function getCurrentFileContent(): ?string
    {
        return $this->getCurrentFile()
            ? $this->read($this->getCurrentFile()->getPathname())
            : null;
    }

    /**
     * Gets a template contents.
     *
     * @return string|null
     */
    private function getTemplate(string $name): ?string
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR .
            $name . '.txt';

        if (\file_exists($file) && \is_readable($file)) {
            return \file_get_contents($file);
        }

        return null;
    }

    /**
     * Adds plugin textpacks to the plugin installer.
     */
    private function addTextpack(): void
    {
        if (!$this->getCurrentFile()->isReadable()) {
            return;
        }

        if ($this->getCurrentFile()->isFile()) {
            $this->plugin['textpack'][] = $this->getCurrentFileContent();

            return;
        }

        if (!$this->getCurrentFile()->isDir()) {
            return;
        }

        $files = new \FilesystemIterator($this->getCurrentFile()->getPathname());
        $textpacks = [];

        /** @var DirectoryIterator $file */
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'textpack') {
                $textpacks[$file->getBasename('.' . $file->getExtension())] = $this->read($file->getPathname());
            }
        }

        \ksort($textpacks);

        foreach ($textpacks as $language => $content) {
            if (strpos($content, '#@language') === false) {
                array_unshift($this->plugin['textpack'], $content);
                continue;
            }

            $this->plugin['textpack'][] =  $content;
        }
    }

    /**
     * Adds manifest to the plugin installer.
     */
    private function addManifest(): void
    {
        $contents = $this->getCurrentFileContent();

        if (!$contents) {
            return;
        }

        $manifest = \json_decode($contents, true);

        foreach ($manifest as $name => $value) {
            if (isset($value['file'])) {
                foreach ((array) $value['file'] as $path) {
                    $this->setCurrentFile(new \SplFileInfo($this->getAbsolutePath($path)));
                    $this->process($name);
                }
            } elseif (\is_scalar($value)) {
                $this->plugin[$name] = $value;
            }
        }
    }

    /**
     * Adds source code to the plugin installer.
     */
    private function addCode(): void
    {
        $code = \trim($this->getCurrentFileContent());

        if (\substr($code, 0, 5) === '<?php') {
            $code = \substr($code, 5);
        }

        if (\substr($code, -2, 2) === '?>') {
            $code = \substr($code, 0, -2);
        }

        $this->plugin['code'][] = $code;
    }

    /**
     * Adds help to the plugin installer.
     */
    private function addHelp(): void
    {
        $help = \trim($this->getCurrentFileContent());

        if ($this->getCurrentFile()->getExtension() === 'textile' ||
            \preg_match('/h1(\(.*\))?\./', $help)
        ) {
            $this->plugin['help_raw'][] = $help;
            $this->plugin['allow_html_help'] = false;
            $this->plugin['help'] = [];
        } else {
            $this->plugin['help_raw'] = [];
            $this->plugin['allow_html_help'] = true;
            $this->plugin['help'][] = $help;
        }
    }

    /**
     * Sets source path.
     */
    private function setSourcePath(string $string): void
    {
        $this->source = \realpath($string);
    }

    /**
     * Gets source path.
     *
     * @return string
     */
    private function getSourcePath(): string
    {
        return $this->source;
    }

    /**
     * Gets an absolute path from the given filename.
     *
     * @param string $path
     *
     * @return string
     */
    private function getAbsolutePath(string $path): string
    {
        $path = \preg_replace('/^\.{0,2}\//', '', $path);

        return $this->getSourcePath() . \DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Reads a file.
     *
     * @param  string $file
     *
     * @return string
     */
    private function read(string $file): string
    {
        $contents = '';

        if ($file && \file_exists($file) && \is_file($file) && \is_readable($file)) {
            $contents = \file_get_contents($file);
        }

        return $contents;
    }

    /**
     * Gets plugin template.
     *
     * @return array
     */
    private function getPluginTemplate(): array
    {
        return [
            'name' => \basename($this->getSourcePath()),
            'version' => $this->getVersion(),
            'author' => '',
            'author_uri' => '',
            'description' => '',
            'help' => [],
            'help_raw' => [],
            'code' => [],
            'type' => 0,
            'order' => 5,
            'flags' => '',
            'textpack' => [],
            'allow_html_help' => true,
        ];
    }
}
