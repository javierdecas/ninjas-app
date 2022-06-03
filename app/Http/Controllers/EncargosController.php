<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Ninja;
use App\Models\Encargo;
use App\Models\Mision;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class EncargosController extends Controller
{
    public function crear(Request $req)
    {
        $datos = $req->getContent();

        //Validar datos del json
        $datos = json_decode($datos); //Se interpreta como objeto. Se puede pasar un parámetro para que en su lugar lo devuelva como array.

        $validator = Validator::make(json_decode($req->getContent(), true), [
            'descripcion' => 'required|string',
            'numero_ninjas_necesarios' => 'required|integer',
            'pago' => 'required|string',
            'prioridad' => ['required', Rule::in(['normal', 'urgente'])],
            'cliente_id' => 'required|exists:App\Models\Cliente,id',
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()]);
            //return response('Formato no válido');
        }
        else
        {
            $encargo = new Encargo();
        }
        //Escribir en la base de datos
        try
        {
            $encargo->descripcion = $datos->descripcion;
            $encargo->numero_ninjas_necesarios = $datos->numero_ninjas_necesarios;
            $encargo->pago = $datos->pago;
            $encargo->prioridad = $datos->prioridad;
            $encargo->estado = 'pendiente';
            $encargo->cliente_id = $datos->cliente_id;

            $encargo->save();

            $respuesta['msg'] = "encargo guardado con id ".$encargo->id;
        }
        catch(\Exception $e)
        {
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($respuesta);
    }
    public function editar(Request $req, $id)
    {
        $datos = $req->getContent();

        //Validar datos del json
        $datos = json_decode($datos); //Se interpreta como objeto. Se puede pasar un parámetro para que en su lugar lo devuelva como array.

        $validator = Validator::make(json_decode($req->getContent(), true), [
            'descripcion' => 'string',
            'numero_ninjas_necesarios' => 'integer',
            'pago' => 'string',
            'fecha_finalizacion' => 'date',
            'prioridad' => Rule::in(['normal', 'urgente']),
            'estado' => Rule::in(['pendiente', 'en curso', 'completado', 'fallado']),
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()]);
            //return response('Formato no válido');
        }
        try
        {
            $encargo = encargo::where('id', $id)->first();
            if ($encargo)
            {
                if(isset($datos->descripcion))
                {
                    $encargo->descripcion = $datos->descripcion;
                }
                if(isset($datos->numero_ninjas_necesarios))
                {
                    $encargo->numero_ninjas_necesarios = $datos->numero_ninjas_necesarios;
                }
                if(isset($datos->pago))
                {
                    $encargo->pago = $datos->pago;
                }
                if(isset($datos->estado))
                {
                    $encargo->estado = $datos->estado;
                }
                if(isset($datos->prioridad))
                {
                    $encargo->prioridad = $datos->prioridad;
                }
                $encargo->save();
            }
            else
            {
                $respuesta['msg'] = "No se ha encontrado un encargo con ese id.";
            }

            $respuesta['msg'] = "encargo guardado con id ".$encargo->id;
        }
        catch(\Exception $e)
        {
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($respuesta);
    }
    
    public function terminar(Request $req, $id)
    {
        $datos = $req->getContent();

        //Validar datos del json
        $datos = json_decode($datos); //Se interpreta como objeto. Se puede pasar un parámetro para que en su lugar lo devuelva como array.

        $validator = Validator::make(json_decode($req->getContent(), true), [
            //'fecha_finalizacion' => 'date',
            'estado' => Rule::in(['completado', 'fallado']),
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()]);
            //return response('Formato no válido');
        }
        try
        {
            $encargo = encargo::where('id', $id)->first();
            if ($encargo)
            {
                $encargo->fecha_finalizacion = date('Y-m-d H:i:s');
                $encargo->estado = $datos->estado;

                $encargo->save();
            }
            else
            {
                $respuesta['msg'] = "No se ha encontrado un encargo con ese id.";
            }

            $respuesta['msg'] = "encargo finalizado con id ".$encargo->id;
        }
        catch(\Exception $e)
        {
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($respuesta);
    }
    public function asignar($encargoId, $ninjaId)
    {
        if (isset($encargoId) && isset($ninjaId))
        {
            try
            {
                $encargo = Encargo::where('id', $encargoId)->first();
                $ninja = Ninja::where('id', $ninjaId)->first();

                //Asignar
                if ($encargo && $ninja)
                {
                    $encargo->estado = 'en curso';
                    $mision = new Mision();

                    $mision->encargo_id = $encargoId;
                    $mision->ninja_id = $ninjaId;

                    $mision->save();
                    $encargo->save();

                    $respuesta['msg'] = "Misión guardada con id " . $mision->id;
                }
                else
                {
                    $respuesta['msg'] = "El encargo o el ninja no existen, revise los ids.";
                }
            }
            catch (\Exception $e)
            {
                $respuesta['msg'] = "Se ha producido un error: " . $e->getMessage();
            }
        }

        return response()->json($respuesta);
    }
    public function desasignar($misionId, $ninjaId)
    {
        if (isset($misionId) && isset($ninjaId))
        {
            try
            {
                $mision = Mision::where('id', $misionId)->first();
                $ninja = Ninja::where('id', $ninjaId)->first();

                //Desasignar
                if ($mision && $ninja)
                {
                    if ($mision->ninja_id == $ninjaId)
                    {
                        $mision->ninja_id = null;
                        $mision->save();

                        $respuesta['msg'] = "Ninja desasignado.";
                    }
                    else
                    {
                        $respuesta['msg'] = "El ninja no está asignado en esa misión.";
                    }
                }
                else
                {
                    $respuesta['msg'] = "La misión o el ninja no existen, revise los id.";
                }
            }
            catch (\Exception $e)
            {
                $respuesta['msg'] = "Se ha producido un error: " . $e->getMessage();
            }
        }
        else
        {
            $respuesta['msg'] = "Introduce misionId y ninjaId";
        }

        return response()->json($respuesta);
    }
    public function listar(Request $req)
    {
        $datos = $req->getContent();

        //Validar datos del json
        $datos = json_decode($datos); //Se interpreta como objeto. Se puede pasar un parámetro para que en su lugar lo devuelva como array.
        
        $validator = Validator::make(json_decode($req->getContent(), true), [
            'estado' => Rule::in(['pendiente', 'en curso', 'completado', 'fallado']),
            'prioridad' => Rule::in(['normal', 'urgente']),
        ]);
        
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()]);
            //return response('Formato no válido');
        }

        try
        {
            $encargos = DB::table('encargos')
                ->when($req->input('estado'), function ($query, $estado) {
                    return $query->where('estado', $estado);
                })
                ->when($req->input('cliente_id'), function ($query, $cliente_id) {
                    return $query->where('cliente_id', $cliente_id);
                })
                ->orderBy('prioridad', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            if (!$encargos->isEmpty())
            {
                // foreach ($encargos as $encargo)
                // {
                //     echo 'ID: '.$encargo->id.' || ';
                //     echo 'FECHA DE CREACIÓN: '.$encargo->created_at.' || ';
                //     echo 'PRIORIDAD: '.$encargo->prioridad.' || ';
                //     echo 'ESTADO: '.$encargo->estado.' || ';
                //     echo 'ID CLIENTE: '.$encargo->cliente_id.' || '."\n";
                // }
                $respuesta['encargos'] = $encargos;
            }
            else
            {
                $respuesta['msg'] = "No se han encontrado encargos.";
            }
           
        }
        catch(\Exception $e)
        {
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($respuesta);
    }
}
