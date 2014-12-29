<?php

namespace DevNanny\GitHook;

use Gitonomy\Git\Hooks;
use Gitonomy\Git\Repository;
use Psr\Log\LoggerInterface;

interface RepositoryContainerInterface
{
    /**
     * @return Hooks
     */
    public function getHooks();

    /**
     * @return LoggerInterface
     */
    public function getLogger();

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger);

    /**
     * @return Repository
     */
    public function getRepository();

    /**
     * @param Repository $repository
     */
    public function setRepository($repository);

    /**
     * @return string
     */
    public function getRepositoryPath();

    /**
     * @return string
     */
    public function getCommittedFiles();

    /**
     * @param string $repositoryPath
     */
    public function __construct($repositoryPath);
}
