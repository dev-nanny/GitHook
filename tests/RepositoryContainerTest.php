<?php

namespace DevNanny\GitHook;

use Gitonomy\Git\Exception\ReferenceNotFoundException;
use Gitonomy\Git\Repository;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass DevNanny\GitHook\RepositoryContainer
 * @covers ::<!public>
 * @covers ::__construct
 * @covers ::setRepositoryPath

 */
final class RepositoryContainerTest extends \PHPUnit_Framework_TestCase
{
    ////////////////////////////////// FIXTURES \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    const MOCK_STRING = 'foo/bar';

    /** @var RepositoryContainer */
    private $container;
    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $mockRepository;

    protected function setUp()
    {
        $this->container = new RepositoryContainer(self::MOCK_STRING);
    }

    /////////////////////////////////// TESTS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @test
     *
     * @covers ::getRepository
     * @covers ::getOptions
     * @covers ::getLogger
     * @covers ::getRepositoryPath
     */
    final public function repositoryContainerShouldCreateRepositoryWhenNoneGiven()
    {
        $container = $this->container;

        $container->__construct(__DIR__);

        $actual = $container->getRepository();

        $this->assertInstanceOf(Repository::class, $actual);
    }

    /**
     * @test
     *
     * @covers ::getRepository
     * @covers ::setRepository
     */
    final public function repositoryContainerShouldRememberRepositoryWhenGivenRepository()
    {
        $container = $this->container;

        $expected = $this->getMockRepository();

        $container->setRepository($expected);

        $actual = $container->getRepository($expected);

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     *
     * @covers ::getLogger
     * @covers ::setLogger
     */
    final public function repositoryContainerShouldRememberLoggerWhenGivenLogger()
    {
        $container = $this->container;

        $expected = $this->getMockLogger();

        $container->setLogger($expected);

        $actual = $container->getLogger($expected);

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     *
     * @covers ::getRepositoryPath
     */
    final public function repositoryContainerShouldRememberThePathItWasGivenWhenInstantiated()
    {
        $container = $this->container;

        $expected = self::MOCK_STRING;
        $actual = $container->getRepositoryPath();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     *
     * @covers ::getLogger
     * @covers ::setLogger
     * @covers ::getOptions
     */
    final public function repositoryContainerShouldPassLoggerOnToRepositoryWhenGivenLogger()
    {
        $container = $this->container;
        $mockLogger = $this->getMockLogger();
        $container->setLogger($mockLogger);

        $options = $container->getOptions();

        $this->assertSame($mockLogger, $options['logger']);
    }

    /**
     * @test
     *
     * @covers ::getCommittedFiles
     * @covers ::getRepository
     * @covers ::setRepository
     */
    final public function repositoryContainerShouldRunGitDiffIndexCommandWhenAskedToGetCommittedFiles()
    {
        $container = $this->container;

        $mockRepository = $this->getMockRepository();
        $this->addRunCallToMockRepository($mockRepository);
        $container->setRepository($mockRepository);

        $committedFiles = $container->getCommittedFiles();

        return $committedFiles;
    }

    /**
     * @test
     *
     * @covers ::getCommittedFiles
     * @covers ::getRepository
     * @covers ::setRepository
     */
    final public function repositoryContainerShouldReplaceHeadReferenceWhenAskedToGetCommittedFilesForNewRepository()
    {
        $container = $this->container;

        $mockRepository = $this->getMockRepository();
        $this->addRunCallToMockRepository($mockRepository, '4b825dc642cb6eb9a060e54bf8d69288fbee4904');
        $container->setRepository($mockRepository);

        /** @var ReferenceNotFoundException|\PHPUnit_Framework_MockObject_MockObject $mockException */
        $mockException = $this->getMockBuilder(ReferenceNotFoundException::class)
            ->setConstructorArgs(array(RepositoryContainer::ERROR_NO_HEAD_FOUND))
            ->getMock()
        ;

        $mockRepository->expects($this->exactly(1))
            ->method('getHead')
            ->willThrowException($mockException)
        ;

        $container->getCommittedFiles();
    }

    /**
     * @test
     *
     * @covers ::getCommittedFiles
     * @covers ::getRepository
     * @covers ::setRepository
     *
     * @expectedException \Gitonomy\Git\Exception\ReferenceNotFoundException
     * @expectedExceptionMessage DevNanny\GitHook\RepositoryContainerTest::MOCK_STRING
     */
    final public function repositoryContainerShouldPassExceptionsOnWhenRepositoryThrowsException()
    {
        $container = $this->container;

        $mockRepository = $this->getMockRepository();
        $container->setRepository($mockRepository);

        /** @var \Exception|\PHPUnit_Framework_MockObject_MockObject $mockException */
        $mockException = $this->getMockBuilder(ReferenceNotFoundException::class)
            ->setConstructorArgs(array(self::MOCK_STRING))
            ->getMock()
        ;

        $mockRepository->expects($this->exactly(1))
            ->method('getHead')
            ->willThrowException($mockException)
        ;

        $container->getCommittedFiles();
    }

    /**
     * @test
     *
     * @depends repositoryContainerShouldRunGitDiffIndexCommandWhenAskedToGetCommittedFiles
     *
     * @param $committedFiles
     */
    final public function repositoryContainerShouldReturnRawOutputWhenAskedToGetCommittedFiles($committedFiles) {
        $this->assertSame(self::MOCK_STRING, $committedFiles);
    }

    ////////////////////////////// MOCKS AND STUBS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockRepository()
    {
        $this->mockRepository = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        return $this->mockRepository;
    }

    /**
     * @return LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockLogger()
    {
        return $this->getMockBuilder(LoggerInterface::class)->getMock();
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mockRepository
     * @param string $reference
     */
    private function addRunCallToMockRepository(
        \PHPUnit_Framework_MockObject_MockObject $mockRepository,
        $reference = 'HEAD'
    ) {
        $arguments = array(
            '--cached',
            '--name-status',
            '-z',
            '--no-color',
            (string) $reference
        );

        $mockRepository->expects($this->exactly(1))
            ->method('run')
            ->with('diff-index', $arguments)
            ->willReturn(self::MOCK_STRING)
        ;
    }
    /////////////////////////////// DATAPROVIDERS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
}

/*EOF*/
