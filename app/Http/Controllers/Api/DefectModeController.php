<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DefectMode;

class DefectModeController extends Controller
{
    public function index(Request $request)
    {
        $query = DefectMode::query();

        if ($request->has('defect_category_id')) {
            $query->where('defect_category_id', $request->defect_category_id);
        }

        $modes = $query->get();

        return response()->json([
            'success' => true,
            'data' => $modes
        ]);
    }
}
