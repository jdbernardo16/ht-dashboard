<?php

namespace Tests\Unit\Rules;

use Tests\TestCase;
use App\Rules\ValidImageDimensions;
use Illuminate\Http\UploadedFile;

class ValidImageDimensionsTest extends TestCase
{
    /** @test */
    public function it_passes_for_images_within_default_limits()
    {
        $rule = new ValidImageDimensions();
        $validImage = UploadedFile::fake()->image('test.jpg', 800, 600);
        
        $this->assertTrue($rule->passes('image', $validImage));
    }

    /** @test */
    public function it_passes_for_images_at_exact_limits()
    {
        $rule = new ValidImageDimensions(1000, 1000);
        $validImage = UploadedFile::fake()->image('test.jpg', 1000, 1000);
        
        $this->assertTrue($rule->passes('image', $validImage));
    }

    /** @test */
    public function it_fails_for_images_wider_than_maximum()
    {
        $rule = new ValidImageDimensions(800, 600);
        $invalidImage = UploadedFile::fake()->image('test.jpg', 1200, 400);
        
        $this->assertFalse($rule->passes('image', $invalidImage));
    }

    /** @test */
    public function it_fails_for_images_taller_than_maximum()
    {
        $rule = new ValidImageDimensions(800, 600);
        $invalidImage = UploadedFile::fake()->image('test.jpg', 400, 900);
        
        $this->assertFalse($rule->passes('image', $invalidImage));
    }

    /** @test */
    public function it_fails_for_images_both_wider_and_taller_than_maximum()
    {
        $rule = new ValidImageDimensions(800, 600);
        $invalidImage = UploadedFile::fake()->image('test.jpg', 1200, 900);
        
        $this->assertFalse($rule->passes('image', $invalidImage));
    }

    /** @test */
    public function it_passes_for_images_smaller_than_minimum()
    {
        $rule = new ValidImageDimensions(800, 600, 100, 100);
        $validImage = UploadedFile::fake()->image('test.jpg', 200, 150);
        
        $this->assertTrue($rule->passes('image', $validImage));
    }

    /** @test */
    public function it_fails_for_images_narrower_than_minimum()
    {
        $rule = new ValidImageDimensions(800, 600, 100, 100);
        $invalidImage = UploadedFile::fake()->image('test.jpg', 50, 200);
        
        $this->assertFalse($rule->passes('image', $invalidImage));
    }

    /** @test */
    public function it_fails_for_images_shorter_than_minimum()
    {
        $rule = new ValidImageDimensions(800, 600, 100, 100);
        $invalidImage = UploadedFile::fake()->image('test.jpg', 200, 50);
        
        $this->assertFalse($rule->passes('image', $invalidImage));
    }

    /** @test */
    public function it_fails_for_images_both_narrower_and_shorter_than_minimum()
    {
        $rule = new ValidImageDimensions(800, 600, 100, 100);
        $invalidImage = UploadedFile::fake()->image('test.jpg', 50, 50);
        
        $this->assertFalse($rule->passes('image', $invalidImage));
    }

    /** @test */
    public function it_passes_for_square_images_within_limits()
    {
        $rule = new ValidImageDimensions(1000, 1000, 100, 100);
        $validImage = UploadedFile::fake()->image('test.jpg', 500, 500);
        
        $this->assertTrue($rule->passes('image', $validImage));
    }

    /** @test */
    public function it_fails_for_non_image_files()
    {
        $rule = new ValidImageDimensions(800, 600);
        $nonImageFile = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        $this->assertFalse($rule->passes('image', $nonImageFile));
    }

    /** @test */
    public function it_fails_for_corrupted_image_files()
    {
        $rule = new ValidImageDimensions(800, 600);
        $corruptedFile = UploadedFile::fake()->create('corrupted.jpg', 1024, 'image/jpeg');
        
        $this->assertFalse($rule->passes('image', $corruptedFile));
    }

    /** @test */
    public function it_handles_null_value()
    {
        $rule = new ValidImageDimensions(800, 600);
        
        $this->assertFalse($rule->passes('image', null));
    }

    /** @test */
    public function it_handles_empty_string()
    {
        $rule = new ValidImageDimensions(800, 600);
        
        $this->assertFalse($rule->passes('image', ''));
    }

    /** @test */
    public function it_provides_correct_error_message_for_maximum_dimensions()
    {
        $rule = new ValidImageDimensions(800, 600);
        $invalidImage = UploadedFile::fake()->image('test.jpg', 1200, 900);
        $rule->passes('image', $invalidImage);
        
        $message = $rule->message();
        $this->assertStringContains('must not exceed', $message);
        $this->assertStringContains('800x600', $message);
    }

    /** @test */
    public function it_provides_correct_error_message_for_minimum_dimensions()
    {
        $rule = new ValidImageDimensions(800, 600, 100, 100);
        $invalidImage = UploadedFile::fake()->image('test.jpg', 50, 50);
        $rule->passes('image', $invalidImage);
        
        $message = $rule->message();
        $this->assertStringContains('must be at least', $message);
        $this->assertStringContains('100x100', $message);
    }

    /** @test */
    public function it_provides_correct_error_message_for_both_min_and_max()
    {
        $rule = new ValidImageDimensions(800, 600, 100, 100);
        $invalidImage = UploadedFile::fake()->image('test.jpg', 50, 50);
        $rule->passes('image', $invalidImage);
        
        $message = $rule->message();
        $this->assertStringContains('must be between', $message);
        $this->assertStringContains('100x100', $message);
        $this->assertStringContains('800x600', $message);
    }

    /** @test */
    public function it_works_with_only_maximum_dimensions()
    {
        $rule = new ValidImageDimensions(1000, 800);
        $validImage = UploadedFile::fake()->image('test.jpg', 800, 600);
        $invalidImage = UploadedFile::fake()->image('test.jpg', 1200, 600);
        
        $this->assertTrue($rule->passes('image', $validImage));
        $this->assertFalse($rule->passes('image', $invalidImage));
    }

    /** @test */
    public function it_works_with_only_minimum_dimensions()
    {
        $rule = new ValidImageDimensions(null, null, 200, 150);
        $validImage = UploadedFile::fake()->image('test.jpg', 400, 300);
        $invalidImage = UploadedFile::fake()->image('test.jpg', 100, 75);
        
        $this->assertTrue($rule->passes('image', $validImage));
        $this->assertFalse($rule->passes('image', $invalidImage));
    }

    /** @test */
    public function it_handles_very_large_images()
    {
        $rule = new ValidImageDimensions(2000, 2000);
        $largeImage = UploadedFile::fake()->image('test.jpg', 1500, 1200);
        
        $this->assertTrue($rule->passes('image', $largeImage));
    }

    /** @test */
    public function it_handles_very_small_images()
    {
        $rule = new ValidImageDimensions(1000, 1000, 10, 10);
        $smallImage = UploadedFile::fake()->image('test.jpg', 20, 15);
        
        $this->assertTrue($rule->passes('image', $smallImage));
    }
}