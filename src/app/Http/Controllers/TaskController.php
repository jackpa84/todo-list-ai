<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function index()
    {
        $tasks = Auth::user()->tasks()->latest()->get();
        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        return view('tasks.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'task_description_natural' => 'nullable',
        ]);

        $data = $this->processTaskDetails($validatedData);

        Auth::user()->tasks()->create($data);

        return redirect()->route('tasks.index')->with('success', 'Tarefa criada com sucesso.');
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        return view('tasks.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
        ]);

        $task->update($validatedData);

        return redirect()->route('tasks.index')->with('success', 'Tarefa atualizada com sucesso.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Tarefa deletada com sucesso.');
    }

    public function complete(Task $task)
    {
        $this->authorize('update', $task);
        $task->update(['is_completed' => true]);

        return redirect()->route('tasks.index')->with('success', 'Tarefa marcada como concluída.');
    }

    public function prioritizeTasks()
    {
        $tasks = Auth::user()->tasks()->where('is_completed', false)->get();

        if ($tasks->isEmpty()) {
            return redirect()->route('tasks.index')->with('info', 'Nenhuma tarefa pendente para priorizar.');
        }

        $taskTitles = $tasks->pluck('title')->implode("\n");

        try {
            $response = $this->geminiService->generatePrioritizedTasks($taskTitles);

            if (isset($response['prioritized_tasks'])) {
                return view('tasks.prioritized', ['prioritizedList' => $response['prioritized_tasks']]);
            }

            Log::error('Erro ao priorizar tarefas na API Gemini.', ['response' => $response]);
            return redirect()->route('tasks.index')->with('error', 'Erro ao priorizar tarefas.');
        } catch (\Exception $e) {
            Log::error('Exceção ao priorizar tarefas: ' . $e->getMessage());
            return redirect()->route('tasks.index')->with('error', 'Erro ao priorizar tarefas.');
        }
    }

    public function chatbot(Request $request)
    {
        $userMessage = $request->input('message');

        try {
            $response = $this->geminiService->chatbot($userMessage);

            return response()->json(['reply' => $response['reply'] ?? 'Desculpe, não entendi sua solicitação.']);
        } catch (\Exception $e) {
            Log::error('Erro no chatbot da API Gemini: ' . $e->getMessage());
            return response()->json(['reply' => 'Desculpe, ocorreu um erro ao processar sua solicitação.'], 500);
        }
    }

    public function generateTaskDetails(Request $request)
    {
        $validatedData = $request->validate([
            'task_description_natural' => 'required|string',
        ]);

        $details = $this->geminiService->generateTaskDetails($validatedData['task_description_natural']);

        return response()->json($details);
    }

    private function processTaskDetails(array $validatedData): array
    {
        $data = [
            'title' => $validatedData['title'] ?? '',
            'description' => $validatedData['description'] ?? '',
        ];

        if (!empty($validatedData['task_description_natural'])) {
            try {
                $taskDetails = $this->geminiService->generateTaskDetails($validatedData['task_description_natural']);

                $data['title'] = $taskDetails['title'] ?? $data['title'];
                $data['description'] = $taskDetails['description'] ?? $data['description'];
                $data['due_date'] = $taskDetails['due_date'] ?? null;
                $data['time'] = $taskDetails['time'] ?? null;
            } catch (\Exception $e) {
                Log::error('Erro ao extrair detalhes da tarefa: ' . $e->getMessage());
            }
        }

        if (!empty($data['description'])) {
            try {
                $sentimentResult = $this->geminiService->analyzeSentiment($data['description']);

                $data['sentiment'] = $sentimentResult['sentiment'] ?? null;
            } catch (\Exception $e) {
                Log::error('Erro ao processar análise de sentimento: ' . $e->getMessage());
                $data['sentiment'] = 'Erro ao processar';
            }
        }
        return $data;
    }
}
