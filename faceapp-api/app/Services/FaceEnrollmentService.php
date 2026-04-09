<?php

namespace App\Services;

use App\Models\Enrollment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class FaceEnrollmentService
{
    public function __construct(
        private readonly GatewaySdkClient $gateway,
    ) {}

    public function enroll(array $payload): Enrollment
    {
        [$imageBase64, $extension] = $this->extractImagePayload($payload['photo_data_url']);

        $employeeId = trim((string) $payload['employee_id']);
        $name = trim((string) $payload['name']);

        $enrollment = Enrollment::create([
            'employee_id' => $employeeId,
            'name' => $name,
            'device_key' => config('gateway.device_key'),
            'status' => 'pending',
        ]);

        try {
            $photoPath = $this->storePhoto($employeeId, $imageBase64, $extension);
            $photoPublicUrl = $this->storageUrl($photoPath, (string) config('gateway.upload.public_base_url', ''));
            $gatewayImageUrl = $this->storageUrl($photoPath, (string) config('gateway.upload.gateway_base_url', ''));

            $enrollment->forceFill([
                'photo_path' => $photoPath,
                'photo_public_url' => $photoPublicUrl,
            ])->save();

            $personPayload = [
                'employee_id' => $employeeId,
                'name' => $name,
                'person_type' => (int) ($payload['person_type'] ?? config('gateway.defaults.person_type', 1)),
                'verify_style' => (int) ($payload['verify_style'] ?? config('gateway.defaults.verify_style', 1)),
                'ac_group_number' => (int) ($payload['ac_group_number'] ?? config('gateway.defaults.ac_group_number', 0)),
            ];

            $personResponse = $this->gateway->upsertPerson($personPayload);

            $enrollment->forceFill([
                'status' => 'person_synced',
                'gateway_person_status' => 'synced',
                'gateway_person_response' => $personResponse,
            ])->save();

            $faceResponse = $this->gateway->mergeFace(
                $employeeId,
                $gatewayImageUrl,
                $imageBase64,
                (int) ($payload['photo_quality'] ?? config('gateway.defaults.photo_quality', 1)),
            );

            $enrollment->forceFill([
                'status' => 'face_uploaded',
                'gateway_face_status' => 'uploaded',
                'gateway_face_response' => $faceResponse,
            ])->save();

            $verificationResponse = $this->gateway->findFace($employeeId);

            if (! $this->gateway->faceExists($verificationResponse)) {
                throw new RuntimeException('Gateway did not confirm the uploaded face.');
            }

            $enrollment->forceFill([
                'status' => 'verified',
                'gateway_face_status' => 'verified',
                'verification_response' => $verificationResponse,
                'enrolled_at' => now(),
                'error_message' => null,
            ])->save();

            return $enrollment->fresh();
        } catch (Throwable $exception) {
            $enrollment->forceFill([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ])->save();

            report($exception);

            throw $exception;
        }
    }

    protected function extractImagePayload(string $dataUrl): array
    {
        if (! preg_match('/^data:image\/(?P<extension>jpeg|jpg|png);base64,(?P<data>.+)$/', $dataUrl, $matches)) {
            throw new RuntimeException('Unsupported image payload received from FaceApp.');
        }

        $imageBase64 = $matches['data'];
        $binary = base64_decode($imageBase64, true);

        if ($binary === false) {
            throw new RuntimeException('Failed to decode the captured image.');
        }

        $extension = $matches['extension'] === 'jpeg' ? 'jpg' : $matches['extension'];

        return [$imageBase64, $extension];
    }

    protected function storePhoto(string $employeeId, string $imageBase64, string $extension): string
    {
        $binary = base64_decode($imageBase64, true);

        if ($binary === false) {
            throw new RuntimeException('Failed to decode the image before upload.');
        }

        $directory = trim((string) config('gateway.upload.directory', 'face-uploads'), '/');
        $fileName = Str::slug($employeeId).'-'.Str::lower(Str::random(8)).'.'.$extension;
        $path = $directory.'/'.$fileName;

        Storage::disk((string) config('gateway.upload.disk', 'public'))->put($path, $binary);

        return $path;
    }

    protected function storageUrl(string $path, string $baseUrl = ''): string
    {
        if ($baseUrl !== '') {
            return $baseUrl.'/'.ltrim($path, '/');
        }

        return Storage::disk((string) config('gateway.upload.disk', 'public'))->url($path);
    }
}
