<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Company;
use App\Models\Item;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;

class TochkaBankService
{
    private string $baseUrl;
    private ?string $clientSecret;
    private ?string $customerCode;
    private ?string $accountId;
    private string $apiVersion;

    public function __construct()
    {
        $this->baseUrl = config('services.tochka.base_url');
        $this->clientSecret = config('services.tochka.client_secret');
        // $this->accessToken = 'working_token';
        $this->customerCode = config('services.tochka.customer_code');
        $this->accountId = config('services.tochka.account_id');
        $this->apiVersion = config('services.tochka.api_version');
        
        Log::info('Tochka Bank credentials', [
            'baseUrl' => $this->baseUrl,
            'clientSecret' => $this->clientSecret,
            'customerCode' => $this->customerCode,
            'accountId' => $this->accountId,
            'apiVersion' => $this->apiVersion
        ]);
        
        if (!$this->clientSecret || !$this->customerCode || !$this->accountId) {
            throw new Exception('Tochka Bank credentials not configured. Please set TOCHKA_CLIENT_SECRET, TOCHKA_CUSTOMER_CODE, and TOCHKA_ACCOUNT_ID in your .env file.');
        }
    }

    /**
     * Create a bill in Tochka Bank system
     *
     * @param Order $order
     * @param array $secondSide Optional second side information
     * @return array
     * @throws Exception
     */
    public function createBill(Order $order, array $secondSide = []): array
    {
        try {
            $payload = $this->prepareBillPayload($order, $secondSide);
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->clientSecret}",
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/invoice/{$this->apiVersion}/bills", $payload);

            if (!$response->successful()) {
                Log::error('Tochka Bank API Error', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'order_id' => $order->id
                ]);
                
                throw new Exception('Failed to create bill: ' . $response->body());
            }

            $responseData = $response->json();
            
            // Update order with invoice ID
            if (isset($responseData['Data']['documentId'])) {
                $order->update([
                    'invoice_id' => $responseData['Data']['documentId'],
                    'invoice_status' => 'created'
                ]);
            }

