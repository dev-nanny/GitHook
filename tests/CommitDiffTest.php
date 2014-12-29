<?php

namespace DevNanny\GitHook;

use DevNanny\GitHook\Interfaces\RepositoryContainerInterface;

/**
 * @coversDefaultClass DevNanny\GitHook\CommitDiff
 * @covers ::<!public>
 * @covers ::__construct
 *
 * @uses \Doctrine\Instantiator\Instantiator
 */
final class CommitDiffTest extends \PHPUnit_Framework_TestCase
{
    ////////////////////////////////// FIXTURES \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @var CommitDiff */
    private $commitDiff;
    /** @var RepositoryContainerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $mockRepository;

    protected function setUp()
    {
        $this->mockRepository = $this->getMockRepositoryContainerInterface();
        $this->commitDiff = new CommitDiff($this->mockRepository);
    }

    /////////////////////////////////// TESTS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @test
     */
    final public function commitDiffShouldBeGivenRepositoryContainerWhenInstantiated()
    {
        $this->setExpectedException(
            \PHPUnit_Framework_Error::class,
            'Argument 1 passed to DevNanny\GitHook\CommitDiff::__construct() ' .
            'must implement interface ' . RepositoryContainerInterface::class . ', none given'
        );
        /** @noinspection PhpParamsInspection */
        new CommitDiff();
    }

    /**
     * @test
     *
     * @covers ::getChangeList
     */
    final public function commitDiffShouldAlwaysReturnFileListWhenAskedToGetFileList()
    {
        $container = $this->commitDiff;

        $fileList = $container->getChangeList();

        $this->assertInternalType('array', $fileList);

        return $fileList;
    }

    /**
     * @test
     *
     * @depends commitDiffShouldAlwaysReturnFileListWhenAskedToGetFileList
     *
     * @param $fileList
     */
    final public function commitDiffShouldReturnAnEmptyFileListWhenNoFilesChanged(array $fileList)
    {
        $this->assertEmpty($fileList);
    }

    /**
     * @test
     *
     * @covers ::getChangeList
     */
    final public function commitDiffShouldReturnPopulatedFileListWhenFilesChanged()
    {
        $container = $this->commitDiff;

        $mockList = array(
            'A', 'src/Foo.php',
            'C', 'src/Foo/Bar/Bar.txt',
            'X', 'baz'
        );

        $expected = array(
            'src/Foo.php',
            'src/Foo/Bar/Bar.txt',
            'baz'
        );

        $rawOutput = implode("\x00", $mockList) . "\x00";

        $this->mockRepository->expects($this->exactly(1))
            ->method('getCommittedFiles')
            ->willReturn($rawOutput)
        ;

        $actual = $container->getChangeList();

        $this->assertEquals($expected, $actual);
    }

    ////////////////////////////// MOCKS AND STUBS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return RepositoryContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockRepositoryContainerInterface()
    {
        return $this->getMockBuilder(RepositoryContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /////////////////////////////// DATAPROVIDERS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
}

/*EOF*/
