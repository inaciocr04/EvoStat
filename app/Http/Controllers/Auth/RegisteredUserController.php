<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'recaptchaSiteKey' => env('RECAPTCHA_SITE_KEY')
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'firstname' => 'required|string|max:100|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
            'lastname' => 'required|string|max:100|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
            'pseudo' => 'required|string|min:3|max:50|unique:users|regex:/^[a-zA-Z0-9_]+$/',
            'gender' => 'required|in:male,female,other',
            'age' => 'nullable|integer|min:13|max:120',
            'weight' => 'required|integer|min:20|max:300',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults(), 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
        ], [
            'firstname.regex' => 'Le prénom ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            'lastname.regex' => 'Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            'pseudo.regex' => 'Le pseudo ne peut contenir que des lettres, chiffres et underscores.',
            'pseudo.min' => 'Le pseudo doit contenir au moins 3 caractères.',
            'age.min' => 'Vous devez avoir au moins 13 ans pour vous inscrire.',
            'weight.min' => 'Le poids doit être d\'au moins 20 kg.',
            'weight.max' => 'Le poids ne peut pas dépasser 300 kg.',
            'password.regex' => 'Le mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user = User::create([
            'lastname' => $request->lastname,
            'firstname' => $request->firstname,
            'pseudo' => $request->pseudo,
            'gender' => $request->gender,
            'age' => $request->age,
            'weight' => $request->weight,
            'email' => $request->email,
            'role' => 'user',
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('profils', absolute: false));
    }
}
