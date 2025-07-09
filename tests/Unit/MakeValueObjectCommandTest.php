<?php

namespace MichaelRavedoni\LaravelValueObjects\Tests\Unit;

use MichaelRavedoni\LaravelValueObjects\Tests\TestCase;
use Illuminate\Support\Facades\File;

class MakeValueObjectCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        // Clean up any created files/directories after each test
        if (File::exists(app_path('ValueObjects/MyNewValueObject.php'))) {
            File::delete(app_path('ValueObjects/MyNewValueObject.php'));
        }
        if (File::exists(app_path('ValueObjects')) && File::isDirectory(app_path('ValueObjects')) && !count(File::files(app_path('ValueObjects')))) {
            File::deleteDirectory(app_path('ValueObjects'));
        }

        parent::tearDown();
    }

    /** @test */
    public function it_can_create_a_new_value_object()
    {
        // Make sure the app/ValueObjects directory doesn't exist before running the command
        if (File::exists(app_path('ValueObjects'))) {
            File::deleteDirectory(app_path('ValueObjects'));
        }

        $this->artisan('make:value-object MyNewValueObject')
            ->assertExitCode(0);

        // Check if the file was created in the correct path with the correct content
        $this->assertFileExists(app_path('ValueObjects/MyNewValueObject.php'));

        $fileContent = File::get(app_path('ValueObjects/MyNewValueObject.php'));

        $this->assertStringContainsString('namespace App\\ValueObjects;', $fileContent);
        $this->assertStringContainsString('class MyNewValueObject extends BaseValueObject', $fileContent);
        $this->assertStringContainsString('use MichaelRavedoni\\LaravelValueObjects\\ValueObjects\\BaseValueObject;', $fileContent);
    }

    /** @test */
    public function it_does_not_overwrite_existing_value_object_without_force()
    {
        // Create a dummy value object file
        File::makeDirectory(app_path('ValueObjects'), 0755, true);
        File::put(app_path('ValueObjects/MyNewValueObject.php'), '<?php // Dummy content');

        $this->artisan('make:value-object MyNewValueObject')
            ->assertExitCode(1) // Should fail with code 1 if file exists
            ->expectsOutputToContain('already exists!');

        // Ensure the dummy content is still there
        $this->assertEquals('<?php // Dummy content', File::get(app_path('ValueObjects/MyNewValueObject.php')));
    }

    /** @test */
    public function it_can_overwrite_existing_value_object_with_force()
    {
        // Create a dummy value object file
        File::makeDirectory(app_path('ValueObjects'), 0755, true);
        File::put(app_path('ValueObjects/MyNewValueObject.php'), '<?php // Dummy content');

        $this->artisan('make:value-object MyNewValueObject --force')
            ->assertExitCode(0);

        // Ensure the content has been overwritten by the stub
        $fileContent = File::get(app_path('ValueObjects/MyNewValueObject.php'));
        $this->assertStringContainsString('class MyNewValueObject extends BaseValueObject', $fileContent);
        $this->assertStringNotContainsString('Dummy content', $fileContent);
    }
}