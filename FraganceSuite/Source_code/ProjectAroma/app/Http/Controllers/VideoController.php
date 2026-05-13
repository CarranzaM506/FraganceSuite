<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::all();

        return view('dashboard.video.index', compact('videos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,webm,mov|max:204800',
        ], [
            'video.required' => 'El archivo de video es obligatorio.',
            'video.file'     => 'El campo debe ser un archivo válido.',
            'video.mimes'    => 'El video debe ser un archivo mp4, webm o mov.',
            'video.max'      => 'El video no puede superar los 200 MB.',
        ]);

        if (!is_dir(public_path('videos'))) {
            mkdir(public_path('videos'), 0755, true);
        }

        $file     = $request->file('video');
        $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $file->move(public_path('videos'), $fileName);

        $v         = new Video();
        $v->route  = $fileName;
        $v->active = 1;
        $v->save();

        return redirect()->route('video.index')->with('success', 'Video subido exitosamente.');
    }

    public function toggle($id)
    {
        $video         = Video::findOrFail($id);
        $video->active = $video->active ? 0 : 1;
        $video->save();

        return response()->json(['active' => $video->active]);
    }

    public function destroy($id)
    {
        $video = Video::findOrFail($id);

        $path = public_path('videos/' . $video->route);
        if (file_exists($path)) {
            unlink($path);
        }

        $video->delete();

        return redirect()->route('video.index')->with('success', 'Video eliminado correctamente.');
    }
}
