mpc ♥ txp
=====

**mtxpc** compiles [Textpattern CMS](https://textpattern.com) plugin sources into installer packages. Supports multi-file structure and a JSON manifest file.

Install
-----

Using [Composer](https://getcomposer.org):

```shell
$ composer require rah/mtxpc --dev
```

Usage
-----

### As a Library

```php
use Rah\mtxpc\Compiler;

$compiler = new Compiler();

echo $compiler->compile('path/to/plugin/source/directory');
```

### Via Command Line

```shell
$ vendor/bin/mtxpc [[-h|--help][-c|--compress]] <file>
```

Example Plugin Template
-----

See [abc_plugin](https://github.com/gocom/abc_plugin) repository for an example template.

Plugin Template
-----

The main difference compared to Textpattern's vanilla plugin template and this compiler is how the plugins are constructed. Textpattern's official template hosts everything in a single file, while **mpc** splits the sources to separate files; translations, readme, manifest and the actual source code.

### Translations

[Textpacks](https://forum.textpattern.com/viewtopic.php?id=33182), Textpattern's plugin localization files, can be stored in a single file, or as separate files, each file storing a different language. The compiler searches `.textpack` files from a directory named `textpacks`.

Textpattern offers a way to set the default language which is used as the fallback when the plugin doesn't come with user's language. The default language is chosen based on `#@language` keyword, or lack of. Textpack files that do not define a language, are treated as the default fallback. Compare abc_plugin's [en-gb.texpack](https://github.com/gocom/abc_plugin/blob/master/textpack/en-gb.textpack) and [fi-fi.textpack](https://github.com/gocom/abc_plugin/blob/master/textpack/fi-fi.textpack) files. The `en-gb` doesn't define the language code and is set as the default.

### Manifest

The manifest file, `manifest.json`, contains all plugin's meta data. That's the rest of the stuff a plugin is made of, including plugin's **version** number, **author**, **url**, special **flags**, **type**, and recommended loading **order**.

### Help File

Manifest file can also be used to specify help file's location. By default a help file is expected to be named as `help` with any extension, but a different location can be chosen with a file option:

```json
{
    "help": {
        "file": ["./README.textile"]
    }
}
```

Textile markup can be used in the help file. If the help file's filename ends with `.textile` extension, or the file contents start with `h1.` tag, the file is treated as if it contained Textile markup.
