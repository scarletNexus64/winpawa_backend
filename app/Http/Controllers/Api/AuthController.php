<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\AffiliateStats;
use App\Models\UserBonus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'date_of_birth' => ['required', 'date', 'before:' . now()->subYears(18)->format('Y-m-d')],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
        ], [
            'date_of_birth.before' => 'Vous devez avoir au moins 18 ans pour vous inscrire.',
        ]);

        $referrer = null;
        if (!empty($validated['referral_code'])) {
            $referrer = User::where('referral_code', $validated['referral_code'])->first();
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'date_of_birth' => $validated['date_of_birth'],
            'referral_code' => User::generateReferralCode(),
            'referred_by' => $referrer?->id,
            'country' => 'CM',
        ]);

        // Créer le wallet
        Wallet::create([
            'user_id' => $user->id,
            'main_balance' => 0,
            'bonus_balance' => 0,
            'affiliate_balance' => 0,
        ]);

        // Créer les stats d'affiliation
        AffiliateStats::create(['user_id' => $user->id]);

        // Mettre à jour les stats du parrain
        if ($referrer) {
            $referrer->affiliateStats?->incrementReferrals();
        }

        // Assigner le rôle
        $user->assignRole('user');

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Inscription réussie ! Bienvenue sur WINPAWA.',
            'data' => [
                'user' => $this->formatUser($user),
                'token' => $token,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect.',
            ], 401);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte a été désactivé. Contactez le support.',
            ], 403);
        }

        // Mettre à jour les infos de connexion
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie !',
            'data' => [
                'user' => $this->formatUser($user),
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->formatUser($request->user()),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour.',
            'data' => $this->formatUser($user->fresh()),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mot de passe actuel incorrect.',
            ], 400);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe modifié avec succès.',
        ]);
    }

    protected function formatUser(User $user): array
    {
        $user->load(['wallet', 'affiliateStats']);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'referral_code' => $user->referral_code,
            'is_verified' => $user->is_verified,
            'wallet' => [
                'main_balance' => (float) $user->wallet?->main_balance ?? 0,
                'bonus_balance' => (float) $user->wallet?->bonus_balance ?? 0,
                'affiliate_balance' => (float) $user->wallet?->affiliate_balance ?? 0,
                'total_balance' => (float) $user->wallet?->total_balance ?? 0,
            ],
            'affiliate' => [
                'total_referrals' => $user->affiliateStats?->total_referrals ?? 0,
                'total_commission' => (float) $user->affiliateStats?->total_commission_earned ?? 0,
                'pending_commission' => (float) $user->affiliateStats?->pending_commission ?? 0,
            ],
            'created_at' => $user->created_at->toISOString(),
        ];
    }
}
