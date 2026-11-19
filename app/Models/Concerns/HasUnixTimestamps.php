<?php

namespace App\Models\Concerns;

trait HasUnixTimestamps
{
    public function initializeHasUnixTimestamps(): void
    {
        $this->timestamps = false;
    }

    protected static function bootHasUnixTimestamps(): void
    {
        static::creating(function ($model) {
            $now = time();
            if ($model->isFillable('createtime') && empty($model->createtime)) {
                $model->createtime = $now;
            }
            if ($model->isFillable('updatetime')) {
                $model->updatetime = $now;
            }
        });

        static::updating(function ($model) {
            if ($model->isFillable('updatetime')) {
                $model->updatetime = time();
            }
        });
    }
}
