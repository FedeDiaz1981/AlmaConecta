<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Profile $profile): RedirectResponse
    {
        $user = $request->user();

        if (!$user || ($user->role ?? null) !== 'client' || ($user->account_status ?? 'active') !== 'active') {
            abort(403);
        }

        if ($profile->is_suspended || !in_array($profile->status, ['approved', 'active'], true)) {
            abort(404);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        Review::updateOrCreate(
            ['profile_id' => $profile->id, 'user_id' => $user->id],
            [
                'rating' => (int) $data['rating'],
                'comment' => trim($data['comment']),
            ]
        );

        return back()->with('status', 'Gracias por tu reseña.');
    }

    public function destroy(Request $request, Profile $profile): RedirectResponse
    {
        $user = $request->user();

        if (!$user || ($user->role ?? null) !== 'client' || ($user->account_status ?? 'active') !== 'active') {
            abort(403);
        }

        $review = Review::where('profile_id', $profile->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $review->delete();

        return back()->with('status', 'Reseña eliminada.');
    }
}
