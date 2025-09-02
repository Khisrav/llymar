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
        // Get absolute path for TemplateProcessor
        $templatePath = Storage::disk('public')->path($template->attachment);
    
        if (!file_exists($templatePath)) {
            throw new \Exception("Template file not found at: " . $templatePath);
        }
    
        // Create TemplateProcessor
        $templateProcessor = new TemplateProcessor($templatePath);
    
        // Fill fields
        $templateFields = $template->fields ?? [];
        foreach ($templateFields as $field) {
            $value = $this->getContractFieldValue($contract, $field);
            $templateProcessor->setValue($field, $value);
        }
    
        // Generate new filename
        $filename = 'filled_contract_' . $contract->id . '_' . Str::random(8) . '.docx';
    
        // Save in public/contracts
        $path = 'contracts/' . $filename;
        $fullPath = Storage::disk('public')->path($path);
    
        // Ensure directory exists
        Storage::disk('public')->makeDirectory('contracts');
    
        // Save the file
        $templateProcessor->saveAs($fullPath);
    
        return $fullPath;
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