<?php

namespace Goodechilde\Arche\Console\Contracts;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

abstract class GeneratorCommand extends \Illuminate\Console\GeneratorCommand
{
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type;


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath($name);
        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((!$this->hasOption('force') ||
                !$this->option('force')) &&
            $this->alreadyExists($this->getNameInput())
        ) {
            $this->error($this->type . ' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->buildClass($name));

        $displayPath = str_replace($this->laravel->basePath(), '', $path);

        $this->info($this->type . ' created successfully in ' . $displayPath);
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param string $name
     *
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        $rootNamespace = $this->rootNamespace();
        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        $name = str_replace('/', '\\', $name);

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name
        );
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return $this->laravel->getNamespace();
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * Determine if the class already exists.
     *
     * @param string $rawName
     *
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        return $this->files->exists($this->getPath($this->qualifyClass($rawName)));
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     *
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        $class = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);

        return $class;

    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        return str_replace('DummyClass', $class, $stub);
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $namespace = $this->getNamespace($name);

        $stub = str_replace(
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel'],
            [$namespace, $this->rootNamespace(), config('auth.providers.users.model')],
            $stub
        );

        return $this;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
        ];
    }

    protected function getRelativePath($path)
    {
        return str_replace(base_path() . '/', '', $path);

    }
}
