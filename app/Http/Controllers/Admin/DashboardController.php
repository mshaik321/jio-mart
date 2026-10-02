<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $todaysOrders = Order::query()->whereDate('created_at', $today)->count();
        $todaysSales = (float) Order::query()->whereDate('created_at', $today)->sum('grand_total');
        $pendingOrders = Order::query()->where('status', OrderStatus::Pending)->count();
        $deliveredOrders = Order::query()->where('status', OrderStatus::Delivered)->count();
        $cancelledOrders = Order::query()->where('status', OrderStatus::Cancelled)->count();
        $totalCustomers = Customer::query()->count();
        $totalRevenue = (float) Order::query()->whereNotIn('status', [OrderStatus::Cancelled])->sum('grand_total');

        $lowStock = Inventory::query()
            ->whereColumn('quantity_on_hand', '<=', 'low_stock_threshold')
            ->with('variant.product')
            ->take(10)
            ->get();

        $recentOrders = Order::query()->with('customer.user')->latest()->take(8)->get();

        return view('admin.dashboard', compact(
            'todaysOrders', 'todaysSales', 'pendingOrders', 'deliveredOrders',
            'cancelledOrders', 'totalCustomers', 'totalRevenue', 'lowStock', 'recentOrders',
        ));
    }
}
