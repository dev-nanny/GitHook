<?php

namespace DevNanny\GitHook;

class CommitDiff implements CommitDiffInterface
{
    /** @var RepositoryContainerInterface */
    private $repository;

    /**
     * @return RepositoryContainerInterface
     */
    final public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param RepositoryContainerInterface $repository
     */
    final public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    final public function __construct(RepositoryContainerInterface $repository)
    {
        $this->setRepository($repository);
    }

    /**
     * @return array
     */
    final public function getFileList()
    {
        $repositoryContainer = $this->getRepository();
        $rawOutput = $repositoryContainer->getCommittedFiles();

        return $this->buildFileList($rawOutput);
    }

    /**
     * Split the raw output from git diff --cached -z into a list of files
     *
     * @param $rawOutput
     *
     * @return array
     */
    private function buildFileList($rawOutput)
    {
        /* Please note that the input is a single string, alternating file-paths
         * and file-status delimited by, and closed of with, a NULL character.
         * Hence the last entry will always be an empty value.
         */
        $files = array();

        $parts = explode("\x00", $rawOutput);

        $currentValueIsFilePath = false;
        foreach ($parts as $pathOrType) {
            if ($currentValueIsFilePath === true) {
                $files[] = $pathOrType;
            }
            $currentValueIsFilePath = !$currentValueIsFilePath;
        };

        return $files;
    }
}

/*EOF*/
