<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasAuditFields
{
    protected static function bootHasAuditFields(): void
    {
        static::creating(function (Model $model): void {
            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function (Model $model): void {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function (Model $model): void {
            if (! method_exists($model, 'isForceDeleting') || ! $model->isForceDeleting()) {
                if (Auth::check()) {
                    $model->deleted_by = Auth::id();
                    $model->saveQuietly();
                }
            }
        });
    }
}
