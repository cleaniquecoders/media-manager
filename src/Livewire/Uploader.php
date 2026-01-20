<?php

namespace CleaniqueCoders\MediaManager\Livewire;

use CleaniqueCoders\MediaManager\Concerns\HandlesPreview;
use CleaniqueCoders\MediaManager\Concerns\HandlesUpload;
use CleaniqueCoders\MediaManager\Services\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Uploader extends Component
{
    use HandlesPreview;
    use HandlesUpload;
    use WithFileUploads;

    #[Locked]
    public ?string $modelClass = null;

    #[Locked]
    public ?int $modelId = null;

    #[Locked]
    public string $collection = 'default';

    #[Locked]
    public int $maxFiles = 1;

    #[Locked]
    public ?array $acceptedTypes = null;

    #[Locked]
    public array $withProperties = [];

    /** @var array<TemporaryUploadedFile> */
    public array $uploads = [];

    public array $uploadProgress = [];

    public array $uploadErrors = [];

    public array $existingMedia = [];

    public array $customProperties = [];

    /**
     * @param  (Model&HasMedia)|null  $model
     */
    public function mount(
        ?Model $model = null,
        string $collection = 'default',
        int $maxFiles = 1,
        ?array $acceptedTypes = null,
        array $withProperties = []
    ): void {
        if ($model) {
            $this->modelClass = get_class($model);
            $this->modelId = $model->getKey();
            $this->loadExistingMedia($model, $collection);
        }

        $this->collection = $collection;
        $this->maxFiles = $maxFiles;
        $this->acceptedTypes = $acceptedTypes;
        $this->withProperties = $withProperties;
    }

    public function updatedUploads(): void
    {
        $this->uploadErrors = [];

        foreach ($this->uploads as $index => $file) {
            if ($file instanceof TemporaryUploadedFile) {
                $errors = $this->validateUploadedFile($file);
                if (! empty($errors)) {
                    $this->uploadErrors[$index] = $errors;
                }
            }
        }
    }

    public function removeUpload(int $index): void
    {
        if (isset($this->uploads[$index])) {
            unset($this->uploads[$index]);
            unset($this->uploadProgress[$index]);
            unset($this->uploadErrors[$index]);
            unset($this->customProperties[$index]);
            $this->uploads = array_values($this->uploads);
        }
    }

    public function removeExisting(int $mediaId): void
    {
        $media = Media::find($mediaId);
        if ($media && $this->canManageMedia($media)) {
            $media->delete();
            $this->existingMedia = array_filter(
                $this->existingMedia,
                fn ($m) => $m['id'] !== $mediaId
            );
            $this->existingMedia = array_values($this->existingMedia);
            $this->dispatch('media-removed', mediaId: $mediaId);
        }
    }

    public function save(): void
    {
        if (empty($this->uploads) || ! $this->modelClass || ! $this->modelId) {
            return;
        }

        $model = $this->getModel();
        if (! $model) {
            return;
        }

        $service = app(MediaService::class);
        $uploaded = [];

        foreach ($this->uploads as $index => $file) {
            if (isset($this->uploadErrors[$index]) && ! empty($this->uploadErrors[$index])) {
                continue;
            }

            $properties = $this->customProperties[$index] ?? [];
            $media = $service->upload($model, $file, $this->collection, $properties);
            $uploaded[] = $this->getPreviewData($media);
        }

        $this->uploads = [];
        $this->uploadProgress = [];
        $this->customProperties = [];
        $this->existingMedia = array_merge($this->existingMedia, $uploaded);

        // Enforce max files limit for single file collections
        if ($this->maxFiles === 1 && count($this->existingMedia) > 1) {
            // Keep only the latest
            $this->existingMedia = [end($this->existingMedia)];
        }

        $this->dispatch('media-uploaded', media: $uploaded);
    }

    public function updatePropertyValue(int $index, string $property, mixed $value): void
    {
        $this->customProperties[$index][$property] = $value;
    }

    #[On('upload:progress')]
    public function handleUploadProgress(string $name, int $progress): void
    {
        // Find the index of the upload by name
        foreach ($this->uploads as $index => $upload) {
            if ($upload instanceof TemporaryUploadedFile && $upload->getFilename() === $name) {
                $this->uploadProgress[$index] = $progress;
                break;
            }
        }
    }

    /**
     * @param  Model&HasMedia  $model
     */
    protected function loadExistingMedia(Model $model, string $collection): void
    {
        $media = $model->getMedia($collection);
        $this->existingMedia = $media->map(fn ($m) => $this->getPreviewData($m))->toArray();
    }

    /**
     * @return (Model&HasMedia)|null
     */
    protected function getModel(): ?Model
    {
        if (! $this->modelClass || ! $this->modelId) {
            return null;
        }

        return $this->modelClass::find($this->modelId);
    }

    protected function canManageMedia(Media $media): bool
    {
        return $media->model_type === $this->modelClass
            && $media->model_id === $this->modelId;
    }

    public function render(): View
    {
        /** @var view-string $viewName */
        $viewName = 'media-manager::livewire.media-uploader';

        return view($viewName, [
            'acceptedTypesString' => $this->getAcceptedTypesString($this->acceptedTypes),
            'maxFileSizeMb' => $this->getMaxFileSizeInMb(),
            'canUploadMore' => count($this->existingMedia) + count($this->uploads) < $this->maxFiles || $this->maxFiles > 1,
            'isMultiple' => $this->maxFiles > 1,
        ]);
    }
}
