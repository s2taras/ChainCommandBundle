<?php

namespace App\FooBundle\Command;

use App\ChainCommandBundle\Command\BaseCommand;

/**
 * Class for demonstrate command foo:hello
 *
 * Class FooCommand
 * @package App\FooBundle\Command
 */
class FooCommand extends BaseCommand
{
    const COMMAND = 'foo:hello';
    const MESSAGE = 'Hello from Foo!';
    const DESCRIPTION = 'Say hello from Foo';
}
