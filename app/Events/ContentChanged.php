<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

class ContentChanged
{
    use Dispatchable;

    public function __construct(public Model $model, public string $action) {}
}
