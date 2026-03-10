@extends('adminlte::page')

@section('title', 'Ver Rol')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>
                    <i class="fas fa-user-tag"></i> Detalles del Rol
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-right">
                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Información del Rol -->
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i> Información del Rol
                        </h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Nombre:</dt>
                            <dd class="col-sm-8">{{ $role->name }}</dd>

                            <dt class="col-sm-4">Slug:</dt>
                            <dd class="col-sm-8"><code>{{ $role->slug }}</code></dd>

                            <dt class="col-sm-4">Descripción:</dt>
                            <dd class="col-sm-8">{{ $role->description ?? 'Sin descripción' }}</dd>

                            <dt class="col-sm-4">Nivel:</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-info">{{ $role->level }}</span>
                            </dd>

                            <dt class="col-sm-4">Creado:</dt>
                            <dd class="col-sm-8">{{ $role->created_at->format('d/m/Y H:i') }}</dd>

                            <dt class="col-sm-4">Actualizado:</dt>
                            <dd class="col-sm-8">{{ $role->updated_at->format('d/m/Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Permisos -->
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-key"></i> Permisos Asignados ({{ $role->permissions->count() }})
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($role->permissions->isEmpty())
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Este rol no tiene permisos asignados.
                            </div>
                        @else
                            @php
                                $grouped = $role->permissions->groupBy(function($perm) {
                                    return explode('.', $perm->slug)[0] ?? 'otros';
                                });
                            @endphp

                            @foreach($grouped as $group => $groupPerms)
                                <h6 class="text-uppercase text-primary">
                                    <i class="fas fa-folder-open"></i> {{ ucfirst($group) }}
                                </h6>
                                <ul class="list-unstyled ml-3 mb-3">
                                    @foreach($groupPerms as $permission)
                                        <li>
                                            <i class="fas fa-check text-success"></i>
                                            {{ $permission->name }}
                                            @if($permission->description)
                                                <br><small class="text-muted ml-4">{{ $permission->description }}</small>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Usuarios con este rol -->
            <div class="col-md-6">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users"></i> Usuarios con este Rol ({{ $role->users->count() }})
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($role->users->isEmpty())
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No hay usuarios con este rol asignado.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Email</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($role->users as $user)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('users.show', $user->id) }}">
                                                        {{ $user->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    @if($user->activated)
                                                        <span class="badge badge-success">Activo</span>
                                                    @else
                                                        <span class="badge badge-warning">Inactivo</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Menús Asociados -->
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bars"></i> Menús Visibles
                        </h3>
                    </div>
                    <div class="card-body">
                        @php
                            $roleMenus = json_decode($role->menus ?? '[]', true);
                            $menus = \App\Models\Menu::whereIn('id', $roleMenus)
                                ->where(function($q) {
                                    $q->where('parent', 0)->orWhereNull('parent');
                                })
                                ->orderBy('order')
                                ->with('hijosActivos')
                                ->get();
                        @endphp

                        @if(empty($roleMenus))
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Este rol no tiene menús asignados.
                            </div>
                        @else
                            <ul class="list-group">
                                @foreach($menus as $menu)
                                    <li class="list-group-item">
                                        <i class="{{ $menu->icon }}"></i> <strong>{{ $menu->text }}</strong>
                                        @if($menu->hijosActivos->count() > 0)
                                            <ul class="list-unstyled ml-4 mt-2">
                                                @foreach($menu->hijosActivos as $submenu)
                                                    @if(in_array($submenu->id, $roleMenus))
                                                        <li>
                                                            <i class="{{ $submenu->icon }}"></i> {{ $submenu->text }}
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
