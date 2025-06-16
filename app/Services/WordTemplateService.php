<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractTemplate;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WordTemplateService
{
    /**
     * Fill a Word template with contract data and return the filled document
     *
     * @param ContractTemplate $template
     * @param Contract $contract
     * @return string Path to the filled document
     */
    public function fillTemplate(ContractTemplate $template, Contract $contract): string
    {
        // Get template file path
        $templatePath = Storage::path($template->attachment);
        
        // Create template processor
        $templateProcessor = new TemplateProcessor($templatePath);
        
        // Get template fields
        $templateFields = $template->fields ?? [];
        
        // Fill template with contract data
        foreach ($templateFields as $field) {
            $value = $this->getContractFieldValue($contract, $field);
            $templateProcessor->setValue($field, $value);
        }
        
        // Generate unique filename for filled document
        $filename = 'filled_contract_' . $contract->id . '_' . Str::random(8) . '.docx';
        $outputPath = storage_path('app/filled_contracts/' . $filename);
        
        // Ensure directory exists
        $directory = dirname($outputPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Save filled document
        $templateProcessor->saveAs($outputPath);
        
        return $outputPath;
    }
    
    /**
     * Get the value of a specific field from the contract
     *
     * @param Contract $contract
     * @param string $field
     * @return string
     */
    private function getContractFieldValue(Contract $contract, string $field): string
    {
        switch ($field) {
            case 'template_id':
                return (string) $contract->template_id;
            case 'number':
                return $contract->number ?? '';
            case 'date':
                return $contract->date ? $contract->date->format('d.m.Y') : '';
            case 'counterparty_type':
                return $this->formatCounterpartyType($contract->counterparty_type);
            case 'counterparty_fullname':
                return $contract->counterparty_fullname ?? '';
            case 'counterparty_phone':
                return $contract->counterparty_phone ?? '';
            case 'installation_address':
                return $contract->installation_address ?? '';
            case 'price':
                return number_format($contract->price ?? 0, 2, ',', ' ') . ' руб.';
            case 'advance_payment_percentage':
                return ($contract->advance_payment_percentage ?? 0) . '%';
            case 'department_code':
                return $contract->department_code ?? '';
            case 'index':
                return $contract->index ?? '';
            default:
                return '';
        }
    }
    
    /**
     * Format counterparty type for display
     *
     * @param string|null $type
     * @return string
     */
    private function formatCounterpartyType(?string $type): string
    {
        return match ($type) {
            'entrepreneur' => 'Индивидуальный предприниматель',
            'individual' => 'Физическое лицо',
            'legal_entity' => 'Юридическое лицо',
            default => $type ?? ''
        };
    }
    
    /**
     * Delete a filled contract file
     *
     * @param string $filePath
     * @return bool
     */
    public function deleteFilledContract(string $filePath): bool
    {
        if (file_exists($filePath) && str_contains($filePath, 'filled_contracts')) {
            return unlink($filePath);
        }
        
        return false;
    }
} 