<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Petition extends Model
{
    protected $fillable = ['remote_id', 'description', 'status', 'last_count'];

    public function markError(): void
    {
        $this->updateStatus('error');
    }

    public function markMissing(): void
    {
        $this->updateStatus('missing');
    }

    public function markOpen(): void
    {
        $this->updateStatus('open');
    }

    public function updateStatus($newStatus): void
    {
        $this->status = $newStatus;
        $this->save();
    }
}
