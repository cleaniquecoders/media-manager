<?php

namespace CleaniqueCoders\MediaManager\Livewire;

use CleaniqueCoders\MediaManager\Concerns\HandlesPreview;
use CleaniqueCoders\MediaManager\Services\MediaService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Browser extends Component
{
    use HandlesPreview;
    use WithPagination;

    #[Url]
    public string $view = 'grid';

    #[Url]
    public string $search = '';

    #[Url]
    public string $collection = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public array $selected = [];

    public ?int $previewMediaId = null;

    public bool $showDeleteConfirm = false;

    public function mount(): void
    {
        $this->view = config('media-manager.browser.default_view', 'grid');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCollection(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function toggleView(): void
    {
        $this->view = $this->view === 'grid' ? 'list' : 'grid';
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function toggleSelect(int $mediaId): void
    {
        if (in_array($mediaId, $this->selected)) {
            $this->selected = array_values(array_diff($this->selected, [$mediaId]));
        } else {
            $this->selected[] = $mediaId;
        }
    }

    public function selectAll(): void
    {
        $this->selected = collect($this->media()->items())->pluck('id')->toArray();
    }

    public function deselectAll(): void
    {
        $this->selected = [];
    }

    public function openPreview(int $mediaId): void
    {
        $this->previewMediaId = $mediaId;
    }

    public function closePreview(): void
    {
        $this->previewMediaId = null;
    }

    public function confirmDelete(): void
    {
        $this->showDeleteConfirm = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
    }

    public function deleteSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $service = app(MediaService::class);
        $service->deleteMultiple($this->selected);

        $this->selected = [];
        $this->showDeleteConfirm = false;

        $this->dispatch('media-deleted');
    }

    public function deleteSingle(int $mediaId): void
    {
        $media = Media::find($mediaId);
        if ($media) {
            $media->delete();
            $this->closePreview();
            $this->dispatch('media-deleted');
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->collection = '';
        $this->type = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    #[Computed]
    public function media(): LengthAwarePaginator
    {
        $service = app(MediaService::class);

        return $service->getMedia([
            'search' => $this->search,
            'collection' => $this->collection,
            'type' => $this->type,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ], config('media-manager.browser.items_per_page', 24));
    }

    #[Computed]
    public function collections(): array
    {
        $service = app(MediaService::class);

        return $service->getCollections()->toArray();
    }

    #[Computed]
    public function previewMedia(): ?array
    {
        if (! $this->previewMediaId) {
            return null;
        }

        $media = Media::find($this->previewMediaId);

        return $media ? $this->getPreviewData($media) : null;
    }

    #[Computed]
    public function typeOptions(): array
    {
        return [
            'image' => 'Images',
            'video' => 'Videos',
            'audio' => 'Audio',
            'pdf' => 'PDF',
            'document' => 'Documents',
            'spreadsheet' => 'Spreadsheets',
        ];
    }

    public function render(): View
    {
        /** @var view-string $viewName */
        $viewName = 'media-manager::livewire.media-browser';

        return view($viewName, [
            'gridColumns' => config('media-manager.browser.columns', 4),
        ]);
    }
}
