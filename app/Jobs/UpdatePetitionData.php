<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Petition;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdatePetitionData extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $petitionId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( $petitionId )
    {
        $this->petitionId = $petitionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle( )
    {

        $petition = Petition::find( $this->petitionId );

    }
}
