<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class ReadmeCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('readme')
            ->setDescription('Generates the README content, based on the fix command help.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $header = <<<'EOF'
PHP Coding Standards Fixer
==========================

The PHP Coding Standards Fixer tool fixes *most* issues in your code when you
want to follow the PHP coding standards as defined in the PSR-1 and PSR-2
documents and many more.

If you are already using a linter to identify coding standards problems in your
code, you know that fixing them by hand is tedious, especially on large
projects. This tool does not only detect them, but also fixes them for you.

Requirements
------------

PHP needs to be a minimum version of PHP 5.3.6.

Installation
------------

Locally
~~~~~~~

Download the `php-cs-fixer.phar`_ file and store it somewhere on your computer.

Globally (manual)
~~~~~~~~~~~~~~~~~

You can run these commands to easily access latest ``php-cs-fixer`` from anywhere on
your system:

.. code-block:: bash

    $ wget %download.url% -O php-cs-fixer

or with specified version:

.. code-block:: bash

    $ wget %download.version_url% -O php-cs-fixer

or with curl:

.. code-block:: bash

    $ curl -L %download.url% -o php-cs-fixer

then:

.. code-block:: bash

    $ sudo chmod a+x php-cs-fixer
    $ sudo mv php-cs-fixer /usr/local/bin/php-cs-fixer

Then, just run ``php-cs-fixer``.

Globally (Composer)
~~~~~~~~~~~~~~~~~~~

To install PHP CS Fixer, `install Composer <https://getcomposer.org/download/>`_ and issue the following command:

.. code-block:: bash

    $ composer global require friendsofphp/php-cs-fixer

Then make sure you have the global Composer binaries directory in your ``PATH``. This directory is platform-dependent, see `Composer documentation <https://getcomposer.org/doc/03-cli.md#composer-home>`_ for details. Example for some Unix systems:

.. code-block:: bash

    $ export PATH="$PATH:$HOME/.composer/vendor/bin"

Globally (homebrew)
~~~~~~~~~~~~~~~~~~~

PHP-CS-Fixer is part of the homebrew-php project. Follow the installation
instructions at https://github.com/homebrew/homebrew-php if you don't
already have it.

.. code-block:: bash

    $ brew install homebrew/php/php-cs-fixer

Update
------

Locally
~~~~~~~

The ``self-update`` command tries to update ``php-cs-fixer`` itself:

.. code-block:: bash

    $ php php-cs-fixer.phar self-update

Globally (manual)
~~~~~~~~~~~~~~~~~

You can update ``php-cs-fixer`` through this command:

.. code-block:: bash

    $ sudo php-cs-fixer self-update

Globally (Composer)
~~~~~~~~~~~~~~~~~~~

You can update ``php-cs-fixer`` through this command:

.. code-block:: bash

    $ ./composer.phar global update friendsofphp/php-cs-fixer

Globally (homebrew)
~~~~~~~~~~~~~~~~~~~

You can update ``php-cs-fixer`` through this command:

.. code-block:: bash

    $ brew upgrade php-cs-fixer

Usage
-----

EOF;

        $footer = <<<'EOF'

Helpers
-------

Dedicated plugins exist for:

* `Atom`_
* `NetBeans`_
* `PhpStorm`_
* `Sublime Text`_
* `Vim`_

Contribute
----------

The tool comes with quite a few built-in fixers, but everyone is more than
welcome to `contribute`_ more of them.

Fixers
~~~~~~

A *fixer* is a class that tries to fix one CS issue (a ``Fixer`` class must
implement ``FixerInterface``).

Configs
~~~~~~~

A *config* knows about the CS rules and the files and directories that must be
scanned by the tool when run in the directory of your project. It is useful for
projects that follow a well-known directory structures (like for Symfony
projects for instance).

.. _php-cs-fixer.phar: %download.url%
.. _Atom:              https://github.com/Glavin001/atom-beautify
.. _NetBeans:          http://plugins.netbeans.org/plugin/49042/php-cs-fixer
.. _PhpStorm:          http://tzfrs.de/2015/01/automatically-format-code-to-match-psr-standards-with-phpstorm
.. _Sublime Text:      https://github.com/benmatselby/sublime-phpcs
.. _Vim:               https://github.com/stephpy/vim-php-cs-fixer
.. _contribute:        https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/CONTRIBUTING.md

EOF;

        $command = $this->getApplication()->get('fix');
        $help = $command->getHelp();
        $help = str_replace('%command.full_name%', 'php-cs-fixer.phar '.$command->getName(), $help);
        $help = str_replace('%command.name%', $command->getName(), $help);
        $help = preg_replace('#</?(comment|info)>#', '``', $help);
        $help = preg_replace('#^(\s+)``(.+)``$#m', '$1$2', $help);
        $help = preg_replace('#^ \* ``(.+)``#m', '* **$1**', $help);
        $help = preg_replace("#^\n( +)#m", "\n.. code-block:: bash\n\n$1", $help);
        $help = preg_replace("#^\.\. code-block:: bash\n\n( +<\?(\w+))#m", ".. code-block:: $2\n\n$1", $help);
        $help = preg_replace_callback(
            "#^\s*<\?(\w+).*?\?>#ms",
            function ($matches) {
                $result = preg_replace("#^\.\. code-block:: bash\n\n#m", '', $matches[0]);

                if ('php' !== $matches[1]) {
                    $result = preg_replace("#<\?{$matches[1]}\s*#", '', $result);
                }

                $result = preg_replace("#\n\n +\?>#", '', $result);

                return $result;
            },
            $help
        );
        $help = preg_replace('#^                        #m', '  ', $help);
        $help = preg_replace('#\*\* +\[#', '** [', $help);

        $downloadLatestUrl = $this->getLatestDownloadUrl();
        $downloadUrl = 'http://cs.sensiolabs.org/download/php-cs-fixer-v2.phar';

        $header = str_replace('%download.version_url%', $downloadLatestUrl, $header);
        $header = str_replace('%download.url%', $downloadUrl, $header);
        $footer = str_replace('%download.version_url%', $downloadLatestUrl, $footer);
        $footer = str_replace('%download.url%', $downloadUrl, $footer);

        $output->write($header."\n".$help."\n".$footer);
    }

    private function getLatestDownloadUrl()
    {
        $version = $this->getApplication()->getVersion();
        $changelogFile = __DIR__.'/../../../CHANGELOG.md';

        if (is_file($changelogFile)) {
            $currentMajor = (int) $version;
            $changelog = file_get_contents($changelogFile);

            for ($i = $currentMajor; $i > 0; --$i) {
                preg_match('/Changelog for v('.$i.'.\d+.\d+)/', $changelog, $matches);

                if (2 === count($matches)) {
                    $version = $matches[1];
                    break;
                }
            }

            if (null === $version) {
                throw new \RuntimeException('Invalid changelog data!');
            }
        }

        return sprintf(
            'https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v%s/php-cs-fixer.phar',
            $version
        );
    }
}
