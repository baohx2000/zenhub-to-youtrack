<?php


namespace B2k\ZH2YT\Application;


use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Application;
use Auryn\Injector;


class CommandLoader
{
    protected Injector $injector;
    protected Application $application;
    protected string $fileMatch;

    public function __construct(
        Injector $injector,
        Application $application,
        string $fileMatch = "/[A-Z].*Command.php/"
    ) {
        $this->injector = $injector;
        $this->application = $application;
        $this->fileMatch = $fileMatch;
    }

    public function loadCommands($path, $namespace, $filter = null): void
    {
        $commands = [];

        // Get the realpath so we can strip it from the start of the filename
        $realpath = realpath($path);

        $finder = (new Finder)->files()->in($path)->name($this->fileMatch);
        foreach ($finder as $file) {
            // Get the realpath of the file and ensure the class is loaded
            $filename = $file->getRealPath();

            // Convert the filename to a class
            $class = $filename;
            $class = str_replace([$realpath, ".php", "/"], ["", "", "\\"], $class);

            if ($filter && $filter($class)) {
                continue;
            }

            // Create an instance of the command class
            $class = $namespace . $class;
            $commands[] = $this->injector->make($class);
        }

        if (!$commands) {
            throw new \InvalidArgumentException("No commands were found in the path ($path)");
        }

        $this->application->addCommands($commands);
    }
}
