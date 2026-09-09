<?php

declare(strict_types=1);

namespace HuberCMS\Traits;

/**
 * HasSoftDelete
 *
 * Adds soft-delete functionality to models.
 * Sets deleted_at instead of actually deleting the row.
 *
 * @package HuberCMS\Traits
 */
trait HasSoftDelete
{
    /**
     * Soft-deletes this model by setting deleted_at.
     */
    public function softDelete(): bool
    {
        $this->attributes['deleted_at'] = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Restores a soft-deleted model.
     */
    public function restore(): bool
    {
        $this->attributes['deleted_at'] = null;
        return $this->save();
    }

    /**
     * Checks whether the model has been soft-deleted.
     */
    public function isDeleted(): bool
    {
        return $this->attributes['deleted_at'] !== null;
    }
}
