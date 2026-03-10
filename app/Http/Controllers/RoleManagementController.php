<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;
use App\Models\User;
use App\Services\DashboardPermissionService;
use App\Helpers\ButtonHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use RealRashid\SweetAlert\Facades\Alert;

class RoleManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::withCount(['users', 'permissions'])->get();
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Sincronizar permisos de dashboard antes de mostrar el formulario
        DashboardPermissionService::syncDashboardPermissions();

        $permissions = Permission::orderBy('model')->orderBy('name')->get();
        $menus = Menu::where(function($q) {
            $q->where('parent', 0)->orWhereNull('parent');
        })->where('enabled', true)->orderBy('order')->with('hijosActivos')->get();

        return view('roles.create', compact('permissions', 'menus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'slug' => 'required|string|max:255|unique:roles,slug',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'menus' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // Crear el rol
            $role = Role::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
                'level' => $request->level ?? 1,
            ]);

            // Crear automáticamente el permiso de dashboard para este rol
            DashboardPermissionService::createDashboardPermissionForRole($role);

            // Auto-asignar el permiso de dashboard al rol
            DashboardPermissionService::autoAssignDashboardPermission($role);

            // Asignar permisos adicionales
            if ($request->has('permissions')) {
                $role->attachPermissions($request->permissions);
            }

            // Asignar menús a los usuarios del rol (cuando se asignen usuarios)
            // Los menús se guardan para referencia futura
            if ($request->has('menus')) {
                $role->menus = json_encode($request->menus);
                $role->save();
            }

            DB::commit();
            Alert::success('Rol creado exitosamente', 'Se ha creado automáticamente el permiso de dashboard para este rol.');
            return redirect()->route('roles.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Error al crear el rol', $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'users']);
        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        // Sincronizar permisos de dashboard
        DashboardPermissionService::syncDashboardPermissions();

        $permissions = Permission::orderBy('model')->orderBy('name')->get();
        $menus = Menu::where(function($q) {
            $q->where('parent', 0)->orWhereNull('parent');
        })->where('enabled', true)->orderBy('order')->with('hijosActivos')->get();

        $rolePermissions = $role->permissions->pluck('id')->toArray();
        $roleMenus = json_decode($role->menus ?? '[]', true);

        return view('roles.edit', compact('role', 'permissions', 'menus', 'rolePermissions', 'roleMenus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'menus' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // Actualizar el rol
            $role->update([
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
                'level' => $request->level ?? $role->level,
            ]);

            // Sincronizar permisos
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            } else {
                $role->detachAllPermissions();
            }

            // Guardar menús
            $role->menus = json_encode($request->menus ?? []);
            $role->save();

            // Sincronizar menús con todos los usuarios del rol
            if ($request->has('menus')) {
                $this->syncMenusToRoleUsers($role, $request->menus);
            }

            DB::commit();
            Alert::success('Rol actualizado exitosamente');
            return redirect()->route('roles.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Error al actualizar el rol', $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Prevenir eliminación de roles del sistema
        if (in_array($role->slug, ['admin', 'user'])) {
            Alert::error('No se puede eliminar este rol del sistema');
            return back();
        }

        try {
            $role->delete();
            Alert::success('Rol eliminado exitosamente');
            return redirect()->route('roles.index');
        } catch (\Exception $e) {
            Alert::error('Error al eliminar el rol', $e->getMessage());
            return back();
        }
    }

    /**
     * DataTable data for roles
     */
    public function rolesData()
    {
        try {
            $roles = Role::withCount(['users', 'permissions'])->get();

            return DataTables::of($roles)
                ->addIndexColumn()
                ->editColumn('description', function ($role) {
                    return $role->description ?? '<span class="text-muted">Sin descripción</span>';
                })
                ->addColumn('users_count', function ($role) {
                    return '<span class="badge badge-info">' . $role->users_count . '</span>';
                })
                ->addColumn('permissions_count', function ($role) {
                    return '<span class="badge badge-success">' . $role->permissions_count . '</span>';
                })
                ->addColumn('action', function ($role) {
                    $buttons = '<div class="btn-group btn-group-sm" role="group">';
                    $buttons .= ButtonHelper::show(route('roles.show', $role->id));
                    $buttons .= ButtonHelper::edit(route('roles.edit', $role->id));

                    if (!in_array($role->slug, ['admin', 'user'])) {
                        $buttons .= ButtonHelper::delete(null, $role->id, null, 'deleteRole');
                    }

                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['description', 'users_count', 'permissions_count', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            \Log::error('Error en rolesData: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }

    /**
     * Sincronizar menús con los usuarios del rol
     */
    private function syncMenusToRoleUsers(Role $role, array $menuIds)
    {
        $users = $role->users;

        foreach ($users as $user) {
            // Obtener menús actuales del usuario
            $currentMenus = $user->menus()->pluck('menus.id')->toArray();

            // Combinar con los nuevos menús del rol
            $allMenus = array_unique(array_merge($currentMenus, $menuIds));

            // Sincronizar
            $user->menus()->sync($allMenus);
        }
    }

    /**
     * Asignar menús automáticamente basados en permisos
     */
    public function autoAssignMenus(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($request->role_id);
        $permissions = $role->permissions;

        // Lógica para mapear permisos a menús
        $menuMapping = [
            'view.users' => [1, 2], // IDs de menús relacionados con usuarios
            'create.users' => [1, 2],
            'edit.users' => [1, 2],
            'delete.users' => [1, 2],
            // Agregar más mapeos según sea necesario
        ];

        $menusToAssign = [];
        foreach ($permissions as $permission) {
            if (isset($menuMapping[$permission->slug])) {
                $menusToAssign = array_merge($menusToAssign, $menuMapping[$permission->slug]);
            }
        }

        $menusToAssign = array_unique($menusToAssign);
        $role->menus = json_encode($menusToAssign);
        $role->save();

        // Sincronizar con usuarios del rol
        $this->syncMenusToRoleUsers($role, $menusToAssign);

        return response()->json([
            'success' => true,
            'message' => 'Menús asignados automáticamente',
            'menus' => $menusToAssign,
        ]);
    }
}
