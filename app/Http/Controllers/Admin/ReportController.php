<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('reports.view');

        $from = $request->query('from') ? Carbon::parse($request->query('from')) : now()->subDays(29)->startOfDay();
        $to = $request->query('to') ? Carbon::parse($request->query('to'))->endOfDay() : now()->endOfDay();

        $ordersInRange = Order::query()->whereBetween('created_at', [$from, $to]);

        $salesByDay = (clone $ordersInRange)
            ->selectRaw("DATE(created_at) as day, COUNT(*) as orders, SUM(grand_total) as revenue")
            ->whereNotIn('status', [OrderStatus::Cancelled])
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $totalRevenue = (float) (clone $ordersInRange)->whereNotIn('status', [OrderStatus::Cancelled])->sum('grand_total');
        $totalOrders = (clone $ordersInRange)->count();
        $totalDiscount = (float) (clone $ordersInRange)->sum('discount_amount');
        $totalTax = (float) (clone $ordersInRange)->sum('tax_amount');

        $statusBreakdown = (clone $ordersInRange)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $topProducts = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->whereBetween('created_at', [$from, $to])->whereNotIn('status', [OrderStatus::Cancelled]))
            ->selectRaw('product_name, SUM(quantity) as units_sold, SUM(total) as revenue')
            ->groupBy('product_name')
            ->orderByDesc('units_sold')
            ->take(10)
            ->get();

        $lowStockCount = Inventory::query()->whereColumn('quantity_on_hand', '<=', 'low_stock_threshold')->count();

        return view('admin.reports.index', compact(
            'from', 'to', 'salesByDay', 'totalRevenue', 'totalOrders', 'totalDiscount',
            'totalTax', 'statusBreakdown', 'topProducts', 'lowStockCount',
        ));
    }
}
