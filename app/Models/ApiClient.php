<?php

namespace App\Models;

use RuntimeException;

class ApiClient
{
    public function __construct(
        private string $baseUrl,
        private ?string $token = null,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function withToken(?string $token): self
    {
        return new self($this->baseUrl, $token);
    }

    public function token(): ?string
    {
        return $this->token;
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function post(string $path, array $body = []): array
    {
        return $this->request('POST', $path, $body);
    }

    /**
     * @param  array<string, scalar|null>  $query
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = []): array
    {
        if ($query !== []) {
            $path .= (str_contains($path, '?') ? '&' : '?') . http_build_query($query);
        }

        return $this->request('GET', $path);
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, ?array $body = null): array
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        if ($this->token !== null && $this->token !== '') {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        $payload = $body === null ? null : json_encode($body, JSON_THROW_ON_ERROR);

        if (function_exists('curl_init')) {
            return $this->requestWithCurl($method, $url, $headers, $payload);
        }

        return $this->requestWithStreams($method, $url, $headers, $payload);
    }

    /**
     * @param  list<string>  $headers
     * @return array<string, mixed>
     */
    private function requestWithCurl(string $method, string $url, array $headers, ?string $payload): array
    {
        $curl = curl_init($url);
        if ($curl === false) {
            throw new RuntimeException('Unable to initialize HTTP client.');
        }

        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($payload !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        }

        $raw = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($raw === false) {
            throw new RuntimeException('API request failed: ' . $error);
        }

        return $this->decodeResponse($status, (string) $raw, $url);
    }

    /**
     * @param  list<string>  $headers
     * @return array<string, mixed>
     */
    private function requestWithStreams(string $method, string $url, array $headers, ?string $payload): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $payload ?? '',
                'ignore_errors' => true,
                'timeout' => 30,
            ],
        ]);

        $raw = file_get_contents($url, false, $context);
        if ($raw === false) {
            throw new RuntimeException('API request failed for ' . $url);
        }

        $status = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches) === 1) {
            $status = (int) $matches[1];
        }

        return $this->decodeResponse($status, $raw, $url);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(int $status, string $raw, string $url): array
    {
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('API returned invalid JSON from ' . $url);
        }

        if ($status < 200 || $status >= 300) {
            $message = is_string($decoded['message'] ?? null)
                ? $decoded['message']
                : 'API request failed with status ' . $status;

            throw new RuntimeException($message . ' (' . $url . ')');
        }

        return $decoded;
    }
}
