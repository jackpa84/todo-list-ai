@extends('layouts.app')

@section('content')
    <h1>Adicionar Tarefa</h1>

    <form action="{{ route('tasks.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="task_description_natural" class="form-label">Descrição Natural da Tarefa</label>
            <textarea class="form-control" id="task_description_natural" name="task_description_natural" rows="4"></textarea>
            <small class="form-text text-muted">Descreva a tarefa em linguagem natural para que a IA gere os detalhes. Ao sair do campo, os detalhes serão carregados.</small>
        </div>

        <div class="mb-3">
            <label for="title" class="form-label">Título<span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Descrição</label>
            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
        </div>

        <div class="mb-3">
            <label for="due_date" class="form-label">Data de Vencimento</label>
            <input type="date" class="form-control" id="due_date" name="due_date">
        </div>

        <div class="mb-3">
            <label for="time" class="form-label">Hora</label>
            <input type="time" class="form-control" id="time" name="time">
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#task_description_natural').on('blur', function() {
                let naturalInput = $(this).val().trim();

                if(naturalInput === '') {
                    return;
                }

                $.ajax({
                    url: "{{ route('tasks.generate.details') }}",
                    type: "POST",
                    data: {
                        task_description_natural: naturalInput,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {

                        if(response.title) {
                            $('#title').val(response.title);
                        }
                        if(response.description) {
                            $('#description').val(response.description);
                        }
                        if(response.due_date) {
                            $('#due_date').val(response.due_date);
                        }
                        if(response.time) {
                            $('#time').val(response.time);
                        }
                    },
                    error: function(xhr) {
                        console.error("Erro ao gerar detalhes da tarefa:", xhr.responseText);
                    }
                });
            });
        });
    </script>


@endsection
