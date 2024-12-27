<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', 'AIzaSyAMCAr6e7GctkNxQLCe3QcBVRSZvT1TaD8');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:';
    }

    /**
     * Generate task details based on natural language input.
     */
    public function generateTaskDetails(string $naturalInput): array
    {
        $date = date('Y-m-d');
        $prompt = "Extrair os detalhes da tarefa a partir da seguinte descrição: \"$naturalInput\".\n" .
            "Formate a saída em JSON com os campos 'title', 'description', 'due_date' e 'time'.\n" .
            "Retorne o campo due_date no formato yyyy-MM-dd.\n" .
            "A data de hoje é $date para ajudar a definir a data de vencimento.";

        $response = $this->generateContent($prompt);

        $array = json_decode($response, true);

        $text = $array['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $text = trim($text, "\" \n");
        $text = preg_replace('/```json\s*([\s\S]*?)\s*```/i', '$1', $text);

        return json_decode($text, true);
    }

    /**
     * Generate content from a prompt.
     */
    public function generateContent(string $inputText): string
    {
        $endpoint = $this->baseUrl . 'generateContent?key=' . $this->apiKey;

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $inputText]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 1,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 8192,
                'responseMimeType' => 'text/plain'
            ]
        ];

        return $this->postRequest($endpoint, $payload);
    }

    /**
     * Analyze sentiment of a given text.
     */
    public function analyzeSentiment(string $text): array
    {
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=' . $this->apiKey;

        $prompt = "Analyze the sentiment of the following text and classify it as POSITIVE, NEGATIVE, or NEUTRAL:\n\"$text\".";
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 1,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 8192,
                'responseMimeType' => 'text/plain'
            ]
        ];

        $response = $this->postRequest($endpoint, $payload);

        // Parse the response to extract sentiment
        return $this->parseSentimentResponse($response);
    }

    private function parseSentimentResponse(string $response): array
    {
        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Erro ao decodificar resposta da API de análise de sentimento', [
                'response' => $response
            ]);

            return [
                'error' => true,
                'message' => 'Falha ao decodificar JSON',
                'response' => $response
            ];
        }

        // Extraindo o texto de saída
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? 'Indeterminado';

        // Usando regex para identificar o sentimento principal
        if (preg_match('/is \\*\\*(POSITIVE|NEGATIVE|NEUTRAL)\\*\\*/i', $text, $matches)) {
            $sentiment = strtoupper($matches[1]);
        } else {
            $sentiment = 'Indeterminado';
        }
        // Retornando o sentimento extraído
        return [
            'sentiment' => $sentiment,
        ];
    }
    /**
     * Helper method to send POST requests.
     */
    private function postRequest(string $url, array $payload): string
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                return $response->body();
            }

            Log::error('Erro na API Gemini', [
                'status' => $response->status(),
                'message' => $response->body()
            ]);

            return json_encode([
                'error' => true,
                'status' => $response->status(),
                'message' => $response->body()
            ]);
        } catch (\Exception $e) {
            Log::error('Exceção ao chamar a API Gemini', [
                'message' => $e->getMessage()
            ]);

            return json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Helper method to parse JSON responses.
     */
    private function parseJsonResponse(string $response): array
    {
        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Erro ao decodificar JSON', [
                'response' => $response
            ]);

            return [
                'error' => true,
                'message' => 'Falha ao decodificar JSON',
                'response' => $response
            ];
        }

        return $decoded;
    }
}
