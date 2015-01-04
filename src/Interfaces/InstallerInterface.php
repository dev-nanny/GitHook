<?php

namespace DevNanny\GitHook\Interfaces;

interface InstallerInterface
{
    const PRE_COMMIT = 'pre-commit';

    public function __construct(RepositoryContainerInterface $container);

    public function install($name);
}

/*EOF*/
