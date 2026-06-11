<?php
namespace App\Modules\Configuracion\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Configuracion\Application\UseCases\DatosEmpresa\ListarDatosEmpresaUseCase;
use App\Modules\Configuracion\Application\UseCases\DatosEmpresa\CrearDatosEmpresaUseCase;
use App\Modules\Configuracion\Application\UseCases\DatosEmpresa\ObtenerDatosEmpresaUseCase;
use App\Modules\Configuracion\Application\UseCases\DatosEmpresa\ActualizarDatosEmpresaUseCase;
use App\Modules\Configuracion\Application\UseCases\DatosEmpresa\EliminarDatosEmpresaUseCase;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;

class DatosEmpresaController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService,
        protected ListarDatosEmpresaUseCase $listarUseCase,
        protected CrearDatosEmpresaUseCase $crearUseCase,
        protected ObtenerDatosEmpresaUseCase $obtenerUseCase,
        protected ActualizarDatosEmpresaUseCase $actualizarUseCase,
        protected EliminarDatosEmpresaUseCase $eliminarUseCase
    ) {}

    public function index(Request $request)
    {
        // $this->permissionService->authorize('datos_empresa.listar');
        return ApiResponse::success($this->listarUseCase->execute(), 'Lista de Datos Empresa');
    }

    public function store(Request $request)
    {
        $this->permissionService->authorize('datos_empresa.crear');
        try {
            return ApiResponse::success($this->crearUseCase->execute($request->all()), 'DatosEmpresa creado', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        // $this->permissionService->authorize('datos_empresa.listar');
        $item = $this->obtenerUseCase->execute($id);
        if (!$item) return ApiResponse::error('No encontrado', 404);
        return ApiResponse::success($item, 'Detalle de DatosEmpresa');
    }

    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('datos_empresa.actualizar');
        try {
            $item = $this->actualizarUseCase->execute($id, $request->all());
            if (!$item) return ApiResponse::error('No encontrado', 404);
            return ApiResponse::success($item, 'DatosEmpresa actualizado');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        $this->permissionService->authorize('datos_empresa.eliminar');
        if ($this->eliminarUseCase->execute($id)) {
            return ApiResponse::success(null, 'DatosEmpresa eliminado');
        }
        return ApiResponse::error('No encontrado', 404);
    }
}