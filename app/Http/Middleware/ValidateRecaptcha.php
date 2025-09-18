<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateRecaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si le CAPTCHA est présent
        if (!$request->has('g-recaptcha-response')) {
            return back()->withErrors(['captcha' => 'Veuillez compléter le CAPTCHA.']);
        }

        $recaptchaResponse = $request->input('g-recaptcha-response');
        $secretKey = env('RECAPTCHA_SECRET_KEY');

        if (!$secretKey) {
            // En développement, on peut ignorer le CAPTCHA si pas configuré
            if (env('APP_ENV') === 'local') {
                return $next($request);
            }
            return back()->withErrors(['captcha' => 'Configuration CAPTCHA manquante.']);
        }

        // Vérifier avec Google reCAPTCHA
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $secretKey,
            'response' => $recaptchaResponse,
            'remoteip' => $request->ip()
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $resultJson = json_decode($result, true);

        if (!$resultJson['success']) {
            return back()->withErrors(['captcha' => 'CAPTCHA invalide. Veuillez réessayer.']);
        }

        return $next($request);
    }
}