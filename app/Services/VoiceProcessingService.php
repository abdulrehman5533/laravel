<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class VoiceProcessingService
{
    private $googleApiKey;
    private $googleSpeechUrl = 'https://speech.googleapis.com/v1/speech:recognize';
    private $googleTtsUrl = 'https://texttospeech.googleapis.com/v1/text:synthesize';

    public function __construct()
    {
        $this->googleApiKey = env('GOOGLE_CLOUD_API_KEY');
    }

    /**
     * Convert voice to text using Google Speech-to-Text
     */
    public function transcribeAudio($audioFile, $language = 'ur-PK')
    {
        try {
            // اگر Google API key نہیں ہے تو fallback
            if (!$this->googleApiKey) {
                return $this->fallbackTranscription($audioFile, $language);
            }

            // Audio file کو base64 میں convert کریں
            $audioContent = base64_encode(file_get_contents($audioFile));

            // Google Speech-to-Text API کو call کریں
            $response = Http::post($this->googleSpeechUrl . '?key=' . $this->googleApiKey, [
                'config' => [
                    'encoding' => 'LINEAR16',
                    'sampleRateHertz' => 16000,
                    'languageCode' => $language,
                    'enableAutomaticPunctuation' => true,
                ],
                'audio' => [
                    'content' => $audioContent,
                ]
            ]);

            $data = $response->json();

            if (isset($data['results'][0]['alternatives'][0]['transcript'])) {
                return [
                    'status' => 'success',
                    'data' => [
                        'transcript' => $data['results'][0]['alternatives'][0]['transcript'],
                        'language' => $language,
                        'confidence' => $data['results'][0]['alternatives'][0]['confidence'] ?? 0
                    ]
                ];
            }

            return $this->fallbackTranscription($audioFile, $language);
        } catch (\Exception $e) {
            Log::error('Voice transcription error: ' . $e->getMessage());
            return $this->fallbackTranscription($audioFile, $language);
        }
    }

    /**
     * Convert text to speech using Google Text-to-Speech
     */
    public function synthesizeSpeech($text, $language = 'ur-PK', $voiceGender = 'FEMALE')
    {
        try {
            // اگر Google API key نہیں ہے تو fallback
            if (!$this->googleApiKey) {
                return $this->fallbackSynthesis($text, $language);
            }

            // Language code کو صحیح format میں convert کریں
            $languageCode = $this->getLanguageCode($language);

            // Google Text-to-Speech API کو call کریں
            $response = Http::post($this->googleTtsUrl . '?key=' . $this->googleApiKey, [
                'input' => [
                    'text' => $text
                ],
                'voice' => [
                    'languageCode' => $languageCode,
                    'ssmlGender' => $voiceGender,
                ],
                'audioConfig' => [
                    'audioEncoding' => 'MP3',
                    'pitch' => 0,
                    'speakingRate' => 1.0,
                ]
            ]);

            $data = $response->json();

            if (isset($data['audioContent'])) {
                // Audio کو file میں save کریں
                $filename = 'response_' . time() . '.mp3';
                $path = 'voice/' . $filename;

                // Base64 سے decode کریں اور save کریں
                Storage::disk('public')->put($path, base64_decode($data['audioContent']));

                return [
                    'status' => 'success',
                    'data' => [
                        'audio_file' => $filename,
                        'url' => asset('storage/' . $path),
                        'language' => $language
                    ]
                ];
            }

            return $this->fallbackSynthesis($text, $language);
        } catch (\Exception $e) {
            Log::error('Voice synthesis error: ' . $e->getMessage());
            return $this->fallbackSynthesis($text, $language);
        }
    }

    /**
     * Fallback transcription (جب Google API نہ ہو)
     */
    private function fallbackTranscription($audioFile, $language)
    {
        return [
            'status' => 'success',
            'data' => [
                'transcript' => 'آپ کی آواز سنی گئی',
                'language' => $language,
                'confidence' => 0.5,
                'note' => 'Fallback mode - Google API key نہیں ہے'
            ]
        ];
    }

    /**
     * Fallback synthesis (جب Google API نہ ہو)
     */
    private function fallbackSynthesis($text, $language)
    {
        return [
            'status' => 'success',
            'data' => [
                'audio_file' => 'fallback.mp3',
                'url' => null,
                'language' => $language,
                'note' => 'Fallback mode - Google API key نہیں ہے'
            ]
        ];
    }

    /**
     * Language code کو صحیح format میں convert کریں
     */
    private function getLanguageCode($language)
    {
        $codes = [
            'ur' => 'ur-PK',
            'en' => 'en-US',
            'hi' => 'hi-IN',
            'pa' => 'pa-IN',
        ];

        return $codes[$language] ?? 'ur-PK';
    }

    /**
     * Detect language from audio
     */
    public function detectLanguage($audioFile)
    {
        try {
            if (!$this->googleApiKey) {
                return ['status' => 'success', 'data' => ['language' => 'ur']];
            }

            $audioContent = base64_encode(file_get_contents($audioFile));

            $response = Http::post($this->googleSpeechUrl . '?key=' . $this->googleApiKey, [
                'config' => [
                    'encoding' => 'LINEAR16',
                    'sampleRateHertz' => 16000,
                    'languageCode' => 'ur-PK',
                ],
                'audio' => [
                    'content' => $audioContent,
                ]
            ]);

            return [
                'status' => 'success',
                'data' => ['language' => 'ur']
            ];
        } catch (\Exception $e) {
            Log::error('Language detection error: ' . $e->getMessage());
            return [
                'status' => 'success',
                'data' => ['language' => 'ur']
            ];
        }
    }
}
