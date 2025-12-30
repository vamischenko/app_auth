<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

/**
 * Контроллер двухфакторной аутентификации (2FA)
 *
 * Управляет настройкой, включением, отключением и верификацией двухфакторной
 * аутентификации через Google Authenticator. Поддерживает коды восстановления
 * для доступа при потере устройства с 2FA.
 */
class TwoFactorController extends Controller
{
    /**
     * Экземпляр Google2FA для генерации и верификации кодов
     *
     * @var Google2FA
     */
    protected Google2FA $google2fa;

    /**
     * Инициализирует контроллер и создает экземпляр Google2FA
     */
    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Отображает форму для включения 2FA
     *
     * Генерирует секретный ключ и QR-код для сканирования в Google Authenticator.
     * Если 2FA уже включена, перенаправляет на dashboard.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showEnableForm()
    {
        $user = Auth::user();

        if ($user->google2fa_enabled) {
            return redirect()->route('dashboard')->with('error', '2FA is already enabled.');
        }

        $secret = $this->google2fa->generateSecretKey();
        session(['2fa_secret' => $secret]);

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('auth.two-factor.enable', [
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $secret,
        ]);
    }

    /**
     * Включает двухфакторную аутентификацию для пользователя
     *
     * Проверяет введенный код для подтверждения настройки 2FA, генерирует
     * коды восстановления и сохраняет настройки в базе данных.
     *
     * @param Request $request HTTP запрос с кодом подтверждения
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $user = Auth::user();
        $secret = session('2fa_secret');

        if (!$secret) {
            return redirect()->route('2fa.enable')->with('error', 'Please scan the QR code first.');
        }

        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return redirect()->back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'google2fa_enabled' => true,
            'google2fa_secret' => $secret,
            'google2fa_recovery_codes' => $recoveryCodes,
        ]);

        session()->forget('2fa_secret');

        return view('auth.two-factor.recovery-codes', [
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Отключает двухфакторную аутентификацию
     *
     * Требует подтверждения текущего пароля. Удаляет секретный ключ
     * и коды восстановления из базы данных.
     *
     * @param Request $request HTTP запрос с текущим паролем
     * @return \Illuminate\Http\RedirectResponse Перенаправление на dashboard
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();

        $user->update([
            'google2fa_enabled' => false,
            'google2fa_secret' => null,
            'google2fa_recovery_codes' => null,
        ]);

        return redirect()->route('dashboard')->with('status', '2FA has been disabled.');
    }

    /**
     * Отображает форму ввода 2FA кода при входе
     *
     * Доступна только если в сессии установлен флаг 2fa_required.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showChallengeForm()
    {
        if (!session('2fa_required')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor.challenge');
    }

    /**
     * Проверяет 2FA код при входе в систему
     *
     * Принимает либо 6-значный код из Google Authenticator, либо код восстановления.
     * При успешной проверке выполняет вход пользователя в систему.
     *
     * @param Request $request HTTP запрос с 2FA кодом
     * @return \Illuminate\Http\RedirectResponse Перенаправление на dashboard или назад с ошибкой
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = session('2fa_user_id');
        $user = \App\Models\User::findOrFail($userId);

        if (strlen($request->code) === 6) {
            $valid = $this->google2fa->verifyKey($user->google2fa_secret, $request->code);
        } else {
            $valid = $this->verifyRecoveryCode($user, $request->code);
        }

        if (!$valid) {
            return redirect()->back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        session()->forget(['2fa_required', '2fa_user_id']);
        Auth::login($user, session('2fa_remember', false));
        session()->forget('2fa_remember');

        return redirect()->intended('/dashboard');
    }

    /**
     * Генерирует коды восстановления для 2FA
     *
     * Создает 8 случайных 8-символьных шестнадцатеричных кодов в верхнем регистре.
     *
     * @return array<int, string> Массив кодов восстановления
     */
    protected function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    /**
     * Проверяет код восстановления и удаляет его при успехе
     *
     * Ищет код в массиве кодов восстановления пользователя.
     * При нахождении удаляет использованный код из базы данных.
     *
     * @param \App\Models\User $user Пользователь
     * @param string $code Код восстановления
     * @return bool True, если код валиден и не был использован
     */
    protected function verifyRecoveryCode($user, $code): bool
    {
        $recoveryCodes = $user->google2fa_recovery_codes;

        if (!$recoveryCodes) {
            return false;
        }

        $key = array_search(strtoupper($code), $recoveryCodes);

        if ($key !== false) {
            unset($recoveryCodes[$key]);
            $user->update(['google2fa_recovery_codes' => array_values($recoveryCodes)]);
            return true;
        }

        return false;
    }
}
