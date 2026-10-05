<?php

namespace App\Observers;

use App\Events\ContentChanged;
use Illuminate\Database\Eloquent\Model;

class ContentObserver
{
    public function saved(Model $model): void
    {
        ContentChanged::dispatch($model, $model->wasRecentlyCreated ? 'created' : 'updated');
    }

    public function deleted(Model $model): void
    {
        ContentChanged::dispatch($model, 'deleted');
    }
}
