<?php

use App\Filament\Resources\GalleryImages\Pages\ManageGalleryImages;
use App\Models\GalleryImage;
use App\Models\User;
use Filament\Actions\CreateAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('an uploaded gallery photo is resized and stored as WebP', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());

    Livewire::test(ManageGalleryImages::class)
        ->callAction(CreateAction::class, data: [
            'path' => UploadedFile::fake()->image('balajaz.jpg', 2000, 1000),
            'alt' => 'Балајаж на долга коса',
            'is_visible' => true,
        ])
        ->assertHasNoActionErrors();

    $image = GalleryImage::sole();
    Storage::disk('public')->assertExists($image->path);

    [$width, $height, $type] = getimagesizefromstring(Storage::disk('public')->get($image->path));

    expect($image->path)->toStartWith('gallery/')->toEndWith('.webp')
        ->and($type)->toBe(IMAGETYPE_WEBP)
        ->and([$width, $height])->toBe([1600, 800]);
});

test('deleting a gallery photo removes its file', function () {
    Storage::fake('public');
    Storage::disk('public')->put('gallery/photo.webp', 'image');
    $image = GalleryImage::factory()->create(['path' => 'gallery/photo.webp']);

    $image->delete();

    Storage::disk('public')->assertMissing('gallery/photo.webp');
});

test('the public gallery shows only visible photos', function () {
    $visible = GalleryImage::factory()->create(['alt' => 'Видлива фотографија']);
    $hidden = GalleryImage::factory()->hidden()->create(['alt' => 'Скриена фотографија']);

    $this->get(route('gallery'))
        ->assertOk()
        ->assertSee($visible->alt)
        ->assertSee($visible->url())
        ->assertDontSee($hidden->alt);
});
