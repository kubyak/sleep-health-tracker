<?php

namespace App\Http\Controllers;

use App\Models\UserHealthData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        $healthData = UserHealthData::firstOrCreate(
            ['user_id' => $user->id],
            [
                'sleep_goal' => 480,
                'steps_goal' => 10000,
                'calories_goal' => 500,
                'active_minutes_goal' => 30,
            ]
        );

        return view('profile.edit', compact('healthData'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $healthData = UserHealthData::where('user_id', $user->id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'age' => 'nullable|integer|min:1|max:120',
            'weight' => 'nullable|numeric|min:1|max:300',
            'height' => 'nullable|numeric|min:50|max:250',
            'gender' => 'nullable|in:male,female,other',
            'sleep_goal' => 'nullable|integer|min:240|max:720',
            'steps_goal' => 'nullable|integer|min:1000|max:50000',
            'calories_goal' => 'nullable|integer|min:100|max:2000',
            'active_minutes_goal' => 'nullable|integer|min:10|max:300',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $healthData->update($request->all());

        return redirect()->route('profile.edit')
            ->with('success', 'Профиль успешно обновлен!');
    }

    public function getHealthStats()
    {
        $user = auth()->user();
        $healthData = UserHealthData::where('user_id', $user->id)->first();

        if (!$healthData) {
            return response()->json(['error' => 'Данные не найдены'], 404);
        }

        return response()->json([
            'bmi' => $healthData->bmi,
            'bmi_category' => $healthData->bmi_description,
            'profile_completion' => $healthData->profile_completion_percentage,
        ]);
    }

    public function exportData()
    {
        $user = auth()->user();
        $healthData = UserHealthData::where('user_id', $user->id)->first();

        if (!$healthData) {
            return redirect()->back()->with('error', 'Нет данных для экспорта');
        }

        $data = [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'health_data' => $healthData->toArray(),
            'exported_at' => now()->format('Y-m-d H:i:s'),
        ];

        return response()->json($data);
    }

    public function showRecommendations()
    {
        $user = auth()->user();
        $healthData = UserHealthData::where('user_id', $user->id)->first();

        if (!$healthData) {
            return redirect()->route('profile.edit')
                ->with('error', 'Заполните профиль для получения рекомендаций');
        }

        $recommendations = $healthData->health_recommendations;

        return view('profile.recommendations', compact('healthData', 'recommendations'));
    }
}
