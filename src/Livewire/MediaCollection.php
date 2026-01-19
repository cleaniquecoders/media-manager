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

class MediaCollection extends Component
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
    public bool $sortable = true;

    #[Locked]
    public array $withProperties = [];

    #[Locked]
    public ?array $acceptedTypes = null;

    #[Locked]
    public ?int $maxFiles = null;

    /** @var array<TemporaryUploadedFile> */
    public array $uploads = [];

    public array $uploadProgress = [];

    public array $uploadErrors = [];

    public array $media = [];

    public ?int $editingMediaId = null;

    public array $editingProperties = [];

    public bool $showUploadZone = false;

    /**
     * @param  (Model&HasMedia)|null  $model
     */
    public function mount(
        ?Model $model = null,
        string $collection = 'default',
        bool $sortable = true,
        array $withProperties = [],
        ?array $acceptedTypes = null,
        ?int $maxFiles = null
    ): void {
        if ($model) {
            $this->modelClass = get_class($model);
            $this->modelId = $model->getKey();
            $this->loadMedia($model, $collection);
        }

        $this->collection = $collection;
        $this->sortable = $sortable;
        $this->withProperties = $withProperties;
        $this->acceptedTypes = $acceptedTypes;
        $this->maxFiles = $maxFiles;
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

    public function toggleUploadZone(): void
    {
        $this->showUploadZone = ! $this->showUploadZone;
    }

    public function removeUpload(int $index): void
    {
        if (isset($this->uploads[$index])) {
            unset($this->uploads[$index]);
            unset($this->uploadProgress[$index]);
            unset($this->uploadErrors[$index]);
            $this->uploads = array_values($this->uploads);
        }
    }

    public function uploadFiles(): void
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

            if ($this->maxFiles && count($this->media) + count($uploaded) >= $this->maxFiles) {
                break;
            }

            $media = $service->upload($model, $file, $this->collection);
            $uploaded[] = $this->getPreviewData($media);
        }

        $this->uploads = [];
        $this->uploadProgress = [];
        $this->showUploadZone = false;
        $this->media = array_merge($this->media, $uploaded);

        $this->dispatch('media-uploaded', media: $uploaded);
    }

    public function removeMedia(int $mediaId): void
    {
        $media = Media::find($mediaId);
        if ($media && $this->canManageMedia($media)) {
            $media->delete();
            $this->media = array_values(array_filter(
                $this->media,
                fn ($m) => $m['id'] !== $mediaId
            ));
            $this->dispatch('media-removed', mediaId: $mediaId);
        }
    }

    public function editMedia(int $mediaId): void
    {
        $this->editingMediaId = $mediaId;
        $mediaItem = collect($this->media)->firstWhere('id', $mediaId);

        if ($mediaItem) {
            $this->editingProperties = [
                'name' => $mediaItem['name'] ?? '',
            ];

            foreach ($this->withProperties as $prop) {
                $this->editingProperties[$prop] = $mediaItem['custom_properties'][$prop] ?? '';
            }
        }
    }

    public function cancelEdit(): void
    {
        $this->editingMediaId = null;
        $this->editingProperties = [];
    }

    public function saveProperties(): void
    {
        if (! $this->editingMediaId) {
            return;
        }

        $media = Media::find($this->editingMediaId);
        if (! $media || ! $this->canManageMedia($media)) {
            return;
        }

        $service = app(MediaService::class);

        // Update name if provided
        if (isset($this->editingProperties['name'])) {
            $service->rename($media, $this->editingProperties['name']);
        }

        // Update custom properties
        $customProps = array_filter(
            $this->editingProperties,
            fn ($key) => $key !== 'name' && in_array($key, $this->withProperties),
            ARRAY_FILTER_USE_KEY
        );

        if (! empty($customProps)) {
            $service->updateProperties($media, $customProps);
        }

        // Refresh local media array
        $this->media = array_map(function ($m) use ($media) {
            if ($m['id'] === $media->id) {
                return $this->getPreviewData($media->fresh());
            }

            return $m;
        }, $this->media);

        $this->editingMediaId = null;
        $this->editingProperties = [];

        $this->dispatch('media-updated', mediaId: $media->id);
    }

    public function updateOrder(array $orderedIds): void
    {
        if (! $this->sortable) {
            return;
        }

        Media::setNewOrder($orderedIds);

        // Reorder local array
        $this->media = collect($this->media)
            ->sortBy(fn ($m) => array_search($m['id'], $orderedIds))
            ->values()
            ->toArray();

        $this->dispatch('media-reordered', order: $orderedIds);
    }

    #[On('upload:progress')]
    public function handleUploadProgress(string $name, int $progress): void
    {
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
    protected function loadMedia(Model $model, string $collection): void
    {
        $mediaItems = $model->getMedia($collection)->sortBy('order_column');
        $this->media = $mediaItems->map(fn ($m) => $this->getPreviewData($m))->values()->toArray();
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
        $viewName = 'media-manager::livewire.media-collection';

        return view($viewName, [
            'acceptedTypesString' => $this->getAcceptedTypesString($this->acceptedTypes),
            'maxFileSizeMb' => $this->getMaxFileSizeInMb(),
            'canUploadMore' => ! $this->maxFiles || count($this->media) < $this->maxFiles,
        ]);
    }
}
