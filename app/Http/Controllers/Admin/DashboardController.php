<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $canSeeOrders = $user->can('orders.view');
        $canSeeSales = $user->can('reports.view');
        $canSeeStock = $user->can('inventory.view');

        $today = Carbon::today();

        $todaysOrders = $canSeeOrders ? Order::query()->whereDate('created_at', $today)->count() : 0;
        $todaysSales = $canSeeSales ? (float) Order::query()->whereDate('created_at', $today)->sum('grand_total') : 0.0;
        $pendingOrders = $canSeeOrders ? Order::query()->where('status', OrderStatus::Pending)->count() : 0;
        $deliveredOrders = $canSeeOrders ? Order::query()->where('status', OrderStatus::Delivered)->count() : 0;
        $cancelledOrders = $canSeeOrders ? Order::query()->where('status', OrderStatus::Cancelled)->count() : 0;
        $totalCustomers = $canSeeOrders ? Customer::query()->count() : 0;
        $totalRevenue = $canSeeSales ? (float) Order::query()->whereNotIn('status', [OrderStatus::Cancelled])->sum('grand_total') : 0.0;

        $lowStock = $canSeeStock
            ? Inventory::query()
                ->whereColumn('quantity_on_hand', '<=', 'low_stock_threshold')
                ->with('variant.product')
                ->take(10)
                ->get()
            : collect();

        $recentOrders = $canSeeOrders ? Order::query()->with('customer.user')->latest()->take(8)->get() : collect();

        return view('admin.dashboard', compact(
            'todaysOrders', 'todaysSales', 'pendingOrders', 'deliveredOrders',
            'cancelledOrders', 'totalCustomers', 'totalRevenue', 'lowStock', 'recentOrders',
            'canSeeOrders', 'canSeeSales', 'canSeeStock',
        ));
    }
}
