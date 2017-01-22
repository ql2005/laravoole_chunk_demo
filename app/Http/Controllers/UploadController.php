<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;

class UploadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('uplaod');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $receiver = new FileReceiver("file", $request, HandlerFactory::classFromRequest($request));

        $this->file = $request->file('file');

        // check if the upload is success
        if ($receiver->isUploaded()) {

            // receive the file
            $save = $receiver->receive();
            // check if the upload has finished (in chunk mode it will send smaller files)
            if ($save->isFinished()) {
                // D($save->getFile());
                $save = $this->saveFile($save->getFile());

                if ($save) {
                    return response()->json([
                        'success' => TRUE,
                        'data' => [
                            'file' => $save
                        ]
                    ]);
                }else{
                    return response()->json([
                        'success' => FALSE,
                        'msg' => '上传失败'
                    ]);
                }
            } else {
                // we are in chunk mode, lets send the current progress
                /** @var AbstractHandler $handler */
                $handler = $save->handler();

                return response()->json([
                    "done" => $handler->getPercentageDone(),
                ]);
            }
        } else {
            throw new UploadMissingFileException();
        }
    }

    private function saveFile($successFile)
    {
        $safeName = $this->fileName();
        $filePath = storage_path('app/uploads/');
        $move = $successFile->move($filePath, $safeName);

        if ($move) {
            return $safeName;
        }else{
            return FALSE;
        }
    }

    private function fileName()
    {
        $extension = $this->file->getClientOriginalExtension() ?: 'png';
        $randName = time().mt_rand(1000,9999);
        $safeName = $randName . '.' . $extension;

        return $safeName;
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
