<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected $fillable = [
        'template_id',
        'number',
        'date',
        'counterparty_type',
        'counterparty_fullname',
        'counterparty_phone',
        'counterparty_address',
        'counterparty_email',
        'installation_address',
        'price',
        'advance_payment_percentage',
        'department_code',
        'index',
        'company_performer_id',
        'company_factory_id',
        'order_id',
    ];
    
    protected $casts = [
        'date' => 'date',
        'price' => 'integer',
        'advance_payment_percentage' => 'integer',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($contract) {
            if (empty($contract->number)) {
                $contract->number = self::generateContractNumber($contract);
            }
        });
    }
    
    public function template(): BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class);
    }
    
    public function companyPerformer(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_performer_id');
    }
    
    public function companyFactory(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_factory_id');
    }
    
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Generate a unique contract number
     */
    private static function generateContractNumber($contract): string
    {
        // Get the current year
        $year = now()->year;
        
        // Get department code and index
        $departmentCode = $contract->department_code ?? '000';
        $index = $contract->index ?? 'МВ';
        
        // Find the next sequential number for this department code and index in current year
        $lastContract = self::where('department_code', $departmentCode)
            ->where('index', $index)
            ->whereYear('date', $year)
            ->orderBy('number', 'desc')
            ->first();
        
        if ($lastContract) {
            $nextNumber = intval($lastContract->number) + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Just return the sequential number - the display format will be handled in the view
        // Format in view: department_code-number-index (e.g., 101-5-МВ)
        return (string) $nextNumber;
    }
    
    public static function getColumns() {
        return [
            'template_id' => 'ID шаблона',
            'number' => 'Номер договора',
            'date' => 'Дата',
            'counterparty_type' => 'Тип заказчика',
            'counterparty_fullname' => 'ФИО заказчика',
            'counterparty_phone' => 'Телефон заказчика',
            'counterparty_address' => 'Адрес заказчика',
            'counterparty_email' => 'Email заказчика',
            'installation_address' => 'Адрес установки',
            'price' => 'Цена',
            'advance_payment_percentage' => '% аванса',
            'department_code' => 'Код подразделения',
            'index' => 'Индекс',
            'company_performer_id' => 'ID организации исполнителя',
            'company_factory_id' => 'ID завода',
            'order_id' => 'ID заказа',
        ];
    }
}
