@extends('layouts.petition')

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
            <form method="POST" action="{{ route('check-petition-post') }}">
                @csrf
                <p>
                    <label for="petition-id">Enter a petition ID</label>
                    <br>
                    <input type="number" id="petition-id" name="petitionId" />
                </p>
                <p>
                    <input type="submit" value="Track it!" />
                </p>
            </form>
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
