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
use Rah\Mtxpc\Packer\CompressedPacker;
use Rah\Mtxpc\Packer\Packer;
use SplFileInfo;

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
     * @var SplFileInfo
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

        if ($this->version !== null) {
            $this->plugin['version'] = $this->getVersion();
        }

        $this->plugin['help'] = \implode("\n\n", $this->plugin['help']);
        $this->plugin['help_raw'] = \implode("\n\n", $this->plugin['help_raw']);
        $this->plugin['code'] = \implode("\n", $this->plugin['code']);
        $this->plugin['textpack'] = \implode("\n", $this->plugin['textpack']);
        $this->plugin['md5'] = \md5($this->plugin['code']);

        $header = $this->getTemplate('header', $this->plugin) . "\n";

        $packer = $this->isCompressionEnabled()
            ? new CompressedPacker()
            : new Packer();

        $packed = $header . $packer->pack($this->plugin);

        return new Package(
            new Plugin($this->plugin),
            $packed
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

        if (\mb_substr($method, -1) === 's') {
            $method = \mb_substr($method, 0, -1);
        }

        if (\method_exists($this, $method)) {
            $this->$method();
        }
    }

    /**
     * Set the current file.
     *
     * @param SplFileInfo $file
     */
    private function setCurrentFile(SplFileInfo $file): void
    {
        $this->currentFile = $file;
    }

    /**
     * Gets the current file.
     *
     * @return SplFileInfo|null
     */
    private function getCurrentFile(): ?SplFileInfo
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
            ? $this->read($this->getCurrentFile())
            : null;
    }

    /**
     * Gets a template contents.
     *
     * @param string $name
     * @param array  $data
     *
     * @return string
     */
    private function getTemplate(string $name, array $data = []): string
    {
        $file = new SplFileInfo(
            __DIR__ . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . $name . '.txt'
        );

        $content = $this->read($file);

        $variables = [];

        foreach ($data as $name => $value) {
            if (\is_scalar($value)) {
                $variables['{' . $name . '}'] = (string)$value;
            }
        }

        return \strtr($content, $variables);
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
                $textpacks[$file->getBasename('.' . $file->getExtension())] = $this->read($file);
            }
        }

        \ksort($textpacks);

        foreach ($textpacks as $language => $content) {
            if (\mb_strpos($content, '#@language') === false) {
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

        if (\mb_substr($code, 0, 5) === '<?php') {
            $code = \mb_substr($code, 5);
        }

        if (\mb_substr($code, -2, 2) === '?>') {
            $code = \mb_substr($code, 0, -2);
        }

        $this->plugin['code'][] = \rtrim($code);
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
     * Adds contents based on composer.json to the plugin installer.
     */
    private function addComposer(): void
    {
        $composer = \json_decode($this->getCurrentFileContent(), true);
        $files = [];

        if (isset($composer['autoload'], $composer['autoload']['psr-4'])) {
            foreach ($composer['autoload']['psr-4'] as $ns => $path) {
                $files = \array_merge($files, (new SourceCollector())->getFiles($this->getAbsolutePath($path), $ns));
            }
        }

        if (isset($composer['autoload'], $composer['autoload']['psr-0'])) {
            foreach ($composer['autoload']['psr-0'] as $ns => $path) {
                $files = \array_merge($files, (new SourceCollector())->getFiles($this->getAbsolutePath($path)));
            }
        }

        if ($files) {
            \array_unshift($this->plugin['code'], $this->getTemplate('autoloader', [
                'content' => \addslashes(\serialize($files)),
            ]));
        }
    }

    /**
     * Sets source path.
     *
     * @param string $path
     */
    private function setSourcePath(string $path): void
    {
        $this->source = \realpath($path);
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
     * @param SplFileInfo $file
     *
     * @return string
     */
    private function read(SplFileInfo $file): string
    {
        return $file->isFile() && $file->isReadable()
            ? \file_get_contents($file->getPathname())
            : '';
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
            'version' => '0.0.0',
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
