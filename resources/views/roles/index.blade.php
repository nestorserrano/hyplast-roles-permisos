@extends('adminlte::page')

@section('title', 'Gestión de Roles')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>
                    <i class="fas fa-user-tag"></i> Gestión de Roles y Permisos
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-right">
                    <a href="{{ route('roles.create') }}" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Nuevo Rol
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i> Lista de Roles
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" onclick="$('#roles-table').DataTable().ajax.reload();">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Información:</strong> Los roles permiten agrupar permisos y menús que se asignarán automáticamente a los usuarios.
                            Al asignar un rol a un usuario, este heredará todos los permisos y menús del rol.
                        </div>

                        <table id="roles-table" class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="20%">Nombre</th>
                                    <th width="15%">Slug</th>
                                    <th width="25%">Descripción</th>
                                    <th width="8%" class="text-center">Usuarios</th>
                                    <th width="8%" class="text-center">Permisos</th>
                                    <th width="6%" class="text-center">Ver</th>
                                    <th width="6%" class="text-center">Editar</th>
                                    <th width="7%" class="text-center">Eliminar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                <tr>
                                    <td class="text-center">{{ $role->id }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td>{{ $role->slug }}</td>
                                    <td>{!! $role->description ?? '<span class="text-muted">Sin descripción</span>' !!}</td>
                                    <td class="text-center">
                                        <span class="badge badge-info">{{ $role->users()->count() }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-success">{{ $role->permissions()->count() }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('roles.show', $role->id) }}" class="btn btn-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        @if(!in_array($role->slug, ['admin', 'user']))
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteRole({{ $role->id }})" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @else
                                        <button type="button" class="btn btn-secondary btn-sm" disabled title="No se puede eliminar">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de roles -->
        <div class="row">
            @foreach($roles as $role)
            <div class="col-md-4">
                <div class="card card-widget widget-user-2">
                    <div class="widget-user-header bg-{{ $loop->index % 3 == 0 ? 'primary' : ($loop->index % 3 == 1 ? 'success' : 'info') }}">
                        <div class="widget-user-image">
                            <span class="brand-image elevation-3" style="opacity: .8">
                                <i class="fas fa-user-tag fa-2x text-white"></i>
                            </span>
                        </div>
                        <h3 class="widget-user-username">{{ $role->name }}</h3>
                        <h5 class="widget-user-desc">{{ $role->slug }}</h5>
                    </div>
                    <div class="card-footer p-0">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <span class="nav-link">
                                    Usuarios <span class="float-right badge bg-primary">{{ $role->users_count }}</span>
                                </span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link">
                                    Permisos <span class="float-right badge bg-success">{{ $role->permissions_count }}</span>
                                </span>
                            </li>
                            <li class="nav-item">
                                <div class="nav-link">
                                    <a href="{{ route('roles.show', $role->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Form para eliminar -->
    <form id="delete-role-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@stop

@section('js')
    @include('scripts.datatables.datatables-roles')
@stop

