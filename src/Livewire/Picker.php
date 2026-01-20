<?php

namespace CleaniqueCoders\MediaManager\Livewire;

use CleaniqueCoders\MediaManager\Concerns\HandlesPreview;
use CleaniqueCoders\MediaManager\Services\MediaService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Picker extends Component
{
    use HandlesPreview;
    use WithPagination;

    #[Modelable]
    public array $selectedIds = [];

    public bool $isOpen = false;

    public bool $multiple = false;

    public ?string $collection = null;

    public ?string $type = null;

    public string $search = '';

    public function mount(
        array $selectedIds = [],
        bool $multiple = false,
        ?string $collection = null,
        ?string $type = null
    ): void {
        $this->selectedIds = $selectedIds;
        $this->multiple = $multiple;
        $this->collection = $collection;
        $this->type = $type;
    }

    public function openPicker(): void
    {
        $this->isOpen = true;
    }

    public function closePicker(): void
    {
        $this->isOpen = false;
    }

    public function toggleSelect(int $mediaId): void
    {
        if ($this->multiple) {
            if (in_array($mediaId, $this->selectedIds)) {
                $this->selectedIds = array_values(array_diff($this->selectedIds, [$mediaId]));
            } else {
                $this->selectedIds[] = $mediaId;
            }
        } else {
            $this->selectedIds = [$mediaId];
        }
    }

    public function confirm(): void
    {
        $this->dispatch('media-selected', mediaIds: $this->selectedIds);
        $this->closePicker();
    }

    public function clearSelection(): void
    {
        $this->selectedIds = [];
    }

    public function removeSelected(int $mediaId): void
    {
        $this->selectedIds = array_values(array_diff($this->selectedIds, [$mediaId]));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function media(): LengthAwarePaginator
    {
        $service = app(MediaService::class);

        $filters = [
            'search' => $this->search,
        ];

        if ($this->collection) {
            $filters['collection'] = $this->collection;
        }

        if ($this->type) {
            $filters['type'] = $this->type;
        }

        return $service->getMedia($filters, 20);
    }

    #[Computed]
    public function selectedMedia(): array
    {
        if (empty($this->selectedIds)) {
            return [];
        }

        return Media::whereIn('id', $this->selectedIds)
            ->get()
            ->map(fn ($m) => $this->getPreviewData($m))
            ->toArray();
    }

    public function render(): View
    {
        /** @var view-string $viewName */
        $viewName = 'media-manager::livewire.media-picker';

        return view($viewName);
    }
}
