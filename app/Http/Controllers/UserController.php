<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UserController extends Controller
{
    // Safety configuration constants
    private const LOGO_MAX_FILE_SIZE = 2097152; // 2MB in bytes
    private const LOGO_ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];
    private const LOGO_ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
    private const LOGO_STORAGE_PATH = 'logos';

    public function show()
    {
        return Inertia::render('App/Account/Settings', [
            'user' => Auth::user(),
        ]);
    }
    
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'telegram' => ['nullable', 'string', 'max:255'],
            'inn' => ['nullable', 'string', 'max:255'],
            'kpp' => ['nullable', 'string', 'max:255'],
            'bank' => ['nullable', 'string', 'max:255'],
            'legal_address' => ['nullable', 'string', 'max:255'],
            'current_account' => ['nullable', 'string', 'max:255'],
            'correspondent_account' => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        $user->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Upload user logo with validation
     */
    public function uploadLogo(Request $request)
    {
        $user = Auth::user();

        // Validate the uploaded file
        $validated = $request->validate([
            'logo' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    // Check file size
                    if ($value->getSize() > self::LOGO_MAX_FILE_SIZE) {
                        $fail("Размер логотипа не должен превышать 2 МБ. Текущий размер: " . round($value->getSize() / 1024 / 1024, 2) . " МБ");
                    }

                    // Check MIME type
                    if (!in_array($value->getMimeType(), self::LOGO_ALLOWED_MIMES)) {
                        $fail('Логотип должен быть в формате JPG, PNG или WebP.');
                    }

                    // Verify it's a valid image
                    try {
                        $path = $value->getRealPath();
                        $imageInfo = getimagesize($path);
                        
                        if ($imageInfo === false) {
                            $fail('Невозможно определить параметры изображения. Убедитесь, что файл является корректным изображением.');
                        }
                    } catch (\Exception $e) {
                        Log::warning('Logo validation failed: ' . $e->getMessage());
                        $fail('Невозможно обработать изображение. Убедитесь, что файл является корректным изображением.');
                    }
                },
            ],
        ]);

        try {
            // Delete old logo if exists
            if ($user->logo) {
                Storage::disk('public')->delete($user->logo);
            }

            // Store new logo
            $logoPath = $request->file('logo')->store(self::LOGO_STORAGE_PATH, 'public');

            // Update user with new logo path
            $user->update(['logo' => $logoPath]);

            return redirect()->back()->with('success', 'Логотип успешно загружен.');
        } catch (\Exception $e) {
            Log::error('Logo upload failed: ' . $e->getMessage());
            return redirect()->back()->withErrors(['logo' => 'Ошибка при загрузке логотипа. Пожалуйста, попробуйте снова.']);
        }
    }

    /**
     * Delete user logo
     */
    public function deleteLogo(Request $request)
    {
        $user = Auth::user();

        try {
            if ($user->logo) {
                // Delete file from storage
                Storage::disk('public')->delete($user->logo);
                
                // Update user to remove logo
                $user->update(['logo' => null]);

                return redirect()->back()->with('success', 'Логотип успешно удален.');
            }

            return redirect()->back()->withErrors(['logo' => 'Логотип не найден.']);
        } catch (\Exception $e) {
            Log::error('Logo deletion failed: ' . $e->getMessage());
            return redirect()->back()->withErrors(['logo' => 'Ошибка при удалении логотипа. Пожалуйста, попробуйте снова.']);
        }
    }
}
