<?php

namespace App\Http\Controllers\Test;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;

class TestUserController extends Controller
{
	public function index(Request $request)
	{
		// Walidacja bezpiecznego sortowania kolumn
		$sortField = $request->input('field', 'id');
		$sortDirection = $request->input('direction', 'asc');
		$perPage = $request->input('per_page', 10);

		if (!in_array($perPage, [1, 3, 5, 10, 25, 50])) {
			$perPage = 10;
		}
		if (!in_array($sortField, ['id', 'name', 'email'])) {
			$sortField = 'id';
		}
		if (!in_array($sortDirection, ['asc', 'desc'])) {
			$sortDirection = 'asc';
		}

		return Inertia::render('test/users/Index', [
			// Eager loading relacji ról ze Spatie
			'users' => User::query()
				->with('roles:id,name')
				->when($request->input('search'), function ($query, $search) {
					$query->where(function ($q) use ($search) {
						$q->where('name', 'like', "%{$search}%")
							->orWhere('email', 'like', "%{$search}%");
					});
				})
				// Zaawansowany filtr: wykorzystanie scope dostarczanego przez Spatie
				->when($request->input('role'), function ($query, $role) {
					$query->role($role);
				})
				->orderBy($sortField, $sortDirection)
				->paginate($perPage)
				->withQueryString(),

			// Pobranie wszystkich ról dla dropdowna w Vue
			'roles' => Role::select(['id', 'name'])->get(),

			// Przekazanie filtrów do zsynchronizowania stanu komponentu
			'filters' => $request->only(['search', 'role', 'field', 'direction', 'per_page']),
		]);
	}

	/**
	 * Wyświetlenie formularza tworzenia
	 */
	public function create()
	{
		return Inertia::render('test/users/Create', [
			// Przekazujemy listę wszystkich ról do wyboru w checkboxach
			'roles' => Role::select(['id', 'name'])->get(),
		]);
	}

	/**
	 * Zapis nowego użytkownika w bazie danych
	 */
	public function store(Request $request)
	{
		// Walidacja danych wejściowych
		$validated = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
			// 'confirmed' automatycznie sprawdza, czy pole 'password_confirmation' pasuje
			'password' => ['required', 'string', 'confirmed', Password::defaults()],
			'roles' => ['array'],
			'roles.*' => ['string', 'exists:roles,name'],
		]);

		// Utworzenie użytkownika z zahaszowanym hasłem
		// $user = User::create([
		// 	'name' => $validated['name'],
		// 	'email' => $validated['email'],
		// 	'password' => bcrypt($validated['password']),
		// ]);

		// // Przypisanie ról ze Spatie Permission
		// if ($request->has('roles')) {
		// 	$user->assignRole($request->input('roles'));
		// }

		Inertia::flash('toast', [
			'message' => 'Użytkownik został pomyślnie utworzony.',
			'type' => 'success', // success, error, warning, info
		]);

		// Przekierowanie na listę z wiadomością sukcesu
		return redirect()->route('test.users.index')
			->with('message', 'Użytkownik został pomyślnie utworzony.');
	}

	/**
	 * Wyświetlenie formularza edycji
	 */
	public function edit(User $user)
	{
		return Inertia::render('test/users/Edit', [
			'user' => $user->only(['id', 'name', 'email']),
			// Lista wszystkich ról dostępnych w systemie
			'roles' => Role::select(['id', 'name'])->get(),
			// Tablica z nazwami ról, które aktualnie posiada edytowany użytkownik
			'userRoles' => $user->getRoleNames(),
		]);
	}

	/**
	 * Aktualizacja danych użytkownika
	 */
	public function update(Request $request, User $user)
	{
		// Walidacja danych wejściowych
		$validated = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'email' => [
				'required',
				'string',
				'email',
				'max:255',
				Rule::unique('users')->ignore($user->id)
			],
			// Hasło jest opcjonalne (wymagane tylko, jeśli użytkownik wpisze nową wartość)
			'password' => ['nullable', 'string', 'min:8'],
			'roles' => ['array'],
			'roles.*' => ['string', 'exists:roles,name'],
		]);

		// Aktualizacja podstawowych danych
		$user->name = $validated['name'];
		$user->email = $validated['email'];

		// Szyfrowanie i aktualizacja hasła tylko wtedy, gdy zostało podane
		if (!empty($validated['password'])) {
			$user->password = bcrypt($validated['password']);
		}

		// Zapisz
		// $user->save();

		// Synchronizacja ról za pomocą metody z pakietu Spatie Permission
		// $user->syncRoles($request->input('roles', []));

		// Inertia::flash('toast', [
		// 	'message' => 'Dane użytkownika zostały pomyślnie zaktualizowane.',
		// 	'type' => 'success', // success, error, warning, info
		// ]);

		return redirect()->route('test.users.index')
			->with('message', 'Dane użytkownika zostały pomyślnie zaktualizowane.');
	}

	/**
	 * Usuwanie pojedynczego użytkownika
	 */
	public function destroy(User $user)
	{
		// Opcjonalnie: zabezpieczenie, aby nie usunąć samego siebie
		if (auth()->id() === $user->id) {
			// Inertia::flash('toast', [
			// 	'message' => 'Nie możesz usunąć własnego konta.',
			// 	'type' => 'error', // success, error, warning, info
			// ]);

			return back()->with('error', 'Nie możesz usunąć własnego konta.');
		}

		// Usuń rekord
		// $user->delete();

		// Inertia::flash('toast', [
		// 	'message' => 'Użytkownik został pomyślnie usunięty.',
		// 	'type' => 'success', // success, error, warning, info
		// ]);

		return back()->with('message', 'Użytkownik został pomyślnie usunięty.');
	}

	/**
	 * Endpoint do obsługi zbiorowej akcji masowego usuwania
	 */
	public function bulkDelete(Request $request)
	{
		if (in_array(auth()->id(), $request->input('ids'))) {
			return back()->with('error', 'Nie możesz usunąć własnego konta.');
		}

		$request->validate([
			'ids' => 'required|array',
			'ids.*' => [
				'integer',
				'exists:users,id',
			],
		]);

		// Masowe usuwanie rekordów
		// User::whereIn('id', $idsToDelete)->delete();

		Inertia::flash('toast', [
			'message' => 'Pomyślnie usunięto wybranych użytkowników.',
			'type' => 'success', // success, error, warning, info
		]);

		return back()->with('message', 'Pomyślnie usunięto wybranych użytkowników.');
	}

	/**
	 * Generowanie 100 testowych użytkowników
	 */
	public function populate()
	{
		set_time_limit(0);

		// Pobieramy wszystkie dostępne role w systemie
		$roles = Role::pluck('name')->toArray();

		// Jeśli w bazie nie ma żadnych ról, stwórzmy domyślne dla bezpieczeństwa
		if (empty($roles)) {
			Role::create(['name' => 'writer']);
			// Role::create(['name' => 'admin']);
			// Role::create(['name' => 'superadmin']);
			$roles = ['writer'];
		}

		// Bezpieczna transakcja DB
		DB::transaction(function () use ($roles) {
			// Generujemy 50 użytkowników za pomocą Factory
			$users = User::factory()->count(50)->create(['password' => 'invalid']);

			// Przypisujemy losową rolę Spatie każdemu nowemu użytkownikowi
			foreach ($users as $user) {
				$randomRole = $roles[array_rand($roles)];
				$user->assignRole($randomRole);
			}
		});

		return back()->with('message', 'Pomyślnie wygenerowano 50 testowych użytkowników z rolami.');
	}
}
