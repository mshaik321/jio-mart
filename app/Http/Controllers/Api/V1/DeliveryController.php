<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeliverySlot;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function zones(Request $request)
    {
        $pincode = $request->query('pincode');

        if ($pincode) {
            $zone = DeliveryZone::findServicing($pincode);

            return response()->json(['serviceable' => (bool) $zone, 'zone' => $zone]);
        }

        return response()->json(DeliveryZone::query()->where('is_active', true)->get());
    }

    public function slots()
    {
        return response()->json(DeliverySlot::query()->where('is_active', true)->get());
    }
}
