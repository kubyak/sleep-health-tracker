<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserHealthData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'privacy_policy' => 'required|accepted', // Добавленная валидация для галочки
        ], [
            'name.required' => 'Поле имя обязательно для заполнения.',
            'name.string' => 'Имя должно быть строкой.',
            'name.max' => 'Имя не должно превышать 255 символов.',

            'email.required' => 'Поле email обязательно для заполнения.',
            'email.string' => 'Email должен быть строкой.',
            'email.email' => 'Введите корректный email адрес.',
            'email.max' => 'Email не должен превышать 255 символов.',
            'email.unique' => 'Пользователь с таким email уже существует.',

            'password.required' => 'Поле пароль обязательно для заполнения.',
            'password.string' => 'Пароль должен быть строкой.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.confirmed' => 'Пароли не совпадают.',

            'privacy_policy.required' => 'Необходимо согласие на обработку персональных данных.',
            'privacy_policy.accepted' => 'Необходимо согласие на обработку персональных данных.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Создаем запись с данными о здоровье для нового пользователя
        UserHealthData::create([
            'user_id' => $user->id,
            'sleep_goal' => 480,      // 8 часов
            'steps_goal' => 10000,    // 10,000 шагов
            'calories_goal' => 500,   // 500 калорий
            'active_minutes_goal' => 30, // 30 минут
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Регистрация успешна! Добро пожаловать в SleepTracker, ' . $user->name . '!');
    }
}
