@extends('adminlte::page')

@section('title', 'Crear Rol')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>
                    <i class="fas fa-plus-circle"></i> Crear Nuevo Rol
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-right">
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
        <form action="{{ route('roles.store') }}" method="POST" id="role-form">
            @csrf

            <div class="row">
                <!-- Información básica -->
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i> Información del Rol
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Nombre del Rol <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}"
                                       placeholder="Ej: Supervisor de Extrusión" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="slug">Slug <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                       id="slug" name="slug" value="{{ old('slug') }}"
                                       placeholder="Ej: supervisor-extrusion" required>
                                <small class="form-text text-muted">
                                    Identificador único del rol (sin espacios, minúsculas, guiones)
                                </small>
                                @error('slug')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="description">Descripción</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3"
                                          placeholder="Descripción del rol y sus responsabilidades">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="level">Nivel de Acceso</label>
                                <input type="number" class="form-control" id="level" name="level"
                                       value="{{ old('level', 1) }}" min="1" max="100">
                                <small class="form-text text-muted">
                                    Nivel jerárquico (1-100). Mayor número = mayor nivel.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permisos -->
                <div class="col-md-6">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-key"></i> Permisos del Sistema
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-light" id="select-all-permissions">
                                    <i class="fas fa-check-square"></i> Seleccionar Todos
                                </button>
                                <button type="button" class="btn btn-sm btn-light" id="deselect-all-permissions">
                                    <i class="fas fa-square"></i> Deseleccionar Todos
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            @if($permissions->isEmpty())
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    No hay permisos disponibles. Crea permisos primero.
                                </div>
                            @else
                                @php
                                    // Agrupar por modelo/categoría
                                    $grouped = $permissions->groupBy('model');
                                    // Ordenar grupos: Dashboard primero
                                    $sortedGroups = $grouped->sortBy(function($items, $key) {
                                        return $key === 'Dashboard' ? 0 : 1;
                                    });
                                @endphp

                                @foreach($sortedGroups as $model => $groupPerms)
                                    <div class="permission-group mb-4">
                                        <h6 class="text-uppercase font-weight-bold"
                                            style="background: {{ $model === 'Dashboard' ? '#17a2b8' : '#6c757d' }}; color: white; padding: 0.5rem; border-radius: 0.25rem;">
                                            <i class="fas {{ $model === 'Dashboard' ? 'fa-chart-line' : 'fa-key' }}"></i>
                                            {{ $model === 'Dashboard' ? 'Dashboards por Rol' : ucfirst($model) }}
                                            <span class="badge badge-light ml-2">{{ $groupPerms->count() }}</span>
                                        </h6>
                                        <div class="pl-3">
                                            @foreach($groupPerms->sortBy('name') as $permission)
                                                <div class="custom-control custom-checkbox mb-2">
                                                    <input type="checkbox" class="custom-control-input permission-checkbox"
                                                           id="permission-{{ $permission->id }}"
                                                           name="permissions[]" value="{{ $permission->id }}">
                                                    <label class="custom-control-label" for="permission-{{ $permission->id }}">
                                                        <strong>{{ $permission->name }}</strong>
                                                        @if($permission->description)
                                                            <small class="text-muted d-block" style="font-size: 0.85rem;">
                                                                {{ $permission->description }}
                                                            </small>
                                                        @endif
                                                        <small class="badge badge-secondary">{{ $permission->slug }}</small>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menús -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-bars"></i> Menús Visibles para el Rol
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-light" id="select-all-menus">
                                    <i class="fas fa-check-square"></i> Seleccionar Todos
                                </button>
                                <button type="button" class="btn btn-sm btn-light" id="deselect-all-menus">
                                    <i class="fas fa-square"></i> Deseleccionar Todos
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Los menús seleccionados serán visibles para todos los usuarios con este rol.
                                Los usuarios heredarán estos menús automáticamente al asignarles el rol.
                            </div>

                            @if($menus->isEmpty())
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    No hay menús disponibles.
                                </div>
                            @else
                                <div class="row">
                                    @foreach($menus as $menu)
                                        <div class="col-md-6 mb-3">
                                            <div class="card card-outline card-primary">
                                                <div class="card-header">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input menu-checkbox parent-menu"
                                                               id="menu-{{ $menu->id }}"
                                                               name="menus[]" value="{{ $menu->id }}"
                                                               data-menu-id="{{ $menu->id }}">
                                                        <label class="custom-control-label font-weight-bold" for="menu-{{ $menu->id }}">
                                                            <i class="{{ $menu->icon }}"></i> {{ $menu->text }}
                                                        </label>
                                                    </div>
                                                </div>
                                                @if($menu->hijosActivos->count() > 0)
                                                    <div class="card-body">
                                                        @foreach($menu->hijosActivos as $submenu)
                                                            <div class="custom-control custom-checkbox ml-3">
                                                                <input type="checkbox" class="custom-control-input menu-checkbox submenu-checkbox"
                                                                       id="menu-{{ $submenu->id }}"
                                                                       name="menus[]" value="{{ $submenu->id }}"
                                                                       data-parent-id="{{ $menu->id }}">
                                                                <label class="custom-control-label" for="menu-{{ $submenu->id }}">
                                                                    <i class="{{ $submenu->icon }}"></i> {{ $submenu->text }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Crear Rol
                            </button>
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop

@section('css')
    <style>
        .permission-group {
            border-left: 3px solid #007bff;
            padding-left: 10px;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Auto-generar slug desde el nombre
            $('#name').on('input', function() {
                const slug = $(this).val()
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .trim();
                $('#slug').val(slug);
            });

            // Seleccionar/Deseleccionar todos los permisos
            $('#select-all-permissions').on('click', function() {
                $('.permission-checkbox').prop('checked', true);
            });

            $('#deselect-all-permissions').on('click', function() {
                $('.permission-checkbox').prop('checked', false);
            });

            // Seleccionar/Deseleccionar todos los menús
            $('#select-all-menus').on('click', function() {
                $('.menu-checkbox').prop('checked', true);
            });

            $('#deselect-all-menus').on('click', function() {
                $('.menu-checkbox').prop('checked', false);
            });

            // Lógica de menús padre-hijo
            $('.parent-menu').on('change', function() {
                const menuId = $(this).data('menu-id');
                const isChecked = $(this).is(':checked');

                // Marcar/desmarcar submenús
                $(`.submenu-checkbox[data-parent-id="${menuId}"]`).prop('checked', isChecked);
            });

            $('.submenu-checkbox').on('change', function() {
                const parentId = $(this).data('parent-id');
                const parentCheckbox = $(`.parent-menu[data-menu-id="${parentId}"]`);

                // Si algún hijo está marcado, marcar el padre
                const anyChecked = $(`.submenu-checkbox[data-parent-id="${parentId}"]:checked`).length > 0;
                if (anyChecked) {
                    parentCheckbox.prop('checked', true);
                }
            });

            // Validación del formulario
            $('#role-form').on('submit', function(e) {
                const name = $('#name').val().trim();
                const slug = $('#slug').val().trim();

                if (!name || !slug) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Los campos Nombre y Slug son obligatorios'
                    });
                    return false;
                }
            });
        });
    </script>
@stop
