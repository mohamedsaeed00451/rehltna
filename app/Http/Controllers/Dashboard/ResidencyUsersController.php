<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\SendRegisterUserReplyJob;
use App\Models\RegisterUsers;
use App\Models\ResidencyUser;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResidencyUsersController extends Controller
{
    public function index(Request $request): view|string
    {
        $query = ResidencyUser::query()->withCount('items','orders');

        if ($request->has('search')) {
            $query->when($request->get('search'), function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->get('search')}%")
                    ->orWhere('email', 'like', "%{$request->get('search')}%")
                    ->orWhere('phone', 'like', "%{$request->get('search')}%")
                    ->orWhere('specialty', 'like', "%{$request->get('search')}%")
                    ->orWhere('country', 'like', "%{$request->get('search')}%");
            });
        }

        $residencyUsers = $query->latest()
            ->paginate(10);

        if ($request->ajax()) {
            return view('pages.residency-users.partials.table', compact('residencyUsers'))->render();
        }

        return view('pages.residency-users.index', compact('residencyUsers'));

    }

    public function destroy($id): RedirectResponse
    {
        ResidencyUser::query()->findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Residency users deleted successfully.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->get('ids');
        if (!is_array($ids) || count($ids) === 0) {
            return redirect()->back()->with('warning', 'No register user selected for deletion.');
        }

        ResidencyUser::query()->whereIn('id', $ids)->delete();
        return redirect()->back()->with('success', 'Residency users deleted successfully.');
    }

}
