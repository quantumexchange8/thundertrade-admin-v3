<?php

namespace App\Http\Controllers\Web;

use App\Exports\Export;
use App\Http\Controllers\Controller;
use App\Models\Ranking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ExportableTrait;
use App\Traits\SearchableTrait;
use Inertia\Inertia;

class RankingController extends Controller
{

    use SearchableTrait, ExportableTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Inertia::render("Ranking");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'level' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'deposit' => ['required', 'numeric'],
            'withdrawal' => ['required', 'numeric'],
        ]);



        Ranking::create([
            'level' => $request->level,
            'amount' => $request->amount,
            'deposit' => $request->deposit,
            'withdrawal' => $request->withdrawal,
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Create Ranking Success',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = Ranking::find($id);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'level' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'deposit' => ['required', 'numeric'],
            'withdrawal' => ['required', 'numeric'],
        ]);



        $rec = Ranking::find($id);
        $rec->update([
            'level' => $request->level,
            'amount' => $request->amount,
            'deposit' => $request->deposit,
            'withdrawal' => $request->withdrawal,
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Update Ranking Success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $count = Ranking::destroy($id);
        if ($count > 0) {
            return response()->json([
                'success' => true,
                "message" => "Delete Ranking Success",
            ]);
        } else {
            return response()->json([
                'success' => false,
                "message" => "Delete Ranking Error",
            ]);
        }
    }
}