            return $responseData;
            
        } catch (Exception $e) {
            Log::error('Error creating Tochka Bank bill', [
                'error' => $e->getMessage(),
                'order_id' => $order->id
            ]);
            
            throw $e;
        }
    }

    /**
     * Get bill payment status
     *
     * @param Order $order
     * @return array
     * @throws Exception
     */
    public function getBillPaymentStatus(Order $order): array
    {
        if (!$order->invoice_id) {
            throw new Exception('Order does not have an invoice ID');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->clientSecret}",
            ])->get("{$this->baseUrl}/invoice/{$this->apiVersion}/bills/{$this->customerCode}/{$order->invoice_id}/payment-status");

            if (!$response->successful()) {
                throw new Exception('Failed to get payment status: ' . $response->body());
            }

            $responseData = $response->json();
            
            // Update order invoice status if needed
            if (isset($responseData['Data']['paymentStatus'])) {
                $order->update(['invoice_status' => $responseData['Data']['paymentStatus']]);
            }

            return $responseData;
            
        } catch (Exception $e) {
            Log::error('Error getting Tochka Bank payment status', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'invoice_id' => $order->invoice_id
            ]);
            
            throw $e;
        }
    }

    /**
     * Get bill PDF file
     *
     * @param Order $order
     * @return string Binary PDF content
     * @throws Exception
     */
    public function getBillPDF(Order $order): string
    {
        if (!$order->invoice_id) {
            throw new Exception('Order does not have an invoice ID');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->clientSecret}",
            ])->get("{$this->baseUrl}/invoice/{$this->apiVersion}/bills/{$this->customerCode}/{$order->invoice_id}/file");

            if (!$response->successful()) {
                throw new Exception('Failed to get PDF file: ' . $response->body());
            }

            return $response->body();
            
        } catch (Exception $e) {
            Log::error('Error getting Tochka Bank PDF', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'invoice_id' => $order->invoice_id
            ]);
            
            throw $e;
        }
    }

    /**
     * Send bill to email
     *
     * @param Order $order
     * @param string $email
     * @return array
     * @throws Exception
     */
    public function sendBillToEmail(Order $order, string $email): array
    {
        if (!$order->invoice_id) {
            throw new Exception('Order does not have an invoice ID');
        }

        try {
            $payload = [
                'Data' => [
                    'email' => $email
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->clientSecret}",
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/invoice/{$this->apiVersion}/bills/{$this->customerCode}/{$order->invoice_id}/email", $payload);

            if (!$response->successful()) {
                throw new Exception('Failed to send email: ' . $response->body());
            }

            return $response->json();
            
        } catch (Exception $e) {
            Log::error('Error sending Tochka Bank email', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'invoice_id' => $order->invoice_id,
                'email' => $email
            ]);
            
            throw $e;
        }
    }

    /**
     * Delete a bill
     *
     * @param Order $order
     * @return bool
     * @throws Exception
     */
    public function deleteBill(Order $order): bool
    {
        if (!$order->invoice_id) {
            throw new Exception('Order does not have an invoice ID');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->clientSecret}",
            ])->delete("{$this->baseUrl}/invoice/{$this->apiVersion}/bills/{$this->customerCode}/{$order->invoice_id}");

            if (!$response->successful()) {
                throw new Exception('Failed to delete bill: ' . $response->body());
            }

            // Clear invoice data from order
            $order->update([
                'invoice_id' => null,
                'invoice_status' => null
            ]);

            return true;
            
        } catch (Exception $e) {
            Log::error('Error deleting Tochka Bank bill', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'invoice_id' => $order->invoice_id
            ]);
            
            throw $e;
        }
    }

    /**
     * Prepare bill payload for Tochka Bank API
     *
     * @param Order $order
     * @param array $secondSide
     * @return array
     */
    private function prepareBillPayload(Order $order, array $secondSide = []): array
    {
        $totalAmount = $order->total_price ?? 0;
        $totalNds = 0;

        $positions = [
            [
                'positionName' => 'Безрамная система остекления LLYMAR',
                'unitCode' => 'шт.',
                'ndsKind' => 'nds_0',
                'price' => number_format($totalAmount, 2, '.', ''),
                'quantity' => '1',
                'totalAmount' => number_format($totalAmount, 2, '.', ''),
                'totalNds' => number_format($totalNds, 2, '.', '')
            ]
        ];

        // Default second side information
        $defaultSecondSide = [
            'taxCode' => $order->user->inn ?? '',
            'type' => 'ip', //or company
            'secondSideName' => $order->customer_name ?? ''
        ];

        $finalSecondSide = array_merge($defaultSecondSide, $secondSide);

        $payload = [
            'Data' => [
                'customerCode' => $this->customerCode,
                'accountId' => $this->accountId,
                'Content' => [
                    'Invoice' => [
                        'number' => $order->order_number ?? (string) $order->id,
                        'basedOn' => 'Заказ №' . ($order->order_number ?? $order->id),
                        'comment' => $order->comment ?? 'Оплата заказа',
                        'paymentExpiryDate' => now()->addDays(3)->format('Y-m-d'),
                        'date' => $order->created_at->format('Y-m-d'),
                        'totalAmount' => number_format($totalAmount, 2, '.', ''),
                        'totalNds' => number_format($totalNds, 2, '.', ''),
                        'Positions' => $positions
                    ]
                ],
                'SecondSide' => $finalSecondSide
            ]
        ];
        
        //log payload
        Log::info('Tochka Bank payload', [
            'payload' => $payload
        ]);

        return $payload;
    }

    /**
     * Get the URL for viewing the bill PDF
     *
     * @param Order $order
     * @return string
     */
    public function getBillPDFUrl(Order $order): string
    {
        if (!$order->invoice_id) {
            throw new Exception('Order does not have an invoice ID');
        }

        return "{$this->baseUrl}/invoice/{$this->apiVersion}/bills/{$this->customerCode}/{$order->invoice_id}/file";
    }
    
    /**
     * Receive webhook from Tochka Bank
     *
     * @param Request $request
     * @return void
     */
    public function receiveWebhook(Request $request): void
    {
        Log::info('Tochka Bank webhook received', ['request' => $request->all()]);
        
        if ($this->validateWebhookJwtToken($request)) {
            $entityBody = $request->getContent();
            
            try {
                $decoded = $this->decodeWebhookPayload($entityBody);
                
                // Process the webhook data
                Log::info('Webhook payload decoded successfully', ['decoded' => $decoded]);
                
                // Handle the webhook based on the event type
                $this->processWebhookEvent($decoded);
                
            } catch (\Exception $e) {
                Log::error('Failed to process webhook', ['error' => $e->getMessage()]);
            }
        } else {
            Log::warning('Invalid webhook signature');
        }
    }
    
    /**
     * Validate webhook JWT token
     *
     * @param Request $request
     * @return bool
     */
    private function validateWebhookJwtToken(Request $request): bool
    {
        $entityBody = $request->getContent();
        
        $json_public_key = '{"kty":"RSA","e":"AQAB","n":"rwm77av7GIttq-JF1itEgLCGEZW_zz16RlUQVYlLbJtyRSu61fCec_rroP6PxjXU2uLzUOaGaLgAPeUZAJrGuVp9nryKgbZceHckdHDYgJd9TsdJ1MYUsXaOb9joN9vmsCscBx1lwSlFQyNQsHUsrjuDk-opf6RCuazRQ9gkoDCX70HV8WBMFoVm-YWQKJHZEaIQxg_DU4gMFyKRkDGKsYKA0POL-UgWA1qkg6nHY5BOMKaqxbc5ky87muWB5nNk4mfmsckyFv9j1gBiXLKekA_y4UwG2o1pbOLpJS3bP_c95rm4M9ZBmGXqfOQhbjz8z-s9C11i-jmOQ2ByohS-ST3E5sqBzIsxxrxyQDTw--bZNhzpbciyYW4GfkkqyeYoOPd_84jPTBDKQXssvj8ZOj2XboS77tvEO1n1WlwUzh8HPCJod5_fEgSXuozpJtOggXBv0C2ps7yXlDZf-7Jar0UYc_NJEHJF-xShlqd6Q3sVL02PhSCM-ibn9DN9BKmD"}';
        $jwks = json_decode($json_public_key, true, 512, JSON_THROW_ON_ERROR);
        
        try {
            JWT::decode($entityBody, JWK::parseKey($jwks, "RS256"));
            return true;
        } catch (\UnexpectedValueException $e) {
            // Неверная подпись, вебхук не от Точки или с ним что-то не так
            Log::error('Invalid webhook signature', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Decode webhook payload
     *
     * @param string $entityBody
     * @return array
     */
    private function decodeWebhookPayload(string $entityBody): array
    {
        $json_public_key = '{"kty":"RSA","e":"AQAB","n":"rwm77av7GIttq-JF1itEgLCGEZW_zz16RlUQVYlLbJtyRSu61fCec_rroP6PxjXU2uLzUOaGaLgAPeUZAJrGuVp9nryKgbZceHckdHDYgJd9TsdJ1MYUsXaOb9joN9vmsCscBx1lwSlFQyNQsHUsrjuDk-opf6RCuazRQ9gkoDCX70HV8WBMFoVm-YWQKJHZEaIQxg_DU4gMFyKRkDGKsYKA0POL-UgWA1qkg6nHY5BOMKaqxbc5ky87muWB5nNk4mfmsckyFv9j1gBiXLKekA_y4UwG2o1pbOLpJS3bP_c95rm4M9ZBmGXqfOQhbjz8z-s9C11i-jmOQ2ByohS-ST3E5sqBzIsxxrxyQDTw--bZNhzpbciyYW4GfkkqyeYoOPd_84jPTBDKQXssvj8ZOj2XboS77tvEO1n1WlwUzh8HPCJod5_fEgSXuozpJtOggXBv0C2ps7yXlDZf-7Jar0UYc_NJEHJF-xShlqd6Q3sVL02PhSCM-ibn9DN9BKmD"}';
        $jwks = json_decode($json_public_key, true, 512, JSON_THROW_ON_ERROR);
        
        try {
            $decoded = JWT::decode($entityBody, JWK::parseKey($jwks, "RS256"));
            return (array)$decoded;
        } catch (\UnexpectedValueException $e) {
            // Неверная подпись, вебхук не от Точки или с ним что-то не так
            throw new Exception("Invalid webhook: " . $e->getMessage());
        }
    }
    
    /**
     * Process webhook event
     *
     * @param array $webhookData
     * @return void
     */
    private function processWebhookEvent(array $webhookData): void
    {
        // Process different webhook events
        if (isset($webhookData['eventType'])) {
            switch ($webhookData['eventType']) {
                case 'PAYMENT_RECEIVED':
                    $this->handlePaymentReceived($webhookData);
                    break;
                case 'BILL_EXPIRED':
                    $this->handleBillExpired($webhookData);
                    break;
                default:
                    Log::info('Unknown webhook event type', ['eventType' => $webhookData['eventType']]);
            }
        }
        
        // Handle webhookType-based events
        if (isset($webhookData['webhookType'])) {
            switch ($webhookData['webhookType']) {
                case 'incomingPayment':
                    $this->handleIncomingPayment($webhookData);
                    break;
                default:
                    Log::info('Unknown webhook type', ['webhookType' => $webhookData['webhookType']]);
            }
        }
    }
    
    /**
     * Handle payment received webhook
     *
     * @param array $webhookData
     * @return void
     */
    private function handlePaymentReceived(array $webhookData): void
    {
        if (isset($webhookData['documentId'])) {
            $order = Order::where('invoice_id', $webhookData['documentId'])->first();
            if ($order) {
                $order->update(['invoice_status' => 'paid']);
                Log::info('Order payment confirmed via webhook', ['order_id' => $order->id]);
            }
        }
    }
    
    /**
     * Handle bill expired webhook
     *
     * @param array $webhookData
     * @return void
     */
    private function handleBillExpired(array $webhookData): void
    {
        if (isset($webhookData['documentId'])) {
            $order = Order::where('invoice_id', $webhookData['documentId'])->first();
            if ($order) {
                $order->update(['invoice_status' => 'expired']);
                Log::info('Order bill expired via webhook', ['order_id' => $order->id]);
            }
        }
    }
    
    /**
     * Handle incoming payment webhook
     *
     * @param array $webhookData
     * @return void
     */
    private function handleIncomingPayment(array $webhookData): void
    {
        $order = $this->findOrderFromWebhookData($webhookData);
        
        if ($order) {
            $order->update(['invoice_status' => 'paid']);
            
            // Extract amount safely from nested structure
            $amount = $this->extractAmountFromWebhookData($webhookData);
            
            Log::info('Order payment received via webhook', [
                'order_id' => $order->id,
                'payment_id' => $webhookData['paymentId'] ?? null,
                'amount' => $amount,
                'purpose' => $webhookData['purpose'] ?? null
            ]);
        } else {
            Log::warning('Could not find order for incoming payment webhook', [
                'webhook_data' => $webhookData
            ]);
        }
    }
    
    /**
     * Find order from webhook data
     *
     * @param array $webhookData
     * @return Order|null
     */
    private function findOrderFromWebhookData(array $webhookData): ?Order
    {
        // Try to find order by various methods
        
        // Method 1: Extract order number from purpose field
        if (isset($webhookData['purpose'])) {
            $purpose = $webhookData['purpose'];
            
            // Look for order number patterns in purpose
            // Pattern 1: "Заказ №123" or "Заказ №123 "
            if (preg_match('/Заказ\s*№\s*(\d+)/ui', $purpose, $matches)) {
                $orderNumber = $matches[1];
                $order = Order::where('order_number', $orderNumber)->first();
                if ($order) {
                    return $order;
                }
                
                // Also try by ID if order_number is not set
                $order = Order::where('id', $orderNumber)->first();
                if ($order) {
                    return $order;
                }
            }
            
            // Pattern 2: Just a number that could be order ID
            if (preg_match('/^\d+$/', trim($purpose))) {
                $orderId = trim($purpose);
                $order = Order::where('id', $orderId)->first();
                if ($order) {
                    return $order;
                }
            }
        }
        
        // Method 2: Try by document number if it matches order pattern
        if (isset($webhookData['documentNumber'])) {
            $order = Order::where('order_number', $webhookData['documentNumber'])->first();
            if ($order) {
                return $order;
            }
        }
        
        // Method 3: Try by amount matching (less reliable, use as last resort)
        $amount = $this->extractAmountFromWebhookData($webhookData);
        if ($amount !== null) {
            $order = Order::where('total_price', $amount)
                         ->whereIn('invoice_status', ['created', 'pending', null])
                         ->first();
            if ($order) {
                return $order;
            }
        }
        
        return null;
    }
    
    /**
     * Extract amount from webhook data handling stdClass structure
     *
     * @param array $webhookData
     * @return float|null
     */
    private function extractAmountFromWebhookData(array $webhookData): ?float
    {
        // Handle different possible structures
        try {
            // Try SidePayer->stdClass->amount
            if (isset($webhookData['SidePayer'])) {
                $sidePayer = $webhookData['SidePayer'];
                
                // If it's an object, convert to array
                if (is_object($sidePayer)) {
                    $sidePayer = json_decode(json_encode($sidePayer), true);
                }
                
                // Check for stdClass nested structure
                if (isset($sidePayer['stdClass']['amount'])) {
                    return floatval($sidePayer['stdClass']['amount']);
                }
                
                // Check for direct amount
                if (isset($sidePayer['amount'])) {
                    return floatval($sidePayer['amount']);
                }
            }
            
            // Try SideRecipient as fallback
            if (isset($webhookData['SideRecipient'])) {
                $sideRecipient = $webhookData['SideRecipient'];
                
                // If it's an object, convert to array
                if (is_object($sideRecipient)) {
                    $sideRecipient = json_decode(json_encode($sideRecipient), true);
                }
                
                // Check for stdClass nested structure
                if (isset($sideRecipient['stdClass']['amount'])) {
                    return floatval($sideRecipient['stdClass']['amount']);
                }
                
                // Check for direct amount
                if (isset($sideRecipient['amount'])) {
                    return floatval($sideRecipient['amount']);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error extracting amount from webhook data', [
                'error' => $e->getMessage(),
                'webhook_data' => $webhookData
            ]);
        }
        
        return null;
    }
} 