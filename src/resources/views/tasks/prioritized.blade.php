@extends('layouts.app')

@section('content')
    <h1>Tarefas Priorizadas</h1>

    @if($prioritizedList->count())
        <ol class="list-group list-group-numbered">
            @foreach($prioritizedList as $task)
                <li class="list-group-item">{{ $task }}</li>
            @endforeach
        </ol>
    @else
        <p>Não foi possível priorizar as tarefas no momento.</p>
    @endif

    <a href="{{ route('tasks.index') }}" class="btn btn-secondary mt-3">Voltar</a>
@endsection
