<?php

namespace App\ChainCommandBundle\Tests\Command;

use App\BarBundle\Command\BarCommand;
use App\ChainCommandBundle\Exception\MemberException;
use App\FooBundle\Command\FooCommand;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class TestCommand
 * @package App\ChainCommandBundle\Tests\Command
 */
class CommandChainTest extends KernelTestCase
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var MockObject|LoggerInterface
     */
    protected $loggerMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        static::$kernel = static::createKernel([
            'environment' => 'test',
            'debug' => true,
        ]);
        static::$kernel->boot();

        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->loggerMock->expects($this::any())->method('info');

        $this->dispatcher = static::$kernel->getContainer()->get('event_dispatcher');
        $this->input = new ArrayInput([]);
        $this->output = new StreamOutput(fopen('php://memory', 'w', false));
    }

    /**
     * Testing child command execution with exception and specified message
     * @throws CommandNotFoundException
     * @throws LogicException
     */
    public function testChildExecutionCommand()
    {
        $application = new Application(static::$class);
        $application->add(new BarCommand($this->loggerMock));
        $command = $application->find('bar:hi');

        $event = new ConsoleCommandEvent($command, $this->input, $this->output);

        $this->expectException(MemberException::class);
        $this->expectErrorMessage('Is a member of bar:hi command chain and cannot be executed on its own');
        $this->dispatcher->dispatch($event, ConsoleEvents::COMMAND);
    }

    /**
     * Testing successfully commands chain
     * @throws CommandNotFoundException
     * @throws LogicException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testFullChainExecution()
    {
        $application = new Application(static::$class);
        $application->add(new BarCommand($this->loggerMock));
        $application->add(new FooCommand($this->loggerMock));
        $command = $application->find('foo:hello');

        $event = new ConsoleCommandEvent($command, $this->input, $this->output);
        $this->dispatcher->dispatch($event, ConsoleEvents::COMMAND);

        if ($event->commandShouldRun()) {
            $exitCode = $command->run($this->input, $this->output);
        } else {
            $exitCode = ConsoleCommandEvent::RETURN_CODE_DISABLED;
        }

        $event = new ConsoleTerminateEvent($command, $this->input, $this->output, $exitCode);
        $this->dispatcher->dispatch($event, ConsoleEvents::TERMINATE);

        static::assertEquals('Hello from Foo!'.PHP_EOL. 'Hi from Bar!'.PHP_EOL, $this->getDisplay());
    }

    /**
     * Read from output buffer
     * @param false $normalize
     * @return mixed
     */
    protected function getDisplay($normalize = false)
    {
        rewind($this->output->getStream());

        $display = stream_get_contents($this->output->getStream());

        if ($normalize) {
            $display = str_replace(PHP_EOL, "\n", $display);
        }

        return $display;
    }
}
