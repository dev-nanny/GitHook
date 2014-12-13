<?php

namespace DevNanny\GitHook;

use Gitonomy\Git\Exception\ReferenceNotFoundException;
use Gitonomy\Git\Repository;
use Psr\Log\LoggerInterface;

class RepositoryContainer implements RepositoryContainerInterface
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    const OPTION_DEBUG = 'debug';
    const OPTION_LOGGER = 'logger';

    const ERROR_NO_HEAD_FOUND = 'Unable to find HEAD file';

    /** @var string */
    private $repositoryPath;
    /** @var Repository */
    private $repository;
    /** @var LoggerInterface */
    private $logger;
    /** @var array */
    private $options = array(
        self::OPTION_DEBUG => false,
        self::OPTION_LOGGER => null,
        'command' => 'git',
        //'environment_variables' => null,
        'process_timeout' => 3600,
        'working_dir' => null,
    );

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return LoggerInterface
     */
    final public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    final public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    final public function getOptions()
    {
        $logger = $this->getLogger();

        if ($logger !== $this->options[self::OPTION_LOGGER]) {
            $this->options[self::OPTION_LOGGER] = $logger;
        }

        return $this->options;
    }

    /**
     * @return Repository
     */
    final public function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = new Repository(
                $this->getRepositoryPath(),
                $this->getOptions()
            );
        }
        return $this->repository;
    }

    /**
     * @param Repository $repository
     */
    final public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    final public function getRepositoryPath()
    {
        return $this->repositoryPath;
    }

    /**
     * @param string $repositoryPath
     */
    private function setRepositoryPath($repositoryPath)
    {
        $this->repositoryPath = $repositoryPath;
    }

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    final public function __construct($repositoryPath)
    {
        $this->setRepositoryPath($repositoryPath);
    }

    /**
     * @return string
     */
    final public function getCommittedFiles()
    {
        $repository = $this->getRepository();

        $against = $this->getHead();

        $arguments = array(
            '--cached',
            '--name-status',
            '-z',
            '--no-color',
            $against
        );

        return $repository->run('diff-index', $arguments);
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return string
     */
    private function getHead()
    {
        $repository = $this->getRepository();

        try {
            //@NOTE: As `getHead` may return Reference|Commit|null we just use (string) HEAD
            /*$head = */$repository->getHead();
            $head = 'HEAD';
        } catch (ReferenceNotFoundException $exception) {
            if (strpos($exception->getMessage(), self::ERROR_NO_HEAD_FOUND) !== false) {
                //@NOTE: Initial commit, diff against an empty tree object
                $head = '4b825dc642cb6eb9a060e54bf8d69288fbee4904';
            } else {
                throw $exception;
            }
        }

        return $head;
    }
}

/*EOF*/
