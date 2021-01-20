@extends('layouts.master')

@section('title', 'Track growth of UK government petitions')

@section('body')
        <header>
            <h1>
                <a href="/">
                    UK Petition Tracker
                </a>
            </h1>
        </header>
        <div class="container centered">
            {!! Form::open(['action' => 'PetitionController@Check']) !!}
                <p>
                    {!! Form::label('petitionId', 'Enter a petition ID') !!}
                    <br>
                    {!! Form::number('petitionId') !!}
                </p>
                <p>
                    {!! Form::submit('Track it!') !!}
                </p>
            {!! Form::close() !!}
        </div>
        <div class="container">
            <h2>Open Petitions</h2>
            <ul>
                @foreach ($petitions as $thisPetition)
                    <li>
                        <a href="{{ route('check-petition', ['petition_id' => $thisPetition->remote_id])  }}">
                            <strong>{{ $thisPetition->remote_id }}</strong>: {{ $thisPetition->description }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
@endsection
