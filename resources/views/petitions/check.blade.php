@extends('layouts.master')

@section('title', 'Petition ' . $petitionId)

@section('body')
<div class="container centered">
    <h1>
        <a href="/">
            UK Petition Tracker
        </a>
    </h1>
    <h2>
        Checking Petition: {{ $petitionId }}
    </h2>
    <p>
        We found a petition with description:<br> <em>{{ $petition->description }}</em>
    </p>
    <p>
        This petition is: <strong>{{ $petition->status }}</strong>
    </p>
    <p>
        This petition has <strong>{{ $petition->last_count }}</strong> signatures (last checked on {{ $petition->last_count_timestamp }})
    </p>
</div>
<div class="container centered">
    <h2>Latest counts</h2>
    <table>
        <thead>
            <tr>
                <th>
                    Timestamp
                </th>
                <th>
                    Signature count
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dataPoints as $thisData)
                <tr>
                    <td>{{ $thisData->data_timestamp }}</td>
                    <td>{{ $thisData->count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection