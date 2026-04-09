<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GatewaySdkClient
{
    public function deviceStatus(): array
    {
        return $this->post('/device/get');
    }

    public function setServerConfig(array $config): array
    {
        return $this->post('/device/setSevConfig', $config);
    }

    public function findPerson(string $employeeId): array
    {
        return $this->post('/person/find', [
            'type' => 1,
            'key' => $employeeId,
        ]);
    }

    public function createPerson(array $person): array
    {
        return $this->post('/person/create', $this->personPayload($person));
    }

    public function updatePerson(array $person): array
    {
        return $this->post('/person/update', $this->personPayload($person));
    }

    public function upsertPerson(array $person): array
    {
        $existing = $this->findPerson($person['employee_id']);

        if ($this->personExists($existing)) {
            return $this->updatePerson($person);
        }

        return $this->createPerson($person);
    }

    public function mergeFace(string $employeeId, string $imageUrl, string $imageBase64, int $photoQuality = 1): array
    {
        return $this->post('/face/merge', [
            'personSn' => $employeeId,
            'imgUrl' => $imageUrl,
            'imgBase64' => $imageBase64,
            'quality' => $photoQuality,
        ]);
    }

    public function findFace(string $employeeId): array
    {
        return $this->post('/face/find', [
            'personSn' => $employeeId,
        ]);
    }

    public function personExists(?array $response): bool
    {
        $data = $response['data'] ?? null;

        if (is_array($data)) {
            return filled($data['sn'] ?? null)
                || filled($data['personSn'] ?? null)
                || filled($data['name'] ?? null);
        }

        return filled($data);
    }

    public function faceExists(?array $response): bool
    {
        $data = $response['data'] ?? null;

        if (is_array($data)) {
            return filled($data['imgBase64'] ?? null)
                || filled($data['featureData'] ?? null);
        }

        return false;
    }

    protected function personPayload(array $person): array
    {
        return [
            'type' => $person['person_type'],
            'sn' => $person['employee_id'],
            'name' => $person['name'],
            'acGroupNumber' => $person['ac_group_number'],
            'verifyStyle' => $person['verify_style'],
        ];
    }

    protected function post(string $path, array $payload = []): array
    {
        try {
            $response = $this->http()
                ->post($this->endpoint($path), $this->withDeviceCredentials($payload))
                ->throw();
        } catch (RequestException $exception) {
            $message = $exception->response?->json('msg')
                ?? $exception->response?->body()
                ?? $exception->getMessage();

            throw new RuntimeException('Gateway request failed: '.$message, previous: $exception);
        }

        $decoded = $response->json();

        return is_array($decoded)
            ? $decoded
            : ['raw' => $response->body()];
    }

    protected function http(): PendingRequest
    {
        $baseUrl = config('gateway.base_url');

        if (! is_string($baseUrl) || $baseUrl === '') {
            throw new RuntimeException('GATEWAY_BASE_URL is not configured.');
        }

        return Http::asForm()->timeout((int) config('gateway.timeout_seconds', 10));
    }

    protected function endpoint(string $path): string
    {
        return rtrim((string) config('gateway.base_url'), '/').'/'.ltrim($path, '/');
    }

    protected function withDeviceCredentials(array $payload): array
    {
        $deviceKey = config('gateway.device_key');
        $secret = config('gateway.secret');

        if (! is_string($deviceKey) || $deviceKey === '') {
            throw new RuntimeException('GATEWAY_DEVICE_KEY is not configured.');
        }

        if (! is_string($secret) || $secret === '') {
            throw new RuntimeException('GATEWAY_SECRET is not configured.');
        }

        return array_filter([
            'deviceKey' => $deviceKey,
            'secret' => $secret,
            ...$payload,
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
