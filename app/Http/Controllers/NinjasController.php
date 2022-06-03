<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Ninja;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NinjasController extends Controller
{
    public function crear(Request $req)
    {
        $datos = $req->getContent();

        //Validar datos del json
        $datos = json_decode($datos); //Se interpreta como objeto. Se puede pasar un parámetro para que en su lugar lo devuelva como array.

        $validator = Validator::make(json_decode($req->getContent(), true), [
            'nombre' => 'required|string|unique:App\Models\Ninja,nombre',
            'informe_habilidades' => 'required|string',
            'rango' => [Rule::in(['novato', 'soldado', 'veterano', 'maestro']), 'required'],
            //'estado' => [Rule::in(['activo', 'retirado', 'fallecido', 'desertor']), 'required'],
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()]);
            //return response('Formato no válido');
        }
        else
        {
            $ninja = new Ninja();
        }
        //Escribir en la base de datos
        try
        {
            $ninja->nombre = $datos->nombre;
            $ninja->informe_habilidades = $datos->informe_habilidades;
            $ninja->rango = $datos->rango;
            $ninja->estado = 'activo';

            $ninja->save();

            $respuesta['msg'] = "ninja guardado con id ".$ninja->id;
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
            'nombre' => 'string',
            'informe_habilidades' => 'string',
            'rango' => Rule::in(['novato', 'soldado', 'veterano', 'maestro']),
            'estado' => Rule::in(['activo', 'retirado', 'fallecido', 'desertor']),
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()]);
            //return response('Formato no válido');
        }
        try
        {
            $ninja = Ninja::where('id', $id)->first();
            if ($ninja)
            {
                if(isset($datos->nombre))
                {
                    $ninja->nombre = $datos->nombre;
                }
                if(isset($datos->informe_habilidades))
                {
                    $ninja->informe_habilidades = $datos->informe_habilidades;
                }
                if(isset($datos->rango))
                {
                    $ninja->rango = $datos->rango;
                }
                if(isset($datos->estado))
                {
                    $ninja->estado = $datos->estado;
                }
                $ninja->save();
            }
            else
            {
                $respuesta['msg'] = "No se ha encontrado un ninja con ese nombre.";
            }
                
            $respuesta['msg'] = "ninja guardado con id ".$ninja->id;
        }
        catch(\Exception $e)
        {
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($respuesta);
    }

    public function listar (Request $req)
    {
        $datos = $req->getContent();

        //Validar datos del json
        $datos = json_decode($datos); //Se interpreta como objeto. Se puede pasar un parámetro para que en su lugar lo devuelva como array.

        try
        {
            $validator = Validator::make(json_decode($req->getContent(), true), [
                'nombre' => 'string',
                'estado' => Rule::in(['activo', 'retirado', 'fallecido', 'desertor']),
            ]);

            if ($validator->fails())
            {
                return response()->json(['errors'=>$validator->errors()]);
            }
            $ninjas = DB::table('ninjas')
                ->when($req->input('nombre'), function ($query, $nombre) {
                    return $query->where('nombre', 'LIKE', "$nombre%");
                })
                ->when($req->input('estado'), function ($query, $estado) {
                    return $query->where('estado', $estado);
                })
                ->get();

            if (!$ninjas->isEmpty())
            {
                $respuesta['ninjas'] = $ninjas;
            }
            else
            {
                $respuesta['msg'] = "No se han encontrado ninjas.";
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
