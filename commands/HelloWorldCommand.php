<?php


namespace Sirius\commands;


class HelloWorldCommand extends Command
{
    public function configure()
    {
        $this->setName('HelloWorld')
            ->setAlias('hlw')
            ->setDescription('écrit Hello World');
    }

    public function execute()
    {
        echo "Hello World" . PHP_EOL;
    }
}