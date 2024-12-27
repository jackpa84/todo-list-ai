@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h1>Tarefas</h1>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">Adicionar Tarefa</a>
    </div>

    <form action="{{ route('tasks.prioritize') }}" method="POST" class="mb-4">
        @csrf
        <button type="submit" class="btn btn-secondary">Priorizar Tarefas</button>
    </form>

    @if($tasks->count())
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Título</th>
                <th>Descrição</th>
                <th>Concluída</th>
                <th>Sentimento</th>
                <th>Data de Vencimento</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            @foreach($tasks as $task)
                <tr>
                    <td>{{ $task->title }}</td>
                    <td>{{ Str::limit($task->description, 50) }}</td>
                    <td>
                        @if($task->is_completed)
                            <span class="badge bg-success">Sim</span>
                        @else
                            <span class="badge bg-warning">Não</span>
                        @endif
                    </td>
                    <td>{{ $task->sentiment }}</td>
                    <td>{{ $task->due_date ? $task->due_date->format('d/m/Y') : '-' }}</td>
                    <td>
                        <a href="{{ route('tasks.show', $task) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta tarefa?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Excluir</button>
                        </form>
                        @if(!$task->is_completed)
                            <form action="{{ route('tasks.complete', $task) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-success btn-sm">Concluir</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p>Você ainda não tem nenhuma tarefa.</p>
    @endif
@endsection
