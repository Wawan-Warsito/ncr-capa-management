<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\NCR;

class PublicLinkController extends Controller
{
    protected function sign(array $params): string
    {
        $secret = config('app.key');
        ksort($params);
        $payload = http_build_query($params);
        return hash_hmac('sha256', $payload, $secret);
    }

    public function getPublicLink(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ncr = NCR::findOrFail($id);
        $canAccess = $user->isAdmin() || $user->isQCManager()
            || $ncr->finder_dept_id === $user->department_id
            || $ncr->receiver_dept_id === $user->department_id
            || $ncr->assigned_pic_id === $user->id
            || $ncr->created_by_user_id === $user->id;

        if (!$canAccess) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $base = Setting::get('public_base_url', url('/'));
        $base = rtrim($base ?: url('/'), '/');
        $ts = time();
        $expiresDays = (int) Setting::get('public_link_expires_days', 7);
        $params = ['id' => (int)$id, 'ts' => $ts];
        $sig = $this->sign($params);
        $url = $base . '/share/ncrs/' . $id . '/print?ts=' . $ts . '&sig=' . $sig;

        return response()->json([
            'success' => true,
            'data' => ['url' => $url, 'expires_days' => $expiresDays],
        ]);
    }
}
