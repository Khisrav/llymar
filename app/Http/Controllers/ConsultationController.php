<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ConsultationController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'service' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:1000',
            'source' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Проверьте правильность заполнения формы',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        try {
            // Send to Telegram
            $telegramSent = $this->sendToTelegram($data);

            if (!$telegramSent) {
                Log::warning('Failed to send consultation request to Telegram', $data);
            }

            // You could also save to database here if needed
            // ConsultationRequest::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing consultation request', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при отправке заявки. Попробуйте еще раз.'
            ], 500);
        }
    }

    private function sendToTelegram(array $data)
    {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (!$botToken || !$chatId) {
            Log::warning('Telegram bot token or chat ID not configured');
            return false;
        }

        // Format the message
        $message = $this->formatTelegramMessage($data);

        try {
            $body = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true
            ];
            
            Log::info($body);
            
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", $body);

            if ($response->successful()) {
                Log::info('Consultation request sent to Telegram successfully');
                return true;
            } else {
                Log::error('Failed to send message to Telegram', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception while sending to Telegram', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function formatTelegramMessage(array $data)
    {
        $serviceNames = [
            'balcony' => 'Безрамное остекление балконов',
            'panoramic' => 'Панорамное остекление',
            'terrace' => 'Остекление террас',
            'facade' => 'Фасадное остекление',
            'other' => 'Другое'
        ];

        $serviceName = $serviceNames[$data['service']] ?? $data['service'] ?? 'Не указано';

        $message = "🔔 <b>Новая заявка на консультацию</b>\n\n";
        $message .= "👤 <b>Имя:</b> " . htmlspecialchars($data['name']) . "\n";
        $message .= "📞 <b>Телефон:</b> " . htmlspecialchars($data['phone']) . "\n";
        
        if (!empty($data['email'])) {
            $message .= "📧 <b>Email:</b> " . htmlspecialchars($data['email']) . "\n";
        }
        
        $message .= "🏗️ <b>Услуга:</b> " . htmlspecialchars($serviceName) . "\n";
        
        if (!empty($data['message'])) {
            $message .= "💬 <b>Сообщение:</b>\n" . htmlspecialchars($data['message']) . "\n";
        }
        
        // $message .= "\n📍 <b>Источник:</b> " . htmlspecialchars($data['source'] ?? 'Лендинг') . "\n";
        // $message .= "🕐 <b>Время:</b> " . now()->format('d.m.Y H:i') . "\n";

        return $message;
    }
} 