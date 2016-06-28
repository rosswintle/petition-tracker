@extends('layouts.master')

@section('title', 'Track growth of UK government petitions')

@section('body')
        <header>
            <h1>
                <a href="/">
                    Petition Tracker
                </a>
            </h1>
        </header>
        <div class="container">
            {!! Form::open(['action' => 'PetitionController@Check']) !!}
                <p>
                    {!! Form::label('petitionId', 'Enter a petition ID') !!}
                    <br>
                    {!! Form::text('petitionId') !!}
                </p>
                <p>
                    {!! Form::submit('Track it!') !!}
                </p>
            {!! Form::close() !!}
        </div>
@endsection