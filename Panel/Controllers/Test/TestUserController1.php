<?php

namespace App\Http\Controllers\Test;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TestUserController1 extends Controller
{
	public function index(Request $request)
	{
		$sortField = $request->input('field', 'id');
		$sortDirection = $request->input('direction', 'asc');

		if (!in_array($sortField, ['id', 'name', 'email'])) {
			$sortField = 'id';
		}

		if (!in_array($sortDirection, ['asc', 'desc'])) {
			$sortDirection = 'asc';
		}

		return Inertia::render('test/users/Index', [
			'users' => User::query()
				->when($request->input('search'), function ($query, $search) {
					$query->where('name', 'like', "%{$search}%")
						->orWhere('email', 'like', "%{$search}%");
				})
				->orderBy($sortField, $sortDirection)
				->paginate(2)
				->withQueryString(),
			'filters' => $request->only(['search', 'field', 'direction']),
		]);
	}
}
