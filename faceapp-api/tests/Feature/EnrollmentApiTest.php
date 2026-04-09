<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EnrollmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_enrolls_a_face_and_marks_it_verified(): void
    {
        Storage::fake('public');

        config()->set('gateway.device_key', 'DEVICE1234567890');
        config()->set('gateway.secret', 'secret123');
        config()->set('gateway.base_url', 'http://gateway.local/api');
        config()->set('gateway.upload.public_base_url', 'http://127.0.0.1:8000/storage');

        Http::fake([
            'http://gateway.local/api/person/find' => Http::sequence()
                ->push([
                    'code' => '200',
                    'msg' => 'ok',
                    'data' => null,
                ])
                ->push([
                    'code' => '200',
                    'msg' => 'ok',
                    'data' => [
                        'sn' => 'EMP4829',
                        'name' => 'Alexandra Chen',
                    ],
                ]),
            'http://gateway.local/api/person/merge' => Http::response([
                'code' => '000',
                'msg' => 'Request is successful',
                'success' => true,
                'data' => true,
            ]),
            'http://gateway.local/api/face/merge' => Http::response([
                'code' => '000',
                'msg' => 'Request is successful',
                'success' => true,
                'data' => true,
            ]),
            'http://gateway.local/api/face/find' => Http::response([
                'code' => '200',
                'msg' => 'ok',
                'data' => [
                    'personSn' => 'EMP4829',
                    'imgBase64' => base64_encode('verified'),
                ],
            ]),
        ]);

        $response = $this->postJson('/api/enrollments', [
            'employee_id' => 'EMP4829',
            'name' => 'Alexandra Chen',
            'photo_data_url' => 'data:image/jpeg;base64,'.base64_encode('fake-image'),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('enrollment.status', 'verified');

        Http::assertSentCount(5);
    }
}
