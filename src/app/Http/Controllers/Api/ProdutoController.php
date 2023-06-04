<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\StoreProdutoRequest;
use App\Models\Produto;
use App\Http\Resources\ProdutoResource;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /*
        * Trabalha com o filtro de entrada
        */
        // Query padrao        
        $query = Produto::with('categoria', 'marca');

        //Obtem o parametro do filtro
        $filterParameter = $request -> input("filtro");

        // Sf nao ha nenhum parametro;
        if($filterParameter == null) {
            // Retorna todos os produtos
            $produtos = $query->get();

            $response = response()->json([
                'status' => 200,
                'mensagem' => 'Lista de produtos retornada',
                'produtos' => ProdutoResource::collection($produtos)
            ],200);
        }
        else {
            // Obtem o nome do filtro e o criterio
            [$filterCriteria, $filterValue] = explode(":", $filterParameter);
            
            //Se o filtro está adequado
            if($filterCriteria == "nome_da_categoria") {
                //Faz inner join para obter a categoria
                $produtos = $query->join("categorias","pkcategoria","=","fkcategoria")
                    ->where("nomedacategoria","=",$filterValue)->get();
                    
                $response = response()->json([
                    'status' => 200,
                    'mensagem' => 'Lista de produtos retornada - Filtrada',
                    'produtos' => ProdutoResource::collection($produtos)
                ],200);
            }
            else {
                //Usuario chamou um filtro que não existe, entáo nao ha nada a retornar (Error 406 - Not Accepted)
                $response = response()->json([
                    'status' => 406,
                    'mensagem' => 'Filtro não aceito',
                    'produtos' =>[]
                ],406);
            }
        }

        // Retorna a resposta processada
        return($response);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProdutoRequest $request)
    {
        // Cria o objeto 
        $produto =new Produto();

        // Transfere os valores
        $produto->nomedoproduto = $request->nome_do_produto;
        $produto->anodomodelo = $request->ano_do_modelo;
        $produto->precodelista = $request->preco_de_lista;
        //TODO: ha um jeito melhor de armazenar o ID?
        $produto->fkmarca = $request->marca['id'];
        $produto->fkcategoria = $request->categoria['id'];
        
        // Salva
        $produto->save();
        
        // Retorna o resultado
        return response() -> json([
            'status' => 200,
            'mensagem' => 'Produto armazenado',
            'produto' => new ProdutoResource($produto)
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Produto  $produto
     * @return \Illuminate\Http\Response
     */
    public function show(Produto $produto)
    {
        $produto = Produto::with('categoria', 'marca')->find($produto->pkproduto);

        return response() -> json([
            'status' => 200,
            'mensagem' => 'Produto retornado',
            'produto' => new ProdutoResource($produto)
        ], 200);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Produto  $produto
     * @return \Illuminate\Http\Response
     */
    public function update(StoreProdutoRequest $request, Produto $produto)
    {
        // Transfere os valores
        $produto->nomedoproduto = $request->nome_do_produto;
        $produto->anodomodelo = $request->ano_do_modelo;
        $produto->precodelista = $request->preco_de_lista;
        //TODO: ha um jeito melhor de armazenar o ID?
        $produto->fkmarca = $request->marca['id'];
        $produto->fkcategoria = $request->categoria['id'];
        
        // Salva
        $produto->update();
        
        // Retorna o resultado
        return response() -> json([
            'status' => 200,
            'mensagem' => 'Produto atualizado'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Produto  $produto
     * @return \Illuminate\Http\Response
     */
    public function destroy(Produto $produto)
    {
        $produto->delete();
        return response() -> json([
            'status' => 200,
            'mensagem' => 'Produto apagado'
        ], 200);
    }
}
