<x-mail::message>
# Добро пожаловать, {{ $userName }}!

Для вас был создан аккаунт в системе {{ config('app.name') }}.

## Ваши данные для входа:

**Email:** {{ $userEmail }}  
**Пароль:** {{ $userPassword }}

<x-mail::button :url="$loginUrl">
Войти в систему
</x-mail::button>

Пожалуйста, сохраните эти данные в безопасном месте. Рекомендуем сменить пароль после первого входа в систему.

С уважением,<br>
{{ config('app.name') }}
</x-mail::message>
