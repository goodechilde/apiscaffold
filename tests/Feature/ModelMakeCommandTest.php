<?php

namespace Goodechilde\Arche\Tests\Feature;


use Illuminate\Support\Facades\Artisan;
use Goodechilde\Arche\Tests\TestCase;

class ModelMakeCommandTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->removeGeneratedFiles();
    }

    public function test_it_generates_a_model()
    {
        //Make sure no artifact related to Post exists
        $this->assertFalse(file_exists(app_path('/Post.php')));


        $this->artisan('arche:model Post');

        $this->assertTrue(file_exists(app_path('/Post.php')));

        $actualOutput = Artisan::output();

        $expectedOutput = "Model created successfully in /app/Post.php" . PHP_EOL;
        $this->assertSame($expectedOutput, $actualOutput);
    }

    public function test_it_generates_a_model_in_the_given_directory_and_namespace()
    {
        //Make sure no artifact related to Post exists
        $this->assertFileDoesNotExist((app_path('Models/Post.php')));

        $this->artisan('arche:model Models/Post');

        $this->assertFileExists(app_path('Models/Post.php'));

        $actualOutput = Artisan::output();

        $expectedOutput = "Model created successfully in /app/Models/Post.php" . PHP_EOL;
        $this->assertSame($expectedOutput, $actualOutput);
    }
}
