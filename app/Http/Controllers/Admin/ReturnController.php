<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderReturn;
use App\Services\ReturnService;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function index()
    {
        $this->authorize('returns.view');

        $returns = OrderReturn::query()->with('order', 'customer.user')->latest()->paginate(20);

        return view('admin.returns.index', compact('returns'));
    }

    public function show(OrderReturn $return)
    {
        $this->authorize('returns.view');

        $return->load('order.items', 'customer.user', 'items.orderItem');

        return view('admin.returns.show', ['orderReturn' => $return]);
    }

    public function approve(OrderReturn $return, ReturnService $returns)
    {
        $this->authorize('returns.manage');

        $returns->approve($return);

        return back()->with('status', 'Return approved.');
    }

    public function reject(Request $request, OrderReturn $return, ReturnService $returns)
    {
        $this->authorize('returns.manage');

        $returns->reject($return, $request->input('note'));

        return back()->with('status', 'Return rejected.');
    }

    public function complete(Request $request, OrderReturn $return, ReturnService $returns)
    {
        $this->authorize('returns.manage');

        $data = $request->validate(['refund_amount' => ['required', 'numeric', 'min:0']]);

        $returns->complete($return, (float) $data['refund_amount'], $request->user());

        return back()->with('status', 'Return completed and stock restored.');
    }
}
