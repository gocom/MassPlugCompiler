# Changelog

## 0.8.0

* Added: `composer.json` PSR-4 and PSR-0 autoloader definition support.
* Added: `--version` option to `mtxpc` command line application.
* Added: released as pre-built phar.

## 0.7.0

* Added: `\Rah\Mtxpc\Api\PluginInterface` that is then returned by the `\Rah\Mtxpc\Api\PackageInterface::getUnpacked()`.
* Added: `\Rah\Mtxpc\Api\PackagerInterface` and implemented separate packagers for compressed and uncompressed plugin packages.

## 0.6.0

* Added: `\Rah\Mtxpc\Api\PackageInterface::getUnpacked()` returns unpacked plugin package contents.
* Changed: Packed `allow_html_help` is set with a boolean `true` or `false` rather than integer `1` or `0`.

## 0.5.0

* Added: Made the compiler available as a [GitHub action](https://github.com/gocom/action-textpattern-package-plugin).
* Added: `--outdir` option to `mtxpc` command line application.
* Internal: Unit tests are now ran on Travis CI.

## 0.4.0

* Added: `\Rah\Mtxpc\Api\PackageInterface` that is then returned by the `\Rah\Mtxpc\Api\CompilerInterface::compile()`.

## 0.3.0

* Complete refactoring and modernization.
* Released as a Composer library.

## 0.2.0

* XML based plugin manifest file support.

## 0.1.0

* Initial release.
