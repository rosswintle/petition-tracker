<?php

namespace App\Jobs;

use App\Http\Controllers\PetitionController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdatePetitionData implements ShouldQueue
{
    use Queueable;

    protected int $petitionId;

    /**
     * Create a new job instance.
     */
    public function __construct($petitionId)
    {
        $this->petitionId = $petitionId;
    }

    /**
     * Execute the job.
     */
    public function handle(PetitionController $controller): void
    {
        $controller->update($this->petitionId);
    }
}
