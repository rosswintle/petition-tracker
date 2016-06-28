@extends('layouts.master')

@section('title', 'Petition ' . $petitionId)

@section('body')
<h1>Checking Petition: {{ $petitionId }}</h1>
<p>
    We found a petition with description:<br> <em>{{ $petition->description }}</em>
</p>
<p>
    This petition is: <strong>{{ $petition->status }}</strong>
</p>
<p>
    This petition has <strong>{{ $petition->last_count }}</strong> signatures (last checked on {{ $petition->last_count_timestamp }})
</p>
@endsection