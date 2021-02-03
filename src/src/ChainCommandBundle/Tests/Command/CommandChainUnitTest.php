<?php

namespace App\ChainCommandBundle\Tests\Command;

use App\ChainCommandBundle\Command\BaseCommand;
use App\ChainCommandBundle\EventSubscriber\CommandSubscriber;
use App\ChainCommandBundle\Exception\MemberException;
use App\ChainCommandBundle\Service\ChainCommandService;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\IncompatibleReturnValueException;
use PHPUnit\Framework\MockObject\MethodCannotBeConfiguredException;
use PHPUnit\Framework\MockObject\MethodNameAlreadyConfiguredException;
use PHPUnit\Framework\MockObject\MethodNameNotConfiguredException;
use PHPUnit\Framework\MockObject\MethodParametersAlreadyConfiguredException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandChainUnitTest extends TestCase
{
    /**
     * @var string
     */
    protected $parentCommandName = 'parent::command';

    /**
     * @var string
     */
    protected $childCommandName = 'child::command';

    /**
     * @var int
     */
    protected $exitCode = 0;

    /**
     * @var MockObject|LoggerInterface
     */
    protected $loggerMock;

    /**
     * @var MockObject|ChainCommandService
     */
    protected $commandServiceMock;

    /**
     * @var MockObject|InputInterface
     */
    protected $inputMock;

    /**
     * @var MockObject|OutputInterface
     */
    protected $outputMock;

    /**
     * @var MockObject|BaseCommand
     */
    protected $baseCommandMock;

    /**
     * @var MockObject|Application
     */
    protected $applicationMock;

    /**
     * {@inheritdoc }
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->commandServiceMock = $this->getMockBuilder(ChainCommandService::class)->getMock();
        $this->inputMock = $this->getMockBuilder(InputInterface::class)->getMock();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)->getMock();
        $this->baseCommandMock = $this->getMockBuilder(BaseCommand::class)->disableOriginalConstructor()->getMock();
        $this->applicationMock = $this->getMockBuilder(Application::class)->getMock();
    }

    /**
     * Testing BeforeCommand child command exception
     * @throws MemberException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws IncompatibleReturnValueException
     * @throws MethodCannotBeConfiguredException
     * @throws MethodNameAlreadyConfiguredException
     * @throws MethodNameNotConfiguredException
     * @throws MethodParametersAlreadyConfiguredException
     */
    public function testBeforeCommandChildException()
    {
        $this->commandServiceMock->expects($this::once())->method('isChild')->with($this->parentCommandName)->willReturn(true);
        $this->baseCommandMock->expects($this::once())->method('getName')->willReturn($this->parentCommandName);

        $consoleCommandEvent = new ConsoleCommandEvent($this->baseCommandMock, $this->inputMock, $this->outputMock);
        $subscriber = new CommandSubscriber($this->loggerMock, $this->commandServiceMock);

        $this->expectException(MemberException::class);
        $this->expectExceptionMessage(sprintf('Is a member of %s command chain and cannot be executed on its own', $this->parentCommandName));

        $subscriber->beforeCommand($consoleCommandEvent);
    }

    /**
     * Testing successfully BeforeCommand execution
     * @throws Exception
     * @throws IncompatibleReturnValueException
     * @throws InvalidArgumentException
     * @throws MemberException
     * @throws MethodCannotBeConfiguredException
     * @throws MethodNameAlreadyConfiguredException
     * @throws MethodNameNotConfiguredException
     * @throws MethodParametersAlreadyConfiguredException
     */
    public function testBeforeCommandExecution()
    {
        $this->commandServiceMock->expects($this::once())->method('isChild')->with($this->parentCommandName)->willReturn(false);
        $this->commandServiceMock->expects($this::once())->method('isParent')->with($this->parentCommandName)->willReturn(true);
        $this->commandServiceMock->expects($this::once())->method('getChildren')->with($this->parentCommandName)->willReturn([$this->childCommandName]);

        $this->loggerMock->expects($this::exactly(3))->method('info');

        $this->baseCommandMock->expects($this::once())->method('getName')->willReturn($this->parentCommandName);
        $this->baseCommandMock->expects($this::once())->method('getApplication')->willReturn($this->applicationMock);

        $consoleCommandEvent = new ConsoleCommandEvent($this->baseCommandMock, $this->inputMock, $this->outputMock);
        $subscriber = new CommandSubscriber($this->loggerMock, $this->commandServiceMock);

        $subscriber->beforeCommand($consoleCommandEvent);
    }

    /**
     * Testing successfully AfterCommand execution
     * @throws Exception
     * @throws IncompatibleReturnValueException
     * @throws InvalidArgumentException
     * @throws MethodCannotBeConfiguredException
     * @throws MethodNameAlreadyConfiguredException
     * @throws MethodNameNotConfiguredException
     * @throws MethodParametersAlreadyConfiguredException
     * @throws CommandNotFoundException
     */
    public function testAfterCommandExecution()
    {
        $this->commandServiceMock->expects($this::once())->method('isParent')->with($this->parentCommandName)->willReturn(true);
        $this->commandServiceMock->expects($this::once())->method('getChildren')->with($this->parentCommandName)->willReturn([$this->childCommandName]);

        $this->baseCommandMock->expects($this::once())->method('getName')->willReturn($this->parentCommandName);

        $this->loggerMock->expects($this::exactly(2))->method('info');

        $childCommand =  $this->getMockBuilder(BaseCommand::class)->disableOriginalConstructor()->getMock();
        $childCommand->expects($this::once())->method('run')->with($this->inputMock, $this->outputMock);

        $this->applicationMock->expects($this::once())->method('get')->with($this->childCommandName)->willReturn($childCommand);

        $consoleTerminateEvent = new ConsoleTerminateEvent($this->baseCommandMock, $this->inputMock, $this->outputMock, $this->exitCode);
        $subscriber = new CommandSubscriber($this->loggerMock, $this->commandServiceMock);
        $subscriber->setApplication($this->applicationMock);

        $subscriber->afterCommand($consoleTerminateEvent);
    }
}
