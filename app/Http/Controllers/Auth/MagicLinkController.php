<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class MagicLinkController extends Controller
{
    public function showRequestForm()
    {
        return view('auth.magic-link.request');
    }

    public function sendMagicLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->back()->with('status', 'If an account exists with this email, you will receive a magic link.');
        }

        MagicLink::where('email', $request->email)
            ->where('used_at', null)
            ->delete();

        $magicLink = MagicLink::createForEmail($request->email);

        Mail::send('emails.magic-link', [
            'url' => route('magic-link.verify', ['token' => $magicLink->token]),
            'user' => $user,
        ], function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Your Magic Login Link');
        });

        return redirect()->back()->with('status', 'If an account exists with this email, you will receive a magic link.');
    }

    public function verify(Request $request, string $token)
    {
        $magicLink = MagicLink::where('token', $token)->first();

        if (!$magicLink || !$magicLink->isValid()) {
            return redirect()->route('login')->withErrors(['error' => 'This magic link is invalid or has expired.']);
        }

        $user = User::where('email', $magicLink->email)->first();

        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'User not found.']);
        }

        $magicLink->markAsUsed();

        Auth::login($user, true);

        return redirect()->intended('/dashboard');
    }
}
