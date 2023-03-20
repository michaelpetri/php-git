# PHP Git

This package contains a php wrapper around the git cli, it is based on [symfony/process](https://github.com/symfony/process) and strictly typed.


[![Type Coverage](https://shepherd.dev/github/michaelpetri/php-git/coverage.svg)](https://shepherd.dev/github/michaelpetri/php-git)
[![Latest Stable Version](https://poser.pugx.org/michaelpetri/php-git/v)](https://packagist.org/packages/michaelpetri/php-git)
[![License](https://poser.pugx.org/michaelpetri/php-git/license)](https://packagist.org/packages/michaelpetri/php-git)

## Installation:
```
composer require michaelpetri/php-git 
```

## Example:

```php

$file = File::from('/home/mpetri/PhpstormProjects/php-git/README.md');

$repository = new GitRepository($file->directory);

$repository->add($file);
$repository->commit('Initial commit')
```

See [GitRepositoryInterface](src/MichaelPetri/Git/GitRepositoryInterface.php) or [Tests](/tests) for an overview of all available methods.