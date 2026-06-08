<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class MLService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('ml.base_url'), '/');
        $this->timeout = (int) config('ml.timeout', 30);
    }

    /**
    জম
     * Send a file to a specific ML endpoint.
     *
     * @param  string  $endpoint
     * @param  \Illuminate\Http\UploadedFile  $file
     */
    protected function postFile(string $endpoint, UploadedFile $file): array
    {
        $url = $this->baseUrl.$endpoint;

        try {
            $response = Http::timeout(120)
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post($url);

            if ($response->failed()) {
                return [
                    'error' => 'ML service returned HTTP ' . $response->status(),
                ];
            }

            $data = $response->json();

            if ($data === null) {
                // Body may contain binary/non-UTF8 - just log the content type
                return [
                    'error' => 'ML service returned an invalid response (not JSON). Content-Type: '
                        . ($response->header('Content-Type') ?: 'unknown'),
                ];
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return ['error' => 'Could not connect to ML service. Is it running?'];
        } catch (\Exception $e) {
            return ['error' => 'ML service communication error: ' . preg_replace('/[^\x20-\x7E]/', '?', $e->getMessage())];
        }
    }

    public function detectPlate(UploadedFile $file): array
    {
        return $this->postFile('/plate/detect', $file);
    }

    public function detectCracks(UploadedFile $file): array
    {
        return $this->postFile('/cracks/detect', $file);
    }

    public function detectDashboard(UploadedFile $file): array
    {
        return $this->postFile('/car_dashboard/detect', $file);
    }

    public function detectAccident(UploadedFile $file): array
    {
        return $this->postFile('/accident/detect', $file);
    }

    public function detectFireSmoke(UploadedFile $file): array
    {
        return $this->postFile('/fire_smoke/detect', $file);
    }

    public function detectTraffic(UploadedFile $file): array
    {
        return $this->postFile('/traffic/detect', $file);
    }

    public function detectVehicles(UploadedFile $file): array
    {
        return $this->postFile('/vehicles/detect', $file);
    }

    public function detectCarDamage(UploadedFile $file): array
    {
        return $this->postFile('/car_damage/detect', $file);
    }

    /**
     * Stream detection results for a video.
     * Returns a generator that yields chunks from the ML service.
     */
    public function streamCracks(UploadedFile $file)
    {
        $url = $this->baseUrl . '/cracks/detect/stream';

        // We use Guzzle directly to get a streaming response
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName()
                ]
            ],
            'stream' => true,
            'timeout' => 300,
        ]);

        return $response->getBody();
    }

    /**
     * Stream detection results for a plate video.
     */
    public function streamPlate(UploadedFile $file)
    {
        $url = $this->baseUrl . '/plate/detect/stream';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName()
                ]
            ],
            'stream' => true,
            'timeout' => 300,
        ]);
        return $response->getBody();
    }

    /**
     * Stream detection results for a dashboard video.
     */
    public function streamDashboard(UploadedFile $file)
    {
        $url = $this->baseUrl . '/car_dashboard/detect/stream';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName()
                ]
            ],
            'stream' => true,
            'timeout' => 300,
        ]);
        return $response->getBody();
    }

    /**
     * Stream detection results for an accident video.
     */
    public function streamAccident(UploadedFile $file)
    {
        $url = $this->baseUrl . '/accident/detect/stream';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName()
                ]
            ],
            'stream' => true,
            'timeout' => 300,
        ]);
        return $response->getBody();
    }

    /**
     * Stream detection results for a fire/smoke video.
     */
    public function streamFireSmoke(UploadedFile $file)
    {
        $url = $this->baseUrl . '/fire_smoke/detect/stream';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName()
                ]
            ],
            'stream' => true,
            'timeout' => 300,
        ]);
        return $response->getBody();
    }

    /**
     * Stream detection results for a traffic video.
     */
    public function streamTraffic(UploadedFile $file)
    {
        $url = $this->baseUrl . '/traffic/detect/stream';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName()
                ]
            ],
            'stream' => true,
            'timeout' => 300,
        ]);
        return $response->getBody();
    }

    /**
     * Stream detection results for a vehicles video.
     */
    public function streamVehicles(UploadedFile $file)
    {
        $url = $this->baseUrl . '/vehicles/detect/stream';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName()
                ]
            ],
            'stream' => true,
            'timeout' => 300,
        ]);
        return $response->getBody();
    }

    /**
     * Stream detection results for a car damage video.
     */
    public function streamCarDamage(UploadedFile $file)
    {
        $url = $this->baseUrl . '/car_damage/detect/stream';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName()
                ]
            ],
            'stream' => true,
            'timeout' => 300,
        ]);
        return $response->getBody();
    }
}

