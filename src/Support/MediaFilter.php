<?php

namespace CleaniqueCoders\MediaManager\Support;

use Illuminate\Database\Eloquent\Builder;

class MediaFilter
{
    /**
     * Apply filters to the media query.
     */
    public function apply(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $this->applySearch($query, $filters['search']);
        }

        if (! empty($filters['collection'])) {
            $this->applyCollection($query, $filters['collection']);
        }

        if (! empty($filters['mime_type'])) {
            $this->applyMimeType($query, $filters['mime_type']);
        }

        if (! empty($filters['type'])) {
            $this->applyType($query, $filters['type']);
        }

        if (! empty($filters['date_from'])) {
            $this->applyDateFrom($query, $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $this->applyDateTo($query, $filters['date_to']);
        }

        if (! empty($filters['model_type'])) {
            $this->applyModelType($query, $filters['model_type']);
        }

        if (! empty($filters['model_id'])) {
            $this->applyModelId($query, $filters['model_id']);
        }

        return $query;
    }

    /**
     * Apply search filter to filename and custom properties.
     */
    protected function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('file_name', 'like', "%{$search}%")
                ->orWhere('custom_properties', 'like', "%{$search}%");
        });
    }

    /**
     * Apply collection filter.
     */
    protected function applyCollection(Builder $query, string $collection): void
    {
        $query->where('collection_name', $collection);
    }

    /**
     * Apply MIME type filter.
     */
    protected function applyMimeType(Builder $query, string $mimeType): void
    {
        $query->where('mime_type', $mimeType);
    }

    /**
     * Apply type category filter (image, video, document, etc.).
     */
    protected function applyType(Builder $query, string $type): void
    {
        $query->where(function (Builder $q) use ($type) {
            match ($type) {
                'image' => $q->where('mime_type', 'like', 'image/%'),
                'video' => $q->where('mime_type', 'like', 'video/%'),
                'audio' => $q->where('mime_type', 'like', 'audio/%'),
                'pdf' => $q->where('mime_type', 'application/pdf'),
                'document' => $q->where(function (Builder $subQ) {
                    $subQ->where('mime_type', 'like', '%word%')
                        ->orWhere('mime_type', 'like', '%document%');
                }),
                'spreadsheet' => $q->where(function (Builder $subQ) {
                    $subQ->where('mime_type', 'like', '%sheet%')
                        ->orWhere('mime_type', 'like', '%excel%');
                }),
                default => null,
            };
        });
    }

    /**
     * Apply date from filter.
     */
    protected function applyDateFrom(Builder $query, string $date): void
    {
        $query->whereDate('created_at', '>=', $date);
    }

    /**
     * Apply date to filter.
     */
    protected function applyDateTo(Builder $query, string $date): void
    {
        $query->whereDate('created_at', '<=', $date);
    }

    /**
     * Apply model type filter.
     */
    protected function applyModelType(Builder $query, string $modelType): void
    {
        $query->where('model_type', $modelType);
    }

    /**
     * Apply model ID filter.
     */
    protected function applyModelId(Builder $query, int $modelId): void
    {
        $query->where('model_id', $modelId);
    }
}
