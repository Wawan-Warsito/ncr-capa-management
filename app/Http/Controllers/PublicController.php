<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NCR;
use App\Models\Setting;

class PublicController extends Controller
{
    protected function verify(array $params, string $sig): bool
    {
        ksort($params);
        $payload = http_build_query($params);
        $secret = config('app.key');
        $expected = hash_hmac('sha256', $payload, $secret);
        if (!hash_equals($expected, $sig)) {
            return false;
        }
        $expiresDays = (int) Setting::get('public_link_expires_days', 7);
        $expiry = ($params['ts'] ?? 0) + ($expiresDays * 86400);
        return time() <= $expiry;
    }

    public function print(Request $request, $id)
    {
        $ts = (int) $request->query('ts', 0);
        $sig = (string) $request->query('sig', '');
        if (!$this->verify(['id' => (int)$id, 'ts' => $ts], $sig)) {
            return response('Unauthorized', 403);
        }

        $ncr = NCR::with([
            'finderDepartment',
            'receiverDepartment',
            'defectCategory',
            'severityLevel',
            'dispositionMethod',
            'assignedPic',
            'createdBy',
        ])->findOrFail($id);

        return response()->view('public_ncr_print', ['ncr' => $ncr]);
    }
}
