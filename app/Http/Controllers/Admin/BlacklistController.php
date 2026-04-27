<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomainBlacklist;
use Illuminate\Http\Request;

class BlacklistController extends Controller
{
    public function index(Request $request)
    {
        $query = DomainBlacklist::query();

        if ($search = $request->get('search')) {
            $query->where('domain', 'like', "%{$search}%");
        }

        $blacklists = $query->latest()->paginate(20)->withQueryString();

        return view('admin.blacklist.index', compact('blacklists'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:domain_blacklists,domain',
            'reason' => 'nullable|string|max:500',
        ]);

        DomainBlacklist::create($validated);

        return back()->with('success', 'Domain ' . $validated['domain'] . ' has been blacklisted.');
    }

    public function destroy(DomainBlacklist $blacklist)
    {
        $domain = $blacklist->domain;
        $blacklist->delete();

        return back()->with('success', 'Domain ' . $domain . ' removed from blacklist.');
    }
}
