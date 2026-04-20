<?php
// app/Http/Controllers/SettingsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Show the account settings page.
     */
    public function index()
    {
        return view('settings.index', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * Update the user's profile (name + email).
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->update($data);

        return back()->with('profile_success', 'Profile updated successfully.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'Your current password is incorrect.',
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('password_success', 'Password changed successfully.');
    }

    /**
     * Delete the user's account and all associated data.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'confirm_delete' => ['required', 'in:DELETE'],
        ], [
            'confirm_delete.in' => 'Please type DELETE exactly to confirm.',
        ]);

        $user = auth()->user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Cascade deletes conversations, messages, journal_entries
        // via the ON DELETE CASCADE foreign keys in the schema
        $user->delete();

        return redirect()->route('login')
            ->with('status', 'Your account has been permanently deleted.');
    }
}