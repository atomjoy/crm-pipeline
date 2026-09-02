<form action="{{ route('users.update-permissions', $user) }}" method="POST">
    @csrf
    @method('PUT')

    @foreach($modules as $moduleName => $permissions)
        <div class="card mb-4">
            <!-- Nazwa modułu jako nagłówek sekcji -->
            <div class="card-header bg-light">
                <h5 class="mb-0">{{ ucfirst($moduleName) }} Module</h5>
            </div>

            <div class="card-body">
                <div class="row">
                    @foreach($permissions as $permission)
                        @php
                            // Tworzymy pełną nazwę uprawnienia, np. "blog.create"
                            $fullPermissionName = $moduleName . '.' . $permission;
                        @endphp

                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="permissions[]"
                                       value="{{ $fullPermissionName }}"
                                       id="perm_{{ $fullPermissionName }}"
                                       {{ in_array($fullPermissionName, $userPermissions) ? 'checked' : '' }}>

                                <label class="form-check-label" for="perm_{{ $fullPermissionName }}">
                                    {{ ucfirst($permission) }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    <button type="submit" class="btn btn-primary">Zapisz uprawnienia</button>
</form>
