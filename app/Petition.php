<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Petition extends Model
{

    protected $fillable = ['remote_id', 'description', 'status', 'last_count', ];

    public function markError() {
        $this->updateStatus( 'error');
    }

    public function markMissing() {
        $this->updateStatus( 'missing' );
    }

    public function updateStatus( $newStatus ) {
        $this->status = $newStatus;
        $this->save();
    }
}
