<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;


class ClientesController extends Controller
{
    public function crear(Request $req)
    {
        $datos = $req->getContent();

        //Validar datos del json
        $datos = json_decode($datos); //Se interpreta como objeto. Se puede pasar un parámetro para que en su lugar lo devuelva como array.

        $validator = Validator::make(json_decode($req->getContent(), true), [
            'VIP' => 'required|boolean',
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()]);
            //return response('Formato no válido');
        }
        else
        {
            $cliente = new Cliente();
        }
        //Escribir en la base de datos
        try
        {
            $cliente->VIP = $datos->VIP;
            $cliente->save();

            $respuesta['msg'] = "cliente guardado con id ".$cliente->id;
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
            'nuevoId' => 'integer|unique:App\Models\Cliente,id',
            'VIP' => 'boolean',
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()]);
            //return response('Formato no válido');
        }
        try
        {
            $cliente = Cliente::where('id', $id)->first();
            if ($cliente)
            {
                if (isset($datos->nuevoId))
                {
                    $cliente->id = $datos->nuevoId;
                }
                if(isset($datos->VIP))
                {
                    $cliente->VIP = $datos->VIP;
                }
                $cliente->save();
                $respuesta['msg'] = "Cliente guardado con id ".$cliente->id;
            }
            else
            {
                $respuesta['msg'] = "No existe ese cliente.";
            }
        }
        catch(\Exception $e)
        {
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($respuesta);
    }
    public function listar()
    {
        try
        {
            $clientes = DB::table('clientes')->get();

            if (isset($clientes))
            {
                // foreach ($clientes as $cliente)
                // {
                //     echo 'ID: '.$cliente->id.' || ';
                //     echo 'FECHA DE CREACIÓN: '.$cliente->created_at.' || ';
                //     echo 'VIP: '.$cliente->VIP.' || '."\n";
                // }
                $respuesta['clientes'] = $clientes;
            }
            else
            {
                $respuesta['msg'] = "No se han encontrado clientes.";
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
