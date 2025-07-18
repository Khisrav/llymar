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

class TochkaBankService
{
    private string $baseUrl;
    private ?string $accessToken;
    private ?string $customerCode;
    private ?string $accountId;
    private string $apiVersion;

    public function __construct()
    {
        $this->baseUrl = config('services.tochka.base_url');
        $this->accessToken = config('services.tochka.access_token');
        // $this->accessToken = 'working_token';
        $this->customerCode = config('services.tochka.customer_code');
        $this->accountId = config('services.tochka.account_id');
        $this->apiVersion = config('services.tochka.api_version');
        
        Log::info('Tochka Bank credentials', [
            'baseUrl' => $this->baseUrl,
            'accessToken' => $this->accessToken,
            'customerCode' => $this->customerCode,
            'accountId' => $this->accountId,
            'apiVersion' => $this->apiVersion
        ]);
        
        if (!$this->accessToken || !$this->customerCode || !$this->accountId) {
            throw new Exception('Tochka Bank credentials not configured. Please set TOCHKA_ACCESSTOKEN_HYBRID, TOCHKA_CUSTOMER_CODE, and TOCHKA_ACCOUNT_ID in your .env file.');
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
                'Authorization' => "Bearer {$this->accessToken}",
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
                'Authorization' => "Bearer {$this->accessToken}",
            ])->get("{$this->baseUrl}/invoice/{$this->apiVersion}/bills/{$this->customerCode}/{$order->invoice_id}/payment-status");

            if (!$response->successful()) {
                throw new Exception('Failed to get payment status: ' . $response->body());
            }

            $responseData = $response->json();
            
            // Update order invoice status if needed
            if (isset($responseData['Data']['status'])) {
                $order->update(['invoice_status' => $responseData['Data']['status']]);
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
                'Authorization' => "Bearer {$this->accessToken}",
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
                'Authorization' => "Bearer {$this->accessToken}",
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
                'Authorization' => "Bearer {$this->accessToken}",
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
                'positionName' => 'Borderless Glass Doors System',
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
            'accountId' => $this->accountId,
            'legalAddress' => config('services.tochka.legal_address', ''),
            'kpp' => config('services.tochka.kpp', ''),
            'bankName' => config('services.tochka.bank_name', 'ТОЧКА ПАО БАНКА "ФК ОТКРЫТИЕ"'),
            'bankCorrAccount' => config('services.tochka.bank_corr_account', ''),
            'taxCode' => config('services.tochka.tax_code', ''),
            'type' => 'company',
            'secondSideName' => config('services.tochka.company_name', '')
        ];

        $finalSecondSide = array_merge($defaultSecondSide, $secondSide);

        return [
            'Data' => [
                'customerCode' => $this->customerCode,
                'accountId' => $this->accountId,
                'Content' => [
                    'Invoice' => [
                        'number' => $order->order_number ?? (string) $order->id,
                        'basedOn' => 'Заказ №' . ($order->order_number ?? $order->id),
                        'comment' => $order->comment ?? 'Оплата заказа',
                        'paymentExpiryDate' => now()->addDays(30)->format('Y-m-d'),
                        'date' => $order->created_at->format('Y-m-d'),
                        'totalAmount' => number_format($totalAmount, 2, '.', ''),
                        'totalNds' => number_format($totalNds, 2, '.', ''),
                        'Positions' => $positions
                    ]
                ],
                'SecondSide' => $finalSecondSide
            ]
        ];
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
} 