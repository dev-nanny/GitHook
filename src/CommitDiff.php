<?php

namespace DevNanny\GitHook;

class CommitDiff implements CommitDiffInterface
{
    /** @var RepositoryContainerInterface */
    private $repository;

    final public function __construct(RepositoryContainerInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return array
     */
    final public function getChangeList()
    {
        $repositoryContainer = $this->repository;
        $rawOutput = $repositoryContainer->getCommittedFiles();

        return $this->buildChangeList($rawOutput);
    }

    /**
     * Split the raw output from git diff --cached -z into a list of files
     *
     * @param $rawOutput
     *
     * @return array
     */
    private function buildChangeList($rawOutput)
    {
        /* Please note that the input is a single string, alternating file-status
         * and file-paths, delimited by (and closed of with), a NULL character.
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
