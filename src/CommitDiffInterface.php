<?php

namespace DevNanny\Git;

interface CommitDiffInterface
{
    //Possible status letters are:
    const FILE_STATUS_ADDED         = 'A';
    const FILE_STATUS_COPIED        = 'C';
    const FILE_STATUS_DELETED       = 'D';
    const FILE_STATUS_MODIFIED      = 'M';
    const FILE_STATUS_RENAMED       = 'R';
    const FILE_STATUS_TYPE_CHANGED  = 'T';
    const FILE_STATUS_UNMERGED      = 'U';  // you must complete the merge before it can be committed)
    const FILE_STATUS_UNKNOWN       = 'X';  // most probably a bug, please report it to GIT

    /**
     * @return RepositoryContainerInterface
     */
    public function getRepository();

    /**
     * @param RepositoryContainerInterface $repository
     */
    public function setRepository($repository);

    /**
     * @return array
     */
    public function getFileList();

    /**
     * @param RepositoryContainerInterface $repository
     */
    public function __construct(RepositoryContainerInterface $repository);
}

/*EOF*/
