<?php

namespace DevNanny\GitHook;

use Gitonomy\Git\Hooks;

/**
 * @coversDefaultClass \DevNanny\GitHook\Installer
 * @covers ::<!public>
 * @covers ::__construct
 *
 * @uses \Doctrine\Instantiator\Instantiator
 */
final class InstallerTest extends \PHPUnit_Framework_TestCase
{
    ////////////////////////////////// FIXTURES \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @var Installer */
    private $installer;
    /** @var RepositoryContainerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $mockRepository;
    /** @var Hooks|\PHPUnit_Framework_MockObject_MockObject  */
    private $mockHooks;

    protected function setUp()
    {
        $this->mockRepository = $this->getMockRepositoryContainerInterface();
        $this->installer = new Installer($this->mockRepository);
    }

    /////////////////////////////////// TESTS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @covers ::__construct
     */
    final public function testInstallerShouldBeGivenRepositoryContainerWhenInstantiated()
    {
        // Tested in mock
    }

    /**
     * @covers ::__construct
     */
    final public function testInstallerShouldSetHooksFromRepositoryContainerWhenInstantiated()
    {
        $installer = $this->installer;
        $expected = $this->mockHooks;

        $reflectionObject = new \ReflectionObject($installer);
        $reflectionProperty = $reflectionObject->getProperty('hooks');
        $reflectionProperty->setAccessible(true);

        $actual = $reflectionProperty->getValue($installer);

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers ::install
     */
    final public function testInstallerShouldComplainWhenAskedToInstallWithoutSpecifyingHookName()
    {
        $this->setExpectedException(
            \PHPUnit_Framework_Error::class,
            'Missing argument 1 for DevNanny\GitHook\Installer::install()'
        );
        $installer = $this->installer;

        /** @noinspection PhpParamsInspection */
        $installer->install();
    }

    /**
     * @covers ::install
     */
    final public function testInstallerShouldComplainWhenAskedToInstallUnsupportedHook()
    {
        $installer = $this->installer;
        $unSupportedHook = 'Installer::UNSUPPORTED';
        $this->setExpectedExceptionRegExp(
            \UnexpectedValueException::class,
            sprintf('/' . Installer::ERROR_UNSUPPORTED_HOOK . '/', $unSupportedHook, '.*')
        );

        $installer->install($unSupportedHook);
    }

    /**
     * @covers ::install
     */
    final public function testInstallerShouldComplainWhenContentOfAlreadyInstalledHookDoesNotMatchSourceContent()
    {
        $installer = $this->installer;
        $mockHooks = $this->mockHooks;
        $supportedHook = Installer::PRE_COMMIT;

        $mockHooks->expects($this->exactly(2))
            ->method('has')
            ->with($supportedHook)
            ->willReturn(true)
        ;
        $this->setExpectedExceptionRegExp(
            \UnexpectedValueException::class,
            sprintf('/' . Installer::ERROR_HOOK_ALREADY_EXISTS . '/', $supportedHook)
        );

        $installer->install($supportedHook);
    }

    /**
     * @covers ::install
     */
    final public function testInstallerShouldValidateInstalledHookMatchesSourceHookWhenAskedToInstallSupportedHook()
    {
        $installer = $this->installer;
        $mockHooks = $this->mockHooks;
        $mockContent = 'foo';
        $mockFile = [$mockContent];

        $supportedHook = Installer::PRE_COMMIT;

        $mockHooks->expects($this->exactly(2))
            ->method('has')
            ->with($supportedHook)
            ->willReturn(true)
        ;

        $mockHooks->expects($this->exactly(1))
            ->method('get')
            ->with($supportedHook)
            ->willReturn($mockContent)
        ;

        $reflectionObject = new \ReflectionObject($installer);
        $reflectionProperty = $reflectionObject->getProperty('files');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($installer, array($supportedHook => $mockFile));

        $installed = $installer->install($supportedHook);

        return $installed;
    }

    /**
     * @covers ::install
     * @depends testInstallerShouldValidateInstalledHookMatchesSourceHookWhenAskedToInstallSupportedHook
     */
    final public function testInstallerShouldMarkHookInstalledWhenHookAlreadyInstalledAndMatchesSourceHook($installed)
    {
        $this->assertTrue($installed);
    }

    /**
     * @covers ::install
     */
    final public function testInstallerShouldInstallHookWhenAskedToInstallSupportedHook()
    {
        $installer = $this->installer;
        $supportedHook = Installer::PRE_COMMIT;

        $mockHooks = $this->mockHooks;

        $mockHooks->expects($this->exactly(2))
            ->method('has')
            ->with($supportedHook)
            ->willReturn(false)
        ;

        $mockHooks->expects($this->exactly(1))
            ->method('setSymlink')
            ->with($supportedHook, sprintf('%s/git/hook/%s', realpath(__DIR__ . '/..'), $supportedHook))
        ;

        $installed = $installer->install($supportedHook);

        $this->assertTrue($installed);
    }

    ////////////////////////////// MOCKS AND STUBS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return RepositoryContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockRepositoryContainerInterface()
    {
        $mockContainer = $this->getMockBuilder(RepositoryContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->mockHooks = $this->getMockBuilder(Hooks::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $mockContainer->expects($this->exactly(1))
            ->method('getHooks')
            ->willReturn($this->mockHooks)
        ;
        return $mockContainer;
    }

    /////////////////////////////// DATAPROVIDERS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
}

/*EOF*/
