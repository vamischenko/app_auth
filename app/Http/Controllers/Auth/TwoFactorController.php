<?php

namespace App\Http\Controllers\Auth;

use App\Events\TwoFactorDisabled;
use App\Events\TwoFactorEnabled;
use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Сервис для работы с двухфакторной аутентификацией
     *
     * @var TwoFactorAuthService
     */
    protected TwoFactorAuthService $twoFactorService;

    /**
     * Инициализирует контроллер и внедряет сервис 2FA
     *
     * @param TwoFactorAuthService $twoFactorService Сервис 2FA
     */
    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
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

        if ($this->twoFactorService->isTwoFactorEnabled($user)) {
            return redirect()->route('dashboard')->with('error', '2FA is already enabled.');
        }

        $secret = $this->twoFactorService->generateSecret();
        session(['2fa_secret' => $secret]);

        $qrCodeUrl = $this->twoFactorService->getQRCodeUrl(
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

        $valid = $this->twoFactorService->verifyCode($secret, $request->code);

        if (!$valid) {
            return redirect()->back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes();
        $this->twoFactorService->enableTwoFactor($user, $secret, $recoveryCodes);

        session()->forget('2fa_secret');

        // Генерируем событие включения 2FA
        event(new TwoFactorEnabled($user, $request->ip(), $request->userAgent()));

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
        $this->twoFactorService->disableTwoFactor($user);

        // Генерируем событие отключения 2FA
        event(new TwoFactorDisabled($user, $request->ip(), $request->userAgent()));

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
            $valid = $this->twoFactorService->verifyCode($user->google2fa_secret, $request->code);
        } else {
            $valid = $this->twoFactorService->verifyAndConsumeRecoveryCode($user, $request->code);
        }

        if (!$valid) {
            return redirect()->back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        session()->forget(['2fa_required', '2fa_user_id']);
        Auth::login($user, session('2fa_remember', false));
        session()->forget('2fa_remember');

        return redirect()->intended('/dashboard');
    }

}
