@extends('layouts.app')

@section('content')
    <h1>Detalhes da Tarefa</h1>

    <div class="card">
        <div class="card-header">
            {{ $task->title }}
        </div>
        <div class="card-body">
            <p><strong>Descrição:</strong> {{ $task->description ?? '-' }}</p>
            <p><strong>Concluída:</strong>
                @if($task->is_completed)
                    <span class="badge bg-success">Sim</span>
                @else
                    <span class="badge bg-warning">Não</span>
                @endif
            </p>
            <p><strong>Sentimento:</strong> {{ $task->sentiment ?? '-' }}</p>
            <p><strong>Data de Vencimento:</strong> {{ $task->due_date ? $task->due_date->format('d/m/Y') : '-' }}</p>
            <p><strong>Hora:</strong> {{ $task->time ? $task->time->format('H:i') : '-' }}</p>
            <p><strong>Criada em:</strong> {{ $task->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Atualizada em:</strong> {{ $task->updated_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-warning mt-3">Editar</a>
    <a href="{{ route('tasks.index') }}" class="btn btn-secondary mt-3">Voltar</a>
@endsection
