Contributing
=====

License
-----

[GPL-2.0](https://raw.github.com/gocom/MassPlugCompiler/master/LICENSE).

Versioning
----

[Semantic Versioning](https://semver.org/).

Development environment
-----

The project uses Docker containers and docker-compose to provide development
environment, and Makefile is used to wrap commands. No other dependencies need
to be installed to the host machine other than Docker and Make.

Development
-----

For available commands, see:

```shell
$ make help
```

Coding style
-----

To verify that your additions follows coding style, run:

```shell
$ make lint
```

Configure git
-----

For convenience your committer, git user, should be linked to your GitHub
account:

```shell
$ git config --global user.name "John Doe"
$ git config --global user.email john.doe@example.com
```

Make sure to use an email address that is linked to your GitHub account. It can
be a throwaway address, or you can use GitHub's email protection features. We
don't want your emails, but this is to make sure we know who did what. All
commits nicely link to their author, instead of them coming
from ``foobar@invalid.tld``.
