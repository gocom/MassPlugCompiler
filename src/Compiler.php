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
use Rah\Mtxpc\Api\PluginInterface;
use Rah\Mtxpc\Converter\PluginDataConverter;
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
     * @var PluginInterface
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

        /** @var \DirectoryIterator $file */
        foreach ($files as $file) {
            $this->setCurrentFile($file);

            if ($this->getCurrentFile()->getExtension() === 'php') {
                $this->process('code');
                continue;
            }

            $this->process();
        }

        if ($this->version !== null) {
            $this->plugin->setVersion($this->getVersion());
        }

        $this->plugin->setMd5(\md5((string) $this->plugin->getCode()));

        $converter = new PluginDataConverter();

        $pluginDataArray = $converter->convert($this->plugin);

        $header = $this->getTemplate('header', $pluginDataArray);

        $packer = $this->isCompressionEnabled()
            ? new CompressedPacker()
            : new Packer();

        $packed = $header . "\n" . $packer->pack($pluginDataArray);

        return new Package(
            $this->plugin,
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
     * @return SplFileInfo
     */
    private function getCurrentFile(): SplFileInfo
    {
        return $this->currentFile;
    }

    /**
     * Gets the current file contents.
     *
     * @return string
     */
    private function getCurrentFileContent(): string
    {
        return $this->read($this->getCurrentFile());
    }

    /**
     * Gets a template contents.
     *
     * @param string $name
     * @param array<string, mixed> $data
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
            $this->plugin->setTextpack(\implode("\n\n", \array_filter([
                $this->plugin->getTextpack(),
                $this->getCurrentFileContent(),
            ])));

            return;
        }

        if (!$this->getCurrentFile()->isDir()) {
            return;
        }

        $files = new \FilesystemIterator($this->getCurrentFile()->getPathname());
        $textpacks = [];

        /** @var \DirectoryIterator $file */
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'textpack') {
                $textpacks[$file->getBasename('.' . $file->getExtension())] = $this->read($file);
            }
        }

        \ksort($textpacks);

        $finalized = [];

        foreach ($textpacks as $language => $content) {
            if (\mb_strpos($content, '#@language') === false) {
                \array_unshift($finalized, $content);
            } else {
                $finalized[] =  $content;
            }
        }

        $this->plugin->setTextpack(\implode("\n", \array_filter($finalized)));
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
                $method = 'set' . \implode('', \array_map('ucfirst', \explode('_', $name)));

                if (\method_exists($this->plugin, $method)) {
                    $this->plugin->$method($value);
                }
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

        $this->plugin->setCode(\implode("\n", \array_filter([
            $this->plugin->getCode(),
            \rtrim($code),
        ])));
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
            $this->plugin
                ->setHelpRaw(\implode("\n\n", \array_filter([
                    $this->plugin->getHelpRaw(),
                    $help,
                ])))
                ->setIsHtmlHelpAllowed(false)
                ->setHelp('');
        } else {
            $this->plugin
                ->setHelp(\implode("\n\n", \array_filter([
                    $this->plugin->getHelp(),
                    $help,
                ])))
                ->setIsHtmlHelpAllowed(true)
                ->setHelpRaw('');
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
            $autoloader = $this->getTemplate('autoloader', [
                'content' => \addslashes(\serialize($files)),
            ]);

            $this->plugin->setCode(\implode("\n", \array_filter([
                $autoloader,
                $this->plugin->getCode(),
            ])));
        }
    }

    /**
     * Sets source path.
     *
     * @param string $path
     */
    private function setSourcePath(string $path): void
    {
        $this->source = (string) \realpath($path);
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
            ? (string) \file_get_contents($file->getPathname())
            : '';
    }

    /**
     * Gets plugin template.
     *
     * @return PluginInterface
     */
    private function getPluginTemplate(): PluginInterface
    {
        $plugin = new Plugin();

        $plugin
            ->setName(\basename($this->getSourcePath()))
            ->setVersion('0.0.0')
            ->setType(0)
            ->setOrder(5)
            ->setIsHtmlHelpAllowed(true);

        return $plugin;
    }
}
