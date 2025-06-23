<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Exports\UsersExport;
use App\Models\Notification;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\NotificationType;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use App\Notifications\Admin\User\NewUserNotification;
use App\Notifications\Admin\User\AdminNewUserCreatedNotification;

class UserController extends Controller
{
    // No necesitamos constructor ya que los middlewares se aplican en las rutas

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener usuarios paginados
        $users = User::paginate(10); // Pagina de 10 en 10
        //
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //dd($request->all());
        // Validación de los datos del formulario
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email', // Verifica que el correo sea único en la tabla `users`
            'password' => 'required|min:8|confirmed', // Asegura que la contraseña coincida con el campo `password_confirmation`
            'status' => 'required|boolean',
            'profile_photo' => 'nullable|image|max:2048',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $plainPassword = $validated['password'];

        // Crear el usuario en la base de datos
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
        ]);

        if (!empty($validated['roles'])) {
            $roles = Role::whereIn('id', $validated['roles'])->pluck('name')->toArray();
            Log::info('Asignando roles al usuario', [
                'user_id' => $user->id,
                'roles_ids' => $validated['roles'],
                'roles_names' => $roles
            ]);
            $user->assignRole($roles);
        } else {
            Log::info('No se enviaron roles, asignando superadmin por defecto', ['user_id' => $user->id]);
            $user->assignRole('superadmin');
        }

        Log::info('Rol asignado al usuario', ['user_id' => $user->id, 'role' => 'superadmin']);

        if ($request->hasFile('profile_photo')) {
            $fileName = strtolower(str_replace(' ', '_', $user->name)) . '.webp'; // Genera el nombre basado en el usuario

            $user->addMediaFromRequest('profile_photo')
                ->usingFileName($fileName) // Usa el nombre basado en el usuario
                ->toMediaCollection('profile_photos');
        }

        $user->notify(new NewUserNotification($user, $plainPassword));

        //Notificar a los admins
        $superadmins = User::role('superadmin')
            ->where('id', '!=', $user->id)
            ->get();

        foreach ($superadmins as $admin) {
            $admin->notify(new AdminNewUserCreatedNotification($user));
        }

        // Mensaje dinámico para la notificación
        return redirect()
            ->route('admin.users.edit', $user->id)
            ->with('notification', [
                'type' => 'success',
                'message' => 'User created successfully!',
                'details' => 'The user data has been saved correctly.',
            ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Obtener la URL de la foto de perfil
        $profilePhotoUrl = $user->getFirstMediaUrl('profile_photos', 'webp');
        
        // Obtener los roles del usuario
        $roles = $user->roles()->get();
        
        return view('admin.users.show', compact('user', 'profilePhotoUrl', 'roles'));
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $profilePhotoUrl = $user->getFirstMediaUrl('profile_photos', 'webp');
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('admin.users.edit', compact('user', 'profilePhotoUrl', 'roles', 'userRoles'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
            'status' => $request->boolean('status'),
        ]);

        if ($request->has('roles')) {
            $roleIds = $request->input('roles', []);
            Log::info('Roles a sincronizar', ['user_id' => $user->id, 'roles' => $roleIds]);
            $roles = Role::whereIn('id', $roleIds)->pluck('name')->toArray();
            $user->syncRoles($roles);
        } else {
            Log::info('No se enviaron roles, limpiando todos los roles', ['user_id' => $user->id]);
            $user->syncRoles([]);
        }

        if ($request->hasFile('profile_photo')) {
            $fileName = strtolower(str_replace(' ', '_', $user->name)) . '.webp'; // Genera el nombre basado en el usuario

            // Limpiar la colección anterior
            $user->clearMediaCollection('profile_photos');

            // Guardar la nueva foto con el nombre personalizado
            $user->addMediaFromRequest('profile_photo')
                ->usingFileName($fileName) // Usa el nombre basado en el usuario
                ->toMediaCollection('profile_photos');
        }

        return redirect()
            ->route('admin.users.edit', $user->id)
            ->with('notification', [
                'type' => 'success',
                'message' => 'User updated successfully!',
                'details' => 'The user details have been updated.',
            ]);
    }

    public function deletePhoto(User $user)
    {
        $media = $user->getFirstMedia('profile_photos');

        if ($media) {
            $media->delete(); // Elimina la foto
            return response()->json([
                'message' => 'Photo deleted successfully.',
                'defaultPhotoUrl' => asset('build/default_profile.png'), // Retorna la foto predeterminada
            ]);
        }

        return response()->json(['message' => 'No photo to delete.'], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('notification', [
            'type' => 'error',
            'message' => 'User deleted successfully!',
        ]);
    }
    
    /**
     * Export users to Excel
     */
    public function exportToExcel(Request $request)
    {
        logger()->info('Iniciando exportación a Excel con parámetros:', [
            'filters' => $request->input('filters'),
            'search' => $request->input('search')
        ]);

        $query = User::query();
        
        // Aplicar filtros si existen
        if ($request->has('filters')) {
            $filters = json_decode($request->input('filters'), true);
            logger()->info('Filtros decodificados para Excel:', $filters);
            
            // Filtro de fecha
            if (isset($filters['date_range']) && !empty($filters['date_range']['start']) && !empty($filters['date_range']['end'])) {
                $startDate = $filters['date_range']['start'];
                $endDate = $filters['date_range']['end'] . ' 23:59:59';
                $query->whereBetween('created_at', [$startDate, $endDate]);
                logger()->info('Aplicando filtro de fechas:', ['start' => $startDate, 'end' => $endDate]);
            }
            
            // Filtro de estado
            if (isset($filters['status'])) {
                if ($filters['status'] === 'active') {
                    $query->where('status', 1);
                } elseif ($filters['status'] === 'inactive') {
                    $query->where('status', 0);
                }
            }
        }
        
        // Aplicar búsqueda si existe
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Obtener datos
        $users = $query->get(['id', 'name', 'email', 'status', 'created_at']);
        
        // Formatear datos para exportación
        $exportData = $users->map(function($user) {
            return [
                'ID' => $user->id,
                'Name' => $user->name,
                'Email' => $user->email,
                'Status' => $user->status ? 'Active' : 'Inactive',
                'Created At' => $user->created_at->format('Y-m-d H:i:s'),
                'Roles' => $user->roles->pluck('name')->implode(', ')
            ];
        });
        
        return Excel::download(new class($exportData) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
            protected $data;
            
            public function __construct($data)
            {
                $this->data = $data;
            }
            
            public function collection()
            {
                return $this->data;
            }
            
            public function headings(): array
            {
                return array_keys($this->data->first()->toArray());
            }
            
            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        }, 'users_export_' . date('Y-m-d') . '.xlsx');
    }
    
    /**
     * Export users to PDF
     */
    public function exportToPdf(Request $request)
    {
        try {
            logger()->info('Iniciando exportación a PDF con parámetros:', [
                'filters' => $request->input('filters'),
                'search' => $request->input('search')
            ]);
        
            $query = User::query();
            
            // Aplicar filtros si existen
            if ($request->has('filters')) {
                $filters = json_decode($request->input('filters'), true);
                logger()->info('Filtros decodificados para PDF:', $filters);
                
                // Filtro de fecha
                if (isset($filters['date_range']) && !empty($filters['date_range']['start']) && !empty($filters['date_range']['end'])) {
                    $startDate = $filters['date_range']['start'];
                    $endDate = $filters['date_range']['end'] . ' 23:59:59';
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                    logger()->info('Aplicando filtro de fechas para PDF:', ['start' => $startDate, 'end' => $endDate]);
                }
                
                // Filtro de estado
                if (isset($filters['status'])) {
                    if ($filters['status'] === 'active') {
                        $query->where('status', 1);
                    } elseif ($filters['status'] === 'inactive') {
                        $query->where('status', 0);
                    }
                }
            }
            
            // Aplicar búsqueda si existe
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            // Obtener datos
            $users = $query->get(['id', 'name', 'email', 'status', 'created_at']);
            
            // Formatear datos para exportación
            $users->transform(function($user) {
                $user->status_text = $user->status ? 'Active' : 'Inactive';
                $user->roles_text = $user->roles->pluck('name')->implode(', ');
                return $user;
            });
            
            $pdf = Pdf::loadView('admin.exports.export', [
                'data' => $users,
                'columns' => ['ID', 'Name', 'Email', 'Status', 'Created At', 'Roles'],
                'title' => 'Users Export'
            ]);
            
            return response()->streamDownload(
                fn() => print($pdf->output()),
                'users_export_' . date('Y-m-d') . '.pdf'
            );
        } catch (\Exception $e) {
            logger()->error('Error en exportación a PDF: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }
}
